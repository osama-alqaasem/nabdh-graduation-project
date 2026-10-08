import 'package:flutter/material.dart';

class AddAnalysisPage extends StatelessWidget {
  final int patientId;

  const AddAnalysisPage(this.patientId, {super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text("إضافة تحليل")),
      body: Center(
        child: Text("هنا تضيف التحليل للمريض رقم $patientId"),
      ),
    );
  }
}