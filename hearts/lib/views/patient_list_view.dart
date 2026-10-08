// PatientListView.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/session_controller.dart';
import 'DetailedPatientProfile.dart';
import 'Patient_Details_View.dart';
import 'select_symptoms_view.dart';

class PatientListView extends StatefulWidget {
  final String title;
  final String subtitle;

  const PatientListView({
    super.key,
    required this.title,
    required this.subtitle,
  });

  @override
  State<PatientListView> createState() => _PatientListViewState();
}

class _PatientListViewState extends State<PatientListView> {
  @override
  void initState() {
    super.initState();
    final session = Get.find<SessionController>();
    session.loadAllData();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0B0B0D),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        title: Text(widget.title, style: const TextStyle(color: Colors.white)),
        centerTitle: true,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Color(0xFFE53935)),
          onPressed: () => Get.back(),
        ),
      ),
      body: GetBuilder<SessionController>(
        builder: (session) {
          final patients = session.patients;

          if (patients.isEmpty) {
            return const Center(
              child: Text(
                'لا يوجد مرضى مضافين حالياً',
                style: TextStyle(color: Colors.white38, fontSize: 16),
              ),
            );
          }

          return ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            physics: const BouncingScrollPhysics(),
            itemCount: patients.length,
            itemBuilder: (_, i) {
              final patient = patients[i];

              return GestureDetector(
                onTap: () {
                  // الانتقال لصفحة التفاصيل مع تمرير السجل المختار
                  Get.to(() => PatientHistoryView());
                },
                child: Container(
                  margin: const EdgeInsets.only(bottom: 15),
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E1E1E),
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: Colors.white10),
                  ),
                  child: Row(
                    children: [
                      // أيقونة المستخدم بتصميم متوهج
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.blueAccent.withOpacity(0.1),
                          shape: BoxShape.circle,
                        ),
                        child: const Icon(
                          Icons.person_outline_rounded,
                          color: Colors.blueAccent,
                          size: 26,
                        ),
                      ),
                      const SizedBox(width: 15),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              patient.fullName,
                              style: const TextStyle(
                                  color: Colors.white,
                                  fontSize: 17,
                                  fontWeight: FontWeight.bold),
                            ),
                            const SizedBox(height: 5),
                            Text(
                              "معرف المريض: ${patient.id ?? '-'}",
                              style: const TextStyle(color: Colors.white38, fontSize: 13),
                            ),
                          ],
                        ),
                      ),
                      // زر الإضافة السريع
                      IconButton(
                        icon: const Icon(Icons.add_circle_outline, color: Color(0xFFE53935), size: 30),
                        onPressed: () {
                          session.attachDraftToPatient(patient);
                          Get.to(() => const SelectSymptomsView());
                        },
                      ),
                    ],
                  ),
                ),
              );
            },
          );
        },
      ),
    );
  }
}