import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../app_scope.dart';
import '../widget_build.dart';
import 'add_test_result_view.dart';

class SelectSymptomsView extends StatefulWidget {
  const SelectSymptomsView({super.key});

  @override
  State<SelectSymptomsView> createState() => _SelectSymptomsViewState();
}

class _SelectSymptomsViewState extends State<SelectSymptomsView> {
  final List<Map<String, String>> _allSymptoms = [
    {"label": "ألم في الصدر", "value": "chest_pain"},
    {"label": "ضيق في التنفس", "value": "shortness"},
    {"label": "غثيان", "value": "nausea"},
    {"label": "دوخة", "value": "dizziness"},
    {"label": "تعرق", "value": "sweating"},
    {"label": "إرهاق", "value": "fatigue"},
  ];

  final Set<String> _selectedSymptoms = {};
  bool _initialized = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_initialized) return;
    _initialized = true;
    final draft = AppScope.of(context).draft;
    _selectedSymptoms.addAll(draft.symptoms);
  }

  void _next() {
    if (_selectedSymptoms.isEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('اختر عرضًا واحدًا على الأقل')),
      );
      return;
    }

    final session = AppScope.of(context);
    session.updateDraft((current) => current.copyWith(symptoms: _selectedSymptoms.toList()));
    Get.to(() => const AddTestResultsView());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0B0B0D),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        title: const Text('الأعراض', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(18),
        child: Column(
          children: [
            const ScreenHeader(
              title: 'اختر أعراض المريض',
              subtitle: 'يمكنك تحديد أكثر من عرض، ثم الانتقال للفحوصات المخبرية.',
              icon: Icons.healing,
            ),
            const SizedBox(height: 20),
            PhoneCard(
              child: Column(
                children: _allSymptoms.map((symptom) {
                  final selected = _selectedSymptoms.contains(symptom['value']);
                  return CheckboxListTile(
                    value: selected,
                    onChanged: (value) {
                      setState(() {
                        if (value == true) {
                          _selectedSymptoms.add(symptom['value']!);
                        } else {
                          _selectedSymptoms.remove(symptom['value']);
                        }
                      });
                    },
                    title: Text(symptom['label']!, style: const TextStyle(color: Colors.white)),
                    checkColor: Colors.white,
                    activeColor: const Color(0xFFE53935),
                    controlAffinity: ListTileControlAffinity.leading,
                  );
                }).toList(),
              ),
            ),
            const SizedBox(height: 18),
            AppButton(text: 'التالي', onPressed: _next),
          ],
        ),
      ),
    );
  }
}
