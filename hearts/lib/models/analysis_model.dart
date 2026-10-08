class AnalysisModel {
  final int id;

  AnalysisModel({required this.id});

  factory AnalysisModel.fromJson(Map<String, dynamic> json) {
    return AnalysisModel(
      id: json['analysis_id'],
    );
  }
}