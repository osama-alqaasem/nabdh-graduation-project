import 'dart:convert';

import '../api/api_config.dart';

class PatientDraft {
  final String name;
  final int? age;
  final String gender;
  final bool previousStroke;
  final List<String> symptoms;
  final double? troponin;
  final double? ckmb;
  final String? ecgImagePath;
  final int? patientId;

  PatientDraft({
    this.name = '',
    this.age,
    this.gender = 'ذكر',
    this.previousStroke = false,
    this.symptoms = const [],
    this.troponin,
    this.ckmb,
    this.ecgImagePath,
    this.patientId,
  });

  PatientDraft copyWith({
    String? name,
    int? age,
    String? gender,
    bool? previousStroke,
    List<String>? symptoms,
    double? troponin,
    double? ckmb,
    String? ecgImagePath,
    int? patientId,
  }) {
    return PatientDraft(
      name: name ?? this.name,
      age: age ?? this.age,
      gender: gender ?? this.gender,
      previousStroke: previousStroke ?? this.previousStroke,
      symptoms: symptoms ?? this.symptoms,
      troponin: troponin ?? this.troponin,
      ckmb: ckmb ?? this.ckmb,
      ecgImagePath: ecgImagePath ?? this.ecgImagePath,
      patientId: patientId ?? this.patientId,
    );
  }

  Map<String, dynamic> toPatientJson() {
    return {
      'full_name': name.isNotEmpty ? name : 'Unknown',
      'age': age ?? 0,
      'gender': gender,
    };
  }

  Map<String, dynamic> toAnalysisFields() {
    return {
      'full_name': name,
      'age': age ?? 0,
      'gender': gender,
      'sex': (gender == 'أنثى' || gender.toLowerCase() == 'female') ? 0 : 1,
      'has_previous_heart_attack': previousStroke ? 1 : 0,
      'troponin': troponin ?? 0,
      'ckmb': ckmb ?? 0,
      'chest_pain': symptoms.contains('chest_pain') ? 1 : 0,
      'shortness': symptoms.contains('shortness') ? 1 : 0,
      'nausea': symptoms.contains('nausea') ? 1 : 0,
      'dizziness': symptoms.contains('dizziness') ? 1 : 0,
      'sweating': symptoms.contains('sweating') ? 1 : 0,
      'fatigue': symptoms.contains('fatigue') ? 1 : 0,
    };
  }

  Map<String, dynamic> toJson() => {
        'name': name,
        'age': age,
        'gender': gender,
        'previousStroke': previousStroke,
        'symptoms': symptoms,
        'troponin': troponin,
        'ckmb': ckmb,
        'ecgImagePath': ecgImagePath,
        'patientId': patientId,
      };

  factory PatientDraft.fromJson(Map<String, dynamic> json) {
    return PatientDraft(
      name: json['name']?.toString() ?? '',
      age: json['age'] is int ? json['age'] as int : int.tryParse(json['age']?.toString() ?? ''),
      gender: json['gender']?.toString() ?? 'ذكر',
      previousStroke: json['previousStroke'] == true || json['previousStroke'] == 1,
      symptoms: (json['symptoms'] as List?)?.map((e) => e.toString()).toList() ?? [],
      troponin: (json['troponin'] as num?)?.toDouble(),
      ckmb: (json['ckmb'] as num?)?.toDouble(),
      ecgImagePath: json['ecgImagePath']?.toString(),
      patientId: json['patientId'] is int ? json['patientId'] as int : int.tryParse(json['patientId']?.toString() ?? ''),
    );
  }
}

class AnalysisResult {
  final bool isPositive;
  final String label;
  final double confidence;
  final String message;
  final Map<String, dynamic> details;
  final double? imageScore;
  final double? bloodScore;
  final double? symptomsScore;
  final String? range;
  final Map<String, dynamic> raw;

  AnalysisResult({
    required this.isPositive,
    required this.label,
    required this.message,
    this.confidence = 0,
    this.details = const <String, dynamic>{},
    this.imageScore,
    this.bloodScore,
    this.symptomsScore,
    this.range,
    this.raw = const <String, dynamic>{},
  });

  String get displayLabel => label.isNotEmpty ? label : message;

  factory AnalysisResult.fromApi(Map<String, dynamic> json) {
    final rawLabel = json['label'] ?? json['result'] ?? json['result_text'] ?? json['message'];
    final String label = _normalizeLabel(rawLabel?.toString() ?? 'طبيعي');
    final score = (json['score'] is num) ? (json['score'] as num).toDouble() : (json['probability'] is num ? (json['probability'] as num).toDouble() : 0.0);

    Map<String, dynamic> parsedDetails = <String, dynamic>{};
    if (json['details'] is Map) {
      parsedDetails = Map<String, dynamic>.from(json['details']);
    }

    return AnalysisResult(
      isPositive: label != 'طبيعي',
      label: label,
      message: json['message']?.toString() ?? label,
      confidence: score,
      imageScore: (json['image_score'] as num?)?.toDouble() ?? (json['imageScore'] as num?)?.toDouble(),
      bloodScore: (json['tabular_score'] as num?)?.toDouble() ?? (json['bloodScore'] as num?)?.toDouble(),
      symptomsScore: (json['symptoms_score'] as num?)?.toDouble() ?? (json['symptomsScore'] as num?)?.toDouble(),
      range: json['range']?.toString(),
      details: parsedDetails,
      raw: Map<String, dynamic>.from(json),
    );
  }

  static String _normalizeLabel(String label) {
    final normalized = label.trim();
    if (normalized.isEmpty) return 'طبيعي';

    if (['طبيعي', 'اضطراب بنبض القلب', 'مصاب بجلطة', 'مصاب بجلطة سابقة'].contains(normalized)) {
      return normalized;
    }

    final lower = normalized.toLowerCase().replaceAll('_', ' ').replaceAll('-', ' ');
    if (lower.contains('arrhythm') || lower.contains('اضطراب') || lower.contains('نبض')) return 'اضطراب بنبض القلب';
    if (lower.contains('previous') || lower.contains('old') || lower.contains('سابق')) return 'مصاب بجلطة سابقة';
    if (lower.contains('positive') || lower.contains('stroke') || lower.contains('heart attack') || lower.contains('جلطة')) return 'مصاب بجلطة';
    if (lower.contains('normal') || lower.contains('negative') || lower.contains('طبيعي')) return 'طبيعي';
    return normalized;
  }

  Map<String, dynamic> toJson() => {
        'isPositive': isPositive,
        'label': label,
        'message': message,
        'confidence': confidence,
        'details': details,
        'imageScore': imageScore,
        'bloodScore': bloodScore,
        'symptomsScore': symptomsScore,
        'range': range,
        'raw': raw,
      };

  factory AnalysisResult.fromJson(Map<String, dynamic> json) {
    return AnalysisResult.fromApi(json);
  }
}

class AnalysisRecord {
  final PatientDraft draft;
  final AnalysisResult? result;
  final String? userId;
  final DateTime createdAt;

  AnalysisRecord({
    required this.draft,
    required this.result,
    this.userId,
    DateTime? createdAt,
  }) : createdAt = createdAt ?? DateTime.now();

  Map<String, dynamic> toJson() => {
        'draft': draft.toJson(),
        'result': result?.toJson(),
        'userId': userId,
        'createdAt': createdAt.toIso8601String(),
      };
  factory AnalysisRecord.fromJson(Map<String, dynamic>? json) {
    final safe = json ?? <String, dynamic>{};

    double? parseDouble(dynamic value) {
      if (value == null) return null;
      if (value is num) return value.toDouble();
      return double.tryParse(value.toString());
    }

    int? parseInt(dynamic value) {
      if (value == null) return null;
      if (value is int) return value;
      return int.tryParse(value.toString());
    }

    final patientMap = safe['patient'] is Map
        ? Map<String, dynamic>.from(safe['patient'])
        : <String, dynamic>{};

    final symptoms = <String>[];
    final rawSymptoms = safe['symptoms'];

    if (rawSymptoms is List) {
      for (final s in rawSymptoms) {
        if (s is Map) {
          final value = s['value'];
          final checked = value == null ||
              value == true ||
              value == 1 ||
              value.toString() == '1';

          final name = (s['name'] ?? s['label'] ?? s['symptom'])?.toString() ??
              '';
          if (checked && name
              .trim()
              .isNotEmpty) {
            symptoms.add(name.trim());
          }
        } else {
          final name = s?.toString().trim() ?? '';
          if (name.isNotEmpty) {
            symptoms.add(name);
          }
        }
      }
    }

    AnalysisResult? parsedResult;
    if (safe['result'] is Map) {
      parsedResult = AnalysisResult.fromApi(
        Map<String, dynamic>.from(safe['result']),
      );
    } else if (safe['result_text'] != null || safe['label'] != null) {
      parsedResult = AnalysisResult.fromApi({
        'label': safe['result_text'] ?? safe['label'],
        'probability': safe['result_score'] ?? safe['confidence'] ?? 0,
      });
    }

    return AnalysisRecord(
      draft: PatientDraft(
        name: (patientMap['full_name'] ??
            safe['full_name'] ??
            safe['name'] ??
            '')
            .toString(),
        age: parseInt(patientMap['age'] ?? safe['age']),
        gender: (patientMap['gender'] ?? safe['gender'] ?? 'ذكر').toString(),
        previousStroke: safe['previousStroke'] == true ||
            safe['previousStroke'] == 1 ||
            safe['has_previous_heart_attack'] == true ||
            safe['has_previous_heart_attack'] == 1,
        symptoms: symptoms,
        troponin: parseDouble(safe['troponin'] ?? patientMap['troponin']),
        ckmb: parseDouble(safe['ckmb'] ?? patientMap['ckmb']),
        ecgImagePath: (safe['ecg_image_path'] ??
            safe['ecg_image_url'] ??
            patientMap['ecg_image_path'] ??
            patientMap['ecg_image_url'])
            ?.toString(),
        patientId: parseInt(patientMap['id'] ?? safe['patient_id']),
      ),
      result: parsedResult,
      userId: safe['user_id']?.toString(),
      createdAt: DateTime.tryParse(safe['created_at']?.toString() ?? '') ??
          DateTime.now(),
    );
  }

  static List<AnalysisRecord> listFromJson(List<String>? list) {
    if (list == null) return [];
    return list.map((e) {
      try {
        if (e.isEmpty) return null;
        final decoded = jsonDecode(e);
        if (decoded is Map) {
          return AnalysisRecord.fromJson(Map<String, dynamic>.from(decoded));
        }
      } catch (_) {}
      return null;
    }).whereType<AnalysisRecord>().toList();
  }
}

class DashboardBanner {
  final String title;
  final String? description;
  final String imageUrl; // هنا يتم تخزين اسم الملف القادم من السيرفر
  final String? buttonText;
  final String? buttonUrl;

  DashboardBanner({
    required this.title,
    required this.imageUrl,
    this.description,
    this.buttonText,
    this.buttonUrl,
  });

  // دالة مساعدة لإرجاع الرابط الكامل للصورة من مجلد banners
  String get fullImageUrl {
    if (imageUrl.isEmpty) return '';
    if (imageUrl.startsWith('http')) return imageUrl;

    // الربط مع المسار الذي حددته في Laravel
    return "${ApiConfig.baseUrl}/storage/banners/$imageUrl";
  }

  factory DashboardBanner.fromJson(Map<String, dynamic> json) {
    return DashboardBanner(
      title: json['title'] ?? '',
      description: json['description'],
      // سيأتي من لارافل بفضل خاصية $appends
      imageUrl: json['image_url'] ?? '',
      buttonText: json['button_text'],
      buttonUrl: json['button_url'],
    );
  }

  Map<String, dynamic> toJson() {
    return {
      'title': title,
      'description': description,
      'image_url': imageUrl,
      'button_text': buttonText,
      'button_url': buttonUrl,
    };
  }
}