import 'dart:convert';
import 'dart:io';

import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

import '../models/app_models.dart';
import 'api_config.dart';

class ApiService {
  ApiService._();
  static final ApiService instance = ApiService._();

  final http.Client _client = http.Client();
  String? _token;

  Uri _uri(String path) => Uri.parse('${ApiConfig.baseUrl}$path');

  Future<void> setToken(String token) async {
    _token = token.trim();
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', _token!);
    await prefs.setString('token', _token!);
  }

  Future<void> loadToken() async {
    if (_token != null && _token!.isNotEmpty) return;
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString('auth_token') ?? prefs.getString('token');
  }

  Future<void> logout() async {
    final headers = await _headers();
    try {
      await _client.post(_uri(ApiConfig.logoutPath), headers: headers);
    } catch (_) {}

    _token = null;
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
    await prefs.remove('token');
  }

  Future<Map<String, String>> _headers({bool jsonContentType = true}) async {
    await loadToken();

    final headers = <String, String>{
      'Accept': 'application/json',
      if (jsonContentType) 'Content-Type': 'application/json',
    };

    if (_token != null && _token!.trim().isNotEmpty) {
      headers['Authorization'] = 'Bearer ${_token!.trim()}';
    }

    return headers;
  }

  static Future<dynamic> get(String endpoint, {String? token}) async {
    final response = await http.get(
      Uri.parse('${ApiConfig.baseUrl}$endpoint'),
      headers: {
        'Accept': 'application/json',
        if (token != null) 'Authorization': 'Bearer $token',
      },
    );

    final text = utf8.decode(response.bodyBytes);

    if (response.statusCode < 200 || response.statusCode >= 300) {
      dynamic decoded;
      try {
        decoded = text.trim().isNotEmpty ? jsonDecode(text) : null;
      } catch (_) {
        decoded = null;
      }

      String message = 'حدث خطأ غير معروف';

      if (decoded is Map<String, dynamic>) {
        if (decoded['message'] != null) {
          message = decoded['message'].toString();
        } else if (decoded['error'] != null) {
          message = decoded['error'].toString();
        } else if (decoded['errors'] != null && decoded['errors'] is Map) {
          final errors = decoded['errors'] as Map;
          final firstError = errors.values.isNotEmpty ? errors.values.first : null;
          if (firstError is List && firstError.isNotEmpty) {
            message = firstError.first.toString();
          } else {
            message = 'البيانات المدخلة غير صحيحة';
          }
        }
      } else if (text.trim().isNotEmpty) {
        message = text;
      }

      throw ApiException(message, response.statusCode, data: decoded);
    }

    if (text.trim().isEmpty) return {};
    return jsonDecode(text);
  }

  Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
  }) async {
    final response = await _client.post(
      _uri(ApiConfig.registerPath),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
      }),
    );

    final data = _decode(response);
    final token = data['token']?.toString() ?? data['access_token']?.toString();
    if (token != null && token.isNotEmpty) {
      await setToken(token);
    }
    return data;
  }

  Future<Map<String, dynamic>> login({
    required String email,
    required String password,
  }) async {
    final response = await _client.post(
      _uri(ApiConfig.loginPath),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'email': email,
        'password': password,
      }),
    );

    final data = _decode(response);
    final token = data['token']?.toString() ??
        data['api_token']?.toString() ??
        data['access_token']?.toString() ??
        data['jwt']?.toString();
    if (token != null && token.isNotEmpty) {
      await setToken(token);
    }
    return data;
  }

  Future<int> savePatient({
    required PatientDraft draft,
    required String userId,
  }) async {
    final body = draft.toPatientJson()..addAll({'user_id': userId});
    final headers = await _headers();

    final response = await _client.post(
      _uri(ApiConfig.patientPath),
      headers: headers,
      body: jsonEncode(body),
    );

    final data = _decode(response);
    final value =
        data['data']?['id'] ?? data['data']?['patient_id'] ?? data['patient_id'] ?? data['id'];
    final parsed = value is int ? value : int.tryParse(value?.toString() ?? '');
    if (parsed == null) {
      throw Exception('patient_id not returned');
    }
    return parsed;
  }

  Future<AnalysisResult> analyze({required PatientDraft draft}) async {
    await loadToken();
    final prefs = await SharedPreferences.getInstance();
    final token = (_token ?? prefs.getString('auth_token'))?.trim();

    final request = http.MultipartRequest(
      'POST',
      Uri.parse('${ApiConfig.aiBaseUrl}${ApiConfig.predictPath}'),
    );
    if (token != null && token.isNotEmpty) {
      request.headers['Authorization'] = 'Bearer $token';
    }
    request.headers['Accept'] = 'application/json';
    print(token);

    final fields = draft.toAnalysisFields();
    request.fields.addAll({
      'full_name': (draft.name).trim().isEmpty ? 'Unknown' : draft.name.trim(),
      'age': (fields['age'] ?? 0).toString(),
      'sex': (draft.gender == 'أنثى' || draft.gender.toLowerCase() == 'female') ? '0' : '1',
      'troponin': (fields['troponin'] ?? 0).toString(),
      'ckmb': (fields['ckmb'] ?? 0).toString(),
      'has_previous_heart_attack': (fields['has_previous_heart_attack'] ?? 0).toString(),
      'chest_pain': (fields['chest_pain'] ?? 0).toString(),
      'shortness': (fields['shortness'] ?? 0).toString(),
      'nausea': (fields['nausea'] ?? 0).toString(),
      'dizziness': (fields['dizziness'] ?? 0).toString(),
      'sweating': (fields['sweating'] ?? 0).toString(),
      'fatigue': (fields['fatigue'] ?? 0).toString(),
    });

    final imagePath = draft.ecgImagePath;
    if (imagePath != null && imagePath.isNotEmpty) {
      final file = File(imagePath);
      if (await file.exists()) {
        request.files.add(await http.MultipartFile.fromPath('file', file.path));
      }
    }

    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);
    return AnalysisResult.fromApi(_decode(response));
  }

  Future<List<AnalysisRecord>> fetchAnalyses() async {
    final headers = await _headers();

    final res = await _client.get(
      _uri('/analyses'),
      headers: headers,
    );

    final data = _decode(res);

    final list = data is List ? data : data['data'];

    return list.map<AnalysisRecord>((e) => AnalysisRecord.fromJson(e)).toList();
  }

  Future<List<dynamic>> fetchPatients() async {
    final headers = await _headers();

    final res = await _client.get(
      _uri('/patients'),
      headers: headers,
    );

    final data = _decode(res);
    return data is List ? data : data['data'];
  }

  Future<void> saveAnalysis({
    required PatientDraft draft,
    required AnalysisResult result,
    String? userId,
  }) async {
    final uri = _uri(ApiConfig.analysesPath);
    final headers = await _headers(jsonContentType: false);

    final request = http.MultipartRequest('POST', uri);
    request.headers.addAll(headers);

    void addField(String key, Object? value) {
      if (value == null) return;
      final text = value.toString().trim();
      if (text.isEmpty) return;
      request.fields[key] = text;
    }

    addField('patient_id', draft.patientId);
    addField('full_name', draft.name);
    addField('gender', draft.gender);
    addField('age', draft.age);
    addField('troponin', draft.troponin);
    addField('ckmb', draft.ckmb);

    request.fields['result_score'] = result.confidence.toString();
    request.fields['result_text'] = result.label;
    request.fields['probability'] = result.confidence.toString();
    request.fields['label'] = result.label;
    request.fields['image_score'] = result.imageScore.toString();
    request.fields['tabular_score'] = result.bloodScore.toString();
    request.fields['symptoms_score'] = result.symptomsScore.toString();

    request.fields['raw_result'] =
    result.raw is String ? result.raw as String : jsonEncode(result.raw ?? {});

    final selectedSymptoms = <String>[
      if (draft.symptoms.contains('chest_pain')) 'chest_pain',
      if (draft.symptoms.contains('shortness')) 'shortness',
      if (draft.symptoms.contains('nausea')) 'nausea',
      if (draft.symptoms.contains('dizziness')) 'dizziness',
      if (draft.symptoms.contains('sweating')) 'sweating',
      if (draft.symptoms.contains('fatigue')) 'fatigue',
    ];

    request.fields['symptoms'] = jsonEncode(selectedSymptoms);

    request.fields['tests'] = jsonEncode({
      'troponin': draft.troponin,
      'ckmb': draft.ckmb,
    });

    final imagePath = draft.ecgImagePath;
    if (imagePath != null && imagePath.isNotEmpty) {
      final file = File(imagePath);
      if (await file.exists()) {
        request.files.add(
          await http.MultipartFile.fromPath('file', file.path),
        );
      }
    }

    final streamed = await request.send();
    final response = await http.Response.fromStream(streamed);

    print('SAVE STATUS: ${response.statusCode}');
    print('SAVE BODY: ${response.body}');

    _decode(response);
  }

  Future<Map<String, dynamic>> me() async {
    final headers = await _headers();
    final response = await _client.get(_uri(ApiConfig.mePath), headers: headers);
    return _decode(response);
  }

  Future<List<AnalysisRecord>> getPatientHistory(String userId) async {
    final headers = await _headers();
    final response = await _client.get(_uri(ApiConfig.historyPath), headers: headers);

    final decoded = _decode(response);
    final list = decoded['data'] ?? decoded;
    if (list is List) {
      return list
          .whereType<Map>()
          .map((e) => AnalysisRecord.fromJson(Map<String, dynamic>.from(e)))
          .toList();
    }
    return [];
  }

  Future<List<DashboardBanner>> fetchBanners() async {
    final res = await http.get(Uri.parse('${ApiConfig.baseUrl}/banners'));

    final data = jsonDecode(res.body) as List;

    return data.map((e) => DashboardBanner.fromJson(e)).toList();
  }

  Map<String, dynamic> _extractErrorMessage(dynamic data) {
    if (data is Map<String, dynamic>) {
      if (data['message'] != null) {
        return {'message': data['message'].toString()};
      }

      if (data['error'] != null) {
        return {'message': data['error'].toString()};
      }

      if (data['errors'] != null && data['errors'] is Map) {
        final errors = data['errors'] as Map;
        final firstError = errors.values.isNotEmpty ? errors.values.first : null;

        if (firstError is List && firstError.isNotEmpty) {
          return {'message': firstError.first.toString()};
        }

        return {'message': 'البيانات المدخلة غير صحيحة'};
      }
    }

    return {'message': 'حدث خطأ غير متوقع'};
  }

  Map<String, dynamic> _decode(http.Response response) {
    final text = utf8.decode(response.bodyBytes);

    if (response.statusCode < 200 || response.statusCode >= 300) {
      dynamic decoded;
      try {
        decoded = text.trim().isNotEmpty ? jsonDecode(text) : null;
      } catch (_) {
        decoded = null;
      }

      final errorMap = _extractErrorMessage(decoded);
      throw ApiException(
        errorMap['message'] ?? 'حدث خطأ غير معروف',
        response.statusCode,
        data: decoded,
      );
    }

    if (text.trim().isEmpty) return {};

    final data = jsonDecode(text);
    if (data is Map<String, dynamic>) return data;

    return {'data': data};
  }
}

class ApiException implements Exception {
  final String message;
  final int statusCode;
  final dynamic data;

  ApiException(
      this.message,
      this.statusCode, {
        this.data,
      });

  @override
  String toString() => 'ApiException($statusCode): $message';
}