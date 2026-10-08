import 'package:get/get.dart';

import '../api/api_service.dart';

class PatientDetailsController extends GetxController {
  var patient = {}.obs;
  var analyses = [].obs;
  var isLoading = false.obs;

  Future<void> fetchPatientDetails(int patientId) async {
    try {
      isLoading.value = true;

      final res = await ApiService.get('/patients/$patientId');

      patient.value = res['patient'] ?? {};
      analyses.value = res['analyses'] ?? [];
    } finally {
      isLoading.value = false;
    }
  }
}
