import 'package:flutter/material.dart';
import 'package:get/get.dart';

import 'Patient_Details_View.dart';
import 'creat_account_view.dart';
import 'run_analysis_view.dart';
import 'welcome_view.dart';

/// يعرض 4 واجهات تلقائياً فقط في أول فتح للتطبيق.
class FirstLaunchFlowView extends StatefulWidget {
  const FirstLaunchFlowView({super.key});

  @override
  State<FirstLaunchFlowView> createState() => _FirstLaunchFlowViewState();
}

class _FirstLaunchFlowViewState extends State<FirstLaunchFlowView> {
  static const Duration _stepDuration = Duration(seconds: 2);

  @override
  void initState() {
    super.initState();
    _runFlow();
  }

  Future<void> _runFlow() async {
    // 1) WelcomeView
    await Future.delayed(_stepDuration);
    if (!mounted) return;
    Get.offAll(() => const PatientHistoryView());

    // 2) PatientDetailsView -> RunAnalysisView
    await Future.delayed(_stepDuration);
    if (!mounted) return;
    Get.offAll(() => const RunAnalysisView());

    // 3) RunAnalysisView -> CreateAccountView
    await Future.delayed(_stepDuration);
    if (!mounted) return;
    Get.offAll(() => CreateAccountView());
  }

  @override
  Widget build(BuildContext context) {
    // نبدأ بواجهة WelcomeView مباشرة كجزء من التدفق.
    return WelcomeView();
  }
}

