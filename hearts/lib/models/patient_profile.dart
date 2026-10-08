class PatientProfile {
  final int? id;
  final String? userId;
  final String fullName;
  final String gender;
  final int? age;
  final int visitsCount;
  final String? latestResultLabel;
  final double? latestResultScore;
  final DateTime? createdAt;
  final DateTime? updatedAt;

  final String? imageUrl; // 🔥 صورة المريض

  const PatientProfile({
    this.id,
    this.userId,
    required this.fullName,
    required this.gender,
    this.age,
    this.visitsCount = 0,
    this.latestResultLabel,
    this.latestResultScore,
    this.createdAt,
    this.updatedAt,
    this.imageUrl,
  });

  PatientProfile copyWith({
    int? id,
    String? userId,
    String? fullName,
    String? gender,
    int? age,
    int? visitsCount,
    String? latestResultLabel,
    double? latestResultScore,
    DateTime? createdAt,
    DateTime? updatedAt,
    String? imageUrl, // 🔥 مهم
  }) {
    return PatientProfile(
      id: id ?? this.id,
      userId: userId ?? this.userId,
      fullName: fullName ?? this.fullName,
      gender: gender ?? this.gender,
      age: age ?? this.age,
      visitsCount: visitsCount ?? this.visitsCount,
      latestResultLabel: latestResultLabel ?? this.latestResultLabel,
      latestResultScore: latestResultScore ?? this.latestResultScore,
      createdAt: createdAt ?? this.createdAt,
      updatedAt: updatedAt ?? this.updatedAt,
      imageUrl: imageUrl ?? this.imageUrl, // 🔥
    );
  }

  Map<String, dynamic> toJson() => {
    'id': id,
    'userId': userId,
    'fullName': fullName,
    'gender': gender,
    'age': age,
    'visitsCount': visitsCount,
    'latestResultLabel': latestResultLabel,
    'latestResultScore': latestResultScore,
    'createdAt': createdAt?.toIso8601String(),
    'updatedAt': updatedAt?.toIso8601String(),
    'imageUrl': imageUrl, // 🔥 حفظ الصورة
  };

  factory PatientProfile.fromJson(Map<String, dynamic> json) {
    final patient = json['patient'] is Map
        ? Map<String, dynamic>.from(json['patient'])
        : json;

    String? extractImage() {
      return patient['image_url']?.toString() ??
          patient['ecg_image_url']?.toString() ??
          patient['ecg_image_path']?.toString() ??
          json['image_url']?.toString();
    }

    return PatientProfile(
      id: patient['id'] is int
          ? patient['id']
          : int.tryParse(patient['id']?.toString() ?? ''),

      userId: patient['user_id']?.toString(),

      fullName: patient['full_name']?.toString() ??
          patient['name']?.toString() ??
          'غير معروف',

      gender: patient['gender']?.toString() ?? 'ذكر',

      age: patient['age'] is int
          ? patient['age']
          : int.tryParse(patient['age']?.toString() ?? ''),

      visitsCount: json['visits_count'] ?? 0,

      latestResultLabel: json['latest_result_label']?.toString(),
      latestResultScore: (json['latest_result_score'] is num)
          ? (json['latest_result_score'] as num).toDouble()
          : double.tryParse(json['latest_result_score']?.toString() ?? ''),

      createdAt: DateTime.tryParse(
          patient['created_at']?.toString() ?? ''),

      updatedAt: DateTime.tryParse(
          patient['updated_at']?.toString() ?? ''),

      imageUrl: extractImage(), // 🔥 أهم نقطة
    );
  }
}