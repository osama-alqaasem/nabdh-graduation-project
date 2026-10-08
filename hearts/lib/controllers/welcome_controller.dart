import 'package:flutter/material.dart';
import 'package:get/get.dart';

// models
import '../models/welcome_model.dart';

// views
import '../views/history_view.dart';
import '../views/patient_details_view.dart';
import '../views/run_analysis_view.dart';
import '../views/creat_account_view.dart';
import '../views/login_view.dart';
import '../views/add_patient_view.dart';
import '../views/select_symptoms_view.dart';
import '../views/add_test_result_view.dart';
import '../views/uplod_ecg_view.dart';
import '../views/result_view.dart';

class WelcomeController extends GetxController {

  // -----------------------------
  // Slides
  // -----------------------------
  static const List<OnboardingSlide> slides = [
    OnboardingSlide(
      title: 'مرحبًا بك',
      description: 'ابدأ رحلتك لتقييم مخاطر القلب بشكل سريع ومرتب.',
      icon: Icons.medical_services,
      bullets: [],
    ),
    OnboardingSlide(
      title: 'أدخل بيانات المريض',
      description: 'املأ المعلومات الأساسية، وحدد الأعراض والنتائج المطلوبة.',
      icon: Icons.check_circle_rounded,
      bullets: [
        'الاسم والعمر والجنس',
        'هل توجد جلطة سابقة؟',
        'الأعراض والتحاليل',
      ],
    ),
    OnboardingSlide(
      title: 'شغّل التحليل',
      description: 'ارفع صورة تخطيط القلب ECG لتبدأ عملية التقييم.',
      icon: Icons.monitor_heart,
      bullets: [
        'رفع ECG',
        'إرسال البيانات للسيرفر',
        'استلام النتيجة',
      ],
    ),
    OnboardingSlide(
      title: 'اعرض النتائج',
      description: 'ستظهر النتيجة بشكل واضح مع شرح مختصر لتسهيل القرار.',
      icon: Icons.analytics,
      bullets: [
        'مؤشر المخاطر',
        'تفاصيل إضافية عند الحاجة',
      ],
    ),
  ];

  // -----------------------------
  // Navigation
  // -----------------------------

  void goNext() {
    Get.to(() => const HistoryView());
  }

  void goToAnalysis() {
    Get.to(() => const RunAnalysisView());
  }

  void goToCreateAccount() {
    Get.to(() => const CreateAccountView());
  }

  void goToLogin() {
    Get.to(() => const LoginView());
  }

  void goToAddPatient() {
    Get.to(() => const AddPatientView());
  }

  void goToSymptoms() {
    Get.to(() => const SelectSymptomsView());
  }

  void goToTestResults(Set<String> symptoms) {
    debugPrint("Symptoms: $symptoms");

    Get.to(
          () => const AddTestResultsView(),
      arguments: symptoms,
    );
  }

  void goToUploadEcg() {
    Get.to(() => const UploadEcgView());
  }

  /// ⚠️ مهم: ResultScreen يحتاج record
  void goToResult(dynamic record) {
    Get.to(() => ResultScreen(record: record));
  }

  void goToHistory() {
    Get.to(() => const HistoryView());
  }

  // void goToPatientDetails(dynamic record) {
  //   Get.to(() => DetailedPatientProfile(record: record));
  // }

  void goBack() {
    Get.back();
  }
}