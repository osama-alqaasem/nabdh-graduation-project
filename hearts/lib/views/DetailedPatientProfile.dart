import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/PatientDetailsController.dart';
import 'add_analysis_page.dart';

class DetailedPatientProfiles extends StatefulWidget {
  final int patientId;

  const DetailedPatientProfiles({super.key, required this.patientId});

  @override
  State<DetailedPatientProfiles> createState() => _DetailedPatientProfilesState();
}

class _DetailedPatientProfilesState extends State<DetailedPatientProfiles> {
  final PatientDetailsController controller = Get.put(PatientDetailsController());
  bool _loaded = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (!_loaded) {
      _loaded = true;
      controller.fetchPatientDetails(widget.patientId);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text("تفاصيل المريض")),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        return Column(
          children: [
            Text(controller.patient['full_name']?.toString() ?? ''),
            Text(controller.patient['gender']?.toString() ?? ''),
            Text(controller.patient['age']?.toString() ?? ''),
            const Divider(),
            Expanded(
              child: ListView.builder(
                itemCount: controller.analyses.length,
                itemBuilder: (_, i) {
                  final analysis = controller.analyses[i];
                  return ListTile(
                    title: Text(analysis['result_text']?.toString() ?? ''),
                    subtitle: Text(analysis['created_at']?.toString() ?? ''),
                    onTap: () {},
                  );
                },
              ),
            ),
            ElevatedButton(
              onPressed: () {
                Get.to(() => AddAnalysisPage(widget.patientId));
              },
              child: const Text("إضافة تحليل جديد"),
            )
          ],
        );
      }),
    );
  }
}
