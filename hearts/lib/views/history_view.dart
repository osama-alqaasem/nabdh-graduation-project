import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../controllers/session_controller.dart';
import 'Patient_Details_View.dart';

class HistoryView extends StatelessWidget {
  const HistoryView({super.key});

  // دالة تحديد الألوان بناءً على الحالة (كما طلبت تماماً)
  Color _getStatusColor(String? label) {
    switch (label) {
      case 'طبيعي':
        return Colors.green;
      case 'اضطراب بنبض القلب':
        return Colors.orange;
      case 'مصاب بجلطة سابقة':
        return Colors.blue;
      case 'مصاب بجلطة':
        return Colors.red;
      default:
        return Colors.white38;
    }
  }


  @override
  Widget build(BuildContext context) {
    return GetBuilder<SessionController>(
      builder: (controller) {
        // جلب سجل الفحوصات الفعلي من الكنترولر
        final history = controller.userHistory;

        return Scaffold(
          backgroundColor: const Color(0xFF101012),
          appBar: AppBar(
            title: const Text(
              "سجل المرضى",
              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
            ),
            backgroundColor: Colors.transparent,
            elevation: 0,
            centerTitle: true,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back_ios, color: Colors.red),
              onPressed: () => Get.back(),
            ),
          ),
          body: history.isEmpty
              ? const Center(
            child: Text(
              "لا يوجد سجلات فحص حالياً",
              style: TextStyle(color: Colors.white38),
            ),
          )
              : ListView.builder(
            padding: const EdgeInsets.all(16),
            itemCount: history.length,
            itemBuilder: (context, index) {
              final record = history[index];

              // جلب اسم المرض/الحالة من نتيجة الفحص
              String label = record.result?.label ?? 'غير محدد';
              Color statusColor = _getStatusColor(label);

              // جلب اسم المريض المسجل في المسودة
              final patient = controller.getPatientById(record.draft.patientId);

              String patientName = (patient != null && patient.fullName.isNotEmpty)
                  ? patient.fullName
                  : (record.draft.name.isNotEmpty ? record.draft.name : "غير معروف");

              // تنسيق التاريخ
              String dateString = record.createdAt.toString().substring(0, 10);

              return Container(
                margin: const EdgeInsets.only(bottom: 15),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E1E1E),
                  borderRadius: BorderRadius.circular(15),
                  border: Border.all(color: Colors.white10),
                ),
                child: ListTile(
                  contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
                  title: Text(
                    patientName,
                    style: const TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.bold),
                  ),
                  subtitle: Text(
                    "التاريخ: $dateString",
                    style: const TextStyle(color: Colors.white38, fontSize: 12),
                  ),
                  // عرض اسم المرض/الحالة في جهة اليسار بتصميم ملون
                  trailing: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: statusColor.withOpacity(0.1),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      label,
                      style: TextStyle(
                        color: statusColor,
                        fontWeight: FontWeight.bold,
                        fontSize: 12,
                      ),
                    ),
                  ),
                  onTap: () {
                    // الانتقال لصفحة التفاصيل مع تمرير السجل المختار
                    // Get.to(() => DetailedPatientProfile(record: record));
                  },
                ),
              );
            },
          ),
        );
      },
    );
  }
}