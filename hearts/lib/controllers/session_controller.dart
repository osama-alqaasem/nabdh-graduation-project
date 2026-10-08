import 'dart:convert';

import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:shared_preferences/shared_preferences.dart';

import '../api/api_config.dart';
import '../api/api_service.dart';
import '../models/app_models.dart';
import '../models/patient_profile.dart';
import 'error_handler.dart';

class SessionController extends GetxController {
  static const String _tokenKey = 'auth_token';
  static const String _legacyTokenKey = 'token';
  static const String _userKey = 'auth_user';
  static const String _seenIntroKey = 'seen_intro';
  static const String _historyKey = 'analysis_history';

  bool _ready = false;
  bool _onboardingSeen = false;
  String? _token;
  Map<String, dynamic>? _user;

  void _showFriendlyError(dynamic error, {String title = 'تنبيه'}) {
    final message = ErrorHandler.getMessage(error);

    if (Get.isSnackbarOpen) {
      Get.closeCurrentSnackbar();
    }

    Get.snackbar(
      title,
      message,
      snackPosition: SnackPosition.BOTTOM,
      backgroundColor: Colors.black87,
      colorText: Colors.white,
      margin: const EdgeInsets.all(12),
      borderRadius: 12,
      duration: const Duration(seconds: 3),
    );

    debugPrint('SessionController error: $message');
  }

  // داخل class SessionController

  String getPatientImageUrl(String? path) {
    if (path == null || path.isEmpty) return '';
    if (path.startsWith('http')) return path;

    // نستخدم رابط السيرفر الأساسي من ApiConfig
    // عادة في Laravel تكون الصور داخل مجلد storage
    return "${ApiConfig.baseUrl}/storage/$path";
  }

  Future<void> loadFromServer() async {
    try {
      final api = ApiService.instance;

      final patientsData = await api.fetchPatients();
      patients
        ..clear()
        ..addAll(
          patientsData.map((e) => PatientProfile.fromJson(e)).toList(),
        );

      final analysesData = await api.fetchAnalyses();
      history
        ..clear()
        ..addAll(analysesData);
    } catch (e) {
      _showFriendlyError(e);
    }
  }

  Future<void> loadAllData() async {
    try {
      final api = ApiService.instance;

      /// 🔥 1. جلب المرضى من السيرفر
      final patientsData = await api.fetchPatients();

      patients
        ..clear()
        ..addAll(
          patientsData.map((e) => PatientProfile.fromJson(e)).toList(),
        );

      /// 🔥 2. جلب التحاليل
      final analysesData = await api.fetchAnalyses();

      history
        ..clear()
        ..addAll(analysesData);

      /// ❌ لا تعيد بناء المرضى من التحاليل
      // _rebuildPatientsFromHistory(save: true);  ← احذفه

      update();
    } catch (e) {
      _showFriendlyError(e);
    }
  }

  PatientDraft draft = PatientDraft();
  AnalysisResult? latestResult;
  AnalysisRecord? latestRecord;

  final List<AnalysisRecord> history = <AnalysisRecord>[];
  final List<PatientProfile> patients = <PatientProfile>[];

  bool get isReady => _ready;
  bool get onboardingSeen => _onboardingSeen;
  bool get isAuthenticated => (_token ?? '').trim().isNotEmpty;
  String? get token => _token;
  Map<String, dynamic>? get user => _user;
  String? get userId => _user?['id']?.toString();

  List<AnalysisRecord> get userHistory {
    if (userId == null) return List<AnalysisRecord>.from(history);
    return history.where((record) => record.userId == null || record.userId == userId).toList();
  }

  List<PatientProfile> get userPatients {
    if (userId == null) return List<PatientProfile>.from(patients);

    return patients.where((p) => p.userId == userId).toList();
  }

  String _patientsKey([String? uid]) => 'patients_cache_${uid ?? userId ?? 'guest'}';

  Future<void> load() async {
    final prefs = await SharedPreferences.getInstance();

    _token = prefs.getString(_tokenKey) ?? prefs.getString(_legacyTokenKey);

    final userRaw = prefs.getString(_userKey);
    if (userRaw != null && userRaw.isNotEmpty) {
      try {
        final decoded = jsonDecode(userRaw);
        if (decoded is Map<String, dynamic>) {
          _user = decoded;
        }
      } catch (e) {
        _user = null;
        _showFriendlyError(e);
      }
    }

    _onboardingSeen = prefs.getBool(_seenIntroKey) ?? false;

    try {
      await _loadHistoryFromPrefs();
      await _loadPatientsFromPrefs();
    } catch (e) {
      _showFriendlyError(e);
    }

    if (history.isNotEmpty) {
      _rebuildPatientsFromHistory(save: false);
    }

    if (_token != null && _token!.isNotEmpty) {
      await ApiService.instance.setToken(_token!);
    }

    _ready = true;
    update();

    if (_token != null && _token!.isNotEmpty && userId != null) {
      try {
        final me = await ApiService.instance.me();
        final meUser = (me['user'] is Map)
            ? Map<String, dynamic>.from(me['user'] as Map)
            : (me is Map<String, dynamic> ? me : null);

        if (meUser != null && meUser.isNotEmpty) {
          _user = meUser;
          final prefs2 = await SharedPreferences.getInstance();
          await prefs2.setString(_userKey, jsonEncode(meUser));
          update();
        }
      } catch (e) {
        _showFriendlyError(e);
      }

      try {
        final serverHistory = await ApiService.instance.getPatientHistory(userId!);
        if (serverHistory.isNotEmpty) {
          history
            ..clear()
            ..addAll(serverHistory);
          await _saveHistoryToPrefs();
          _rebuildPatientsFromHistory(save: true);
        }
      } catch (e) {
        _showFriendlyError(e);
      }
    }
  }

  Future<void> markOnboardingSeen() async {
    final prefs = await SharedPreferences.getInstance();
    _onboardingSeen = true;
    await prefs.setBool(_seenIntroKey, true);
    update();
  }

  Future<void> saveAuth({
    required String token,
    Map<String, dynamic>? user,
  }) async {
    final prefs = await SharedPreferences.getInstance();

    _token = token.trim();
    _user = user;
    await loadFromServer();

    await prefs.setString(_tokenKey, _token!);
    await prefs.setString(_legacyTokenKey, _token!);
    update();

    if (user != null) {
      await prefs.setString(_userKey, jsonEncode(user));
    }

    await ApiService.instance.setToken(_token!);
    await _loadHistoryFromPrefs();
    await _loadPatientsFromPrefs();

    if (history.isNotEmpty) {
      _rebuildPatientsFromHistory(save: false);
    }

    update();
  }

  Future<void> logout() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_tokenKey);
    await prefs.remove(_legacyTokenKey);
    await prefs.remove(_userKey);

    _token = null;
    _user = null;
    draft = PatientDraft();
    latestResult = null;
    latestRecord = null;
    history.clear();
    patients.clear();

    try {
      await ApiService.instance.logout();
    } catch (e) {
      _showFriendlyError(e);
    }

    update();
  }

  void updateDraft(PatientDraft Function(PatientDraft current) updater) {
    try {
      draft = updater(draft);
      update();
    } catch (e) {
      _showFriendlyError(e, title: 'خطأ في التحديث');
      debugPrint('Draft update error: $e');
    }
  }

  void setDraftDirect(PatientDraft value) {
    draft = value;
    update();
  }

  void clearDraft({bool keepPatient = false}) {
    if (keepPatient) {
      draft = draft.copyWith(
        symptoms: const [],
        troponin: null,
        ckmb: null,
        ecgImagePath: null,
      );
    } else {
      draft = PatientDraft();
    }
    latestResult = null;
    latestRecord = null;
    update();
  }

  void attachDraftToPatient(PatientProfile patient) {
    draft = PatientDraft(
      name: patient.fullName,
      age: patient.age,
      gender: patient.gender,
      patientId: patient.id,
      previousStroke: false,
      symptoms: const [],
    );
    update();
  }

  void setResult(AnalysisResult result) {
    latestResult = result;
    update();
  }

  Future<void> addHistory(AnalysisRecord record) async {
    try {
      history.insert(0, record);
      latestRecord = record;
      latestResult = record.result;
      await _saveHistoryToPrefs();
      _rebuildPatientsFromHistory(save: true);
      update();
    } catch (e) {
      _showFriendlyError(e);
    }
  }

  Future<void> upsertPatient(PatientProfile patient) async {
    try {
      final key = _patientStorageKey(patient);

      final index = patients.indexWhere((p) => _patientStorageKey(p) == key);
      if (index >= 0) {
        patients[index] = patients[index].copyWith(
          id: patient.id ?? patients[index].id,
          userId: patient.userId ?? patients[index].userId,
          fullName: patient.fullName.isNotEmpty ? patient.fullName : patients[index].fullName,
          gender: patient.gender.isNotEmpty ? patient.gender : patients[index].gender,
          age: patient.age ?? patients[index].age,
          visitsCount: patient.visitsCount >= 0 ? patient.visitsCount : patients[index].visitsCount,
          latestResultLabel: patient.latestResultLabel ?? patients[index].latestResultLabel,
          latestResultScore: patient.latestResultScore ?? patients[index].latestResultScore,
          createdAt: patient.createdAt ?? patients[index].createdAt,
          updatedAt: patient.updatedAt ?? patients[index].updatedAt,
        );
      } else {
        patients.insert(0, patient);
      }

      await _savePatientsToPrefs();
      update();
    } catch (e) {
      _showFriendlyError(e);
    }
  }

  Future<void> createPatientAndStartVisit({
    required PatientProfile patient,
  }) async {
    try {
      await upsertPatient(patient);
      attachDraftToPatient(patient);
    } catch (e) {
      _showFriendlyError(e);
    }
  }

  PatientProfile? getPatientById(int? id) {
    if (id == null) return null;
    for (final patient in userPatients) {
      if (patient.id == id) return patient;
    }
    return null;
  }

  List<AnalysisRecord> recordsForPatient(PatientProfile patient) {
    if (patient.id != null) {
      return userHistory.where((record) => record.draft.patientId == patient.id).toList();
    }

    return userHistory.where((record) {
      final draftName = record.draft.name.trim().toLowerCase();
      final patientName = patient.fullName.trim().toLowerCase();
      return draftName == patientName &&
          (record.draft.gender.trim() == patient.gender.trim()) &&
          (record.draft.age ?? 0) == (patient.age ?? 0);
    }).toList();
  }

  PatientProfile _profileFromRecordGroup(String key, List<AnalysisRecord> records) {
    records.sort((a, b) => b.createdAt.compareTo(a.createdAt));
    final latest = records.first;
    final oldest = records.last;

    return PatientProfile(
      id: latest.draft.patientId,
      fullName: latest.draft.name.isNotEmpty ? latest.draft.name : 'مريض',
      gender: latest.draft.gender.isNotEmpty ? latest.draft.gender : 'ذكر',
      age: latest.draft.age,
      visitsCount: records.length,
      latestResultLabel: latest.result?.label,
      latestResultScore: latest.result?.confidence,
      createdAt: oldest.createdAt,
      updatedAt: latest.createdAt,
    );
  }

  String _patientStorageKey(PatientProfile patient) {
    final uid = patient.userId ?? userId ?? 'guest';
    if (patient.id != null) {
      return 'u_${uid}_id_${patient.id}';
    }
    return 'u_${uid}_name_${patient.fullName.trim().toLowerCase()}|gender_${patient.gender.trim().toLowerCase()}|age_${patient.age ?? 0}';
  }

  String _recordPatientKey(AnalysisRecord record) {
    final uid = record.userId ?? userId ?? 'guest';
    if (record.draft.patientId != null) {
      return 'u_${uid}_id_${record.draft.patientId}';
    }
    return 'u_${uid}_name_${record.draft.name.trim().toLowerCase()}|gender_${record.draft.gender.trim().toLowerCase()}|age_${record.draft.age ?? 0}';
  }

  Future<void> _loadHistoryFromPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    history
      ..clear()
      ..addAll(AnalysisRecord.listFromJson(prefs.getStringList(_historyKey)));
  }

  Future<void> _loadPatientsFromPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    final raw = prefs.getStringList(_patientsKey()) ?? <String>[];

    patients
      ..clear()
      ..addAll(
        raw.map((e) {
          try {
            final decoded = jsonDecode(e);
            if (decoded is Map<String, dynamic>) {
              return PatientProfile.fromJson(decoded);
            }
          } catch (e) {
            _showFriendlyError(e);
          }
          return null;
        }).whereType<PatientProfile>(),
      );
  }

  Future<void> _savePatientsToPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(
      _patientsKey(),
      patients.map((e) => jsonEncode(e.toJson())).toList(),
    );
  }

  Future<void> _saveHistoryToPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setStringList(
      _historyKey,
      history.map((e) => jsonEncode(e.toJson())).toList(),
    );
  }

  void _rebuildPatientsFromHistory({required bool save}) {
    final Map<String, List<AnalysisRecord>> groups = <String, List<AnalysisRecord>>{};

    for (final record in userHistory) {
      groups.putIfAbsent(_recordPatientKey(record), () => <AnalysisRecord>[]).add(record);
    }

    final existingByKey = <String, PatientProfile>{
      for (final patient in patients) _patientStorageKey(patient): patient,
    };

    final rebuilt = <PatientProfile>[];

    for (final entry in groups.entries) {
      final derived = _profileFromRecordGroup(entry.key, List<AnalysisRecord>.from(entry.value));
      final current = existingByKey[entry.key];

      rebuilt.add(
        (current ?? derived).copyWith(
          id: derived.id ?? current?.id,
          userId: derived.userId ?? current?.userId,
          fullName: derived.fullName.isNotEmpty ? derived.fullName : current?.fullName,
          gender: derived.gender.isNotEmpty ? derived.gender : current?.gender,
          age: derived.age ?? current?.age,
          visitsCount: entry.value.length,
          latestResultLabel: derived.latestResultLabel ?? current?.latestResultLabel,
          latestResultScore: derived.latestResultScore ?? current?.latestResultScore,
          createdAt: current?.createdAt ?? derived.createdAt,
          updatedAt: derived.updatedAt ?? current?.updatedAt,
        ),
      );
    }

    // keep patients that were created but have no analyses yet
    for (final patient in patients) {
      final key = _patientStorageKey(patient);
      if (!groups.containsKey(key) && (patient.userId == null || patient.userId == userId)) {
        rebuilt.add(patient);
      }
    }

    patients
      ..clear()
      ..addAll(rebuilt);

    if (save) {
      _savePatientsToPrefs();
    }
  }
}