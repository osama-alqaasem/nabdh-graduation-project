import 'package:flutter/material.dart';

import 'patient_list_view.dart';

class ExistingPatientsView extends StatelessWidget {
  const ExistingPatientsView({super.key});

  @override
  Widget build(BuildContext context) {
    return const PatientListView(
      title: 'المرضى المسجلون',
      subtitle: 'اختر مريضًا موجودًا لبدء زيارة جديدة مع الحفاظ على الاسم والجنس والعمر الأساسيين.',
    );
  }
}
