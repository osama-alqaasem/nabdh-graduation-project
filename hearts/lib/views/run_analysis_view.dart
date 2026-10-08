import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../api/api_service.dart';
import '../app_scope.dart';
import '../models/app_models.dart';
import '../widget_build.dart';
import 'result_view.dart';

class RunAnalysisView extends StatefulWidget {
  const RunAnalysisView({super.key});

  @override
  State<RunAnalysisView> createState() => _RunAnalysisViewState();
}

class _RunAnalysisViewState extends State<RunAnalysisView> {
  bool _loading = false;
  String? _error;

  Future<void> _submitAnalysis() async {
    final session = AppScope.of(context);
    final draft = session.draft;

    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      if (draft.name.trim().isEmpty || draft.ecgImagePath == null || draft.ecgImagePath!.isEmpty) {
        throw Exception('يرجى إكمال بيانات المريض ورفع صورة ECG أولاً');
      }

      final result = await ApiService.instance.analyze(draft: draft);

      if (session.isAuthenticated) {
        try {
          await ApiService.instance.saveAnalysis(draft: draft, result: result, userId: session.userId);
        } catch (_) {
          // keep local record even if save to Laravel failed
        }
      }

      final record = AnalysisRecord(
        draft: draft,
        result: result,
        userId: session.userId,
      );

      session.setResult(result);
      await session.addHistory(record);

      if (!mounted) return;
      Get.off(() => ResultScreen(record: record));
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final draft = AppScope.of(context).draft;

    return Scaffold(
      backgroundColor: const Color(0xFF0B0B0D),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        title: const Text('مراجعة التحليل', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(18),
        child: Column(
          children: [
            const ScreenHeader(
              title: 'راجع البيانات قبل الإرسال',
              subtitle: 'سيتم إرسال البيانات إلى خادم Flask للحصول على النتيجة ثم حفظها في السجل.',
              icon: Icons.send_and_archive,
            ),
            const SizedBox(height: 20),
            PhoneCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  InfoCard(title: 'اسم المريض', value: draft.name.isEmpty ? 'غير محدد' : draft.name),
                  InfoCard(title: 'العمر', value: draft.age?.toString() ?? '-'),
                  InfoCard(title: 'الجنس', value: draft.gender),
                  InfoCard(title: 'الأعراض', value: draft.symptoms.isEmpty ? '-' : draft.symptoms.join('، ')),
                  InfoCard(title: 'Troponin', value: draft.troponin?.toString() ?? '-'),
                  InfoCard(title: 'CK-MB', value: draft.ckmb?.toString() ?? '-'),
                ],
              ),
            ),
            if (_error != null) ...[
              const SizedBox(height: 12),
              Text(_error!, style: const TextStyle(color: Colors.redAccent)),
            ],
            const SizedBox(height: 18),
            AppButton(
              text: 'بدء التحليل',
              onPressed: _submitAnalysis,
              loading: _loading,
            ),
          ],
        ),
      ),
    );
  }
}
