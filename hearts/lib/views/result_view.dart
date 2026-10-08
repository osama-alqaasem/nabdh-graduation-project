import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../app_scope.dart';
import '../models/app_models.dart';
import 'dashboard_view.dart';
import 'Patient_Details_View.dart';

class ResultScreen extends StatelessWidget {
  final AnalysisRecord record;

  const ResultScreen({super.key, required this.record});

  // تحديد اللون بناءً على الحالة المطلوبة
  Color _statusColor(String label) {
    switch (label) {
      case 'طبيعي':
        return Colors.greenAccent;
      case 'اضطراب بنبض القلب':
        return Colors.orange;
      case 'مصاب بجلطة سابقة':
        return Colors.blueAccent;
      case 'مصاب بجلطة':
        return const Color(0xFFE53935);
      default:
        return Colors.white;
    }
  }

  // توضيح سبب النتيجة (التحليل العلمي المبسط)
  String _explanationMessage(String label) {
    switch (label) {
      case 'طبيعي':
        return 'تم تحليل مخطط القلب ولم يتم العثور على تغيرات في قطاع ST أو موجات غير طبيعية تدل على نقص تروية.';
      case 'اضطراب بنبض القلب':
        return 'تم رصد عدم انتظام في فترات ضربات القلب (R-R Intervals) مما يشير إلى وجود خلل في كهرباء القلب.';
      case 'مصاب بجلطة سابقة':
        return 'يظهر المخطط وجود موجات Q عميقة أو تغيرات دائمة تدل على تضرر سابق في أنسجة عضلة القلب.';
      case 'مصاب بجلطة':
        return 'تم رصد ارتفاع ملحوظ في قطاع ST، وهو مؤشر قوي على وجود انسداد حاد في أحد الشرايين التاجية.';
      default:
        return 'التحليل يعتمد على خوارزميات الذكاء الاصطناعي لفحص أنماط مخطط القلب الكهربائي.';
    }
  }

  @override
  Widget build(BuildContext context) {
    final session = AppScope.of(context);
    final result = record.result ?? session.latestResult;
    final label = result?.label ?? 'طبيعي';
    final color = _statusColor(label);
    final isSafe = label == 'طبيعي';

    double troponinValue = record.draft.troponin ?? 0.0;
    double ckmbValue = record.draft.ckmb ?? 0.0;

    bool isTroponinHigh = troponinValue >= 0.06;
    bool isCkmbHigh = ckmbValue >= 5.0;

    // تغليف الصفحة بـ PopScope لمنع الرجوع من زر النظام
    return PopScope(
      canPop: false, // منع الرجوع الافتراضي
      onPopInvoked: (didPop) { // لاحظ هنا بارامتر واحد فقط (didPop)
        if (didPop) return;

        // عند محاولة الرجوع، نذهب للرئيسية ونمسح كل الصفحات السابقة
        Get.offAll(() => const DashboardView());
      },
      child: Scaffold(
        backgroundColor: const Color(0xFF0B0B0D),
        appBar: AppBar(
          title: const Text(
            'النتيجة التحليلية',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
          centerTitle: true,
          backgroundColor: Colors.transparent,
          elevation: 0,
          leading: IconButton(
            icon: const Icon(Icons.arrow_back_ios, color: Color(0xFFE53935)),
            onPressed: () {
              // تعديل زر الرجوع في الـ AppBar ليذهب للرئيسية
              Get.offAll(() => const DashboardView());
            },
          ),
        ),
        body: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24),
          child: Column(
            children: [
              const SizedBox(height: 20),
              Center(
                child: Container(
                  width: 150,
                  height: 150,
                  decoration: BoxDecoration(
                    shape: BoxShape.circle,
                    gradient: RadialGradient(
                      colors: [color.withOpacity(0.4), const Color(0x1112141F)],
                    ),
                  ),
                  child: Center(
                    child: Container(
                      width: 100,
                      height: 100,
                      decoration: BoxDecoration(
                        color: color,
                        shape: BoxShape.circle,
                        boxShadow: [
                          BoxShadow(
                            color: color.withOpacity(0.4),
                            blurRadius: 30,
                            spreadRadius: 4,
                          ),
                        ],
                      ),
                      child: Icon(
                        isSafe ? Icons.verified_user_rounded : Icons.warning_amber_rounded,
                        size: 58,
                        color: Colors.white,
                      ),
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 24),
              Text(
                label,
                textAlign: TextAlign.center,
                style: TextStyle(
                  fontSize: 32,
                  fontWeight: FontWeight.w900,
                  color: color,
                ),
              ),
              if (result?.confidence != null)
                Padding(
                  padding: const EdgeInsets.only(top: 8.0),
                  child: Text(
                    'نسبة التأكد: ${result!.confidence.toString()}%',
                    style: TextStyle(
                        color: color.withOpacity(0.8),
                        fontSize: 18,
                        fontWeight: FontWeight.bold
                    ),
                  ),
                ),
              const SizedBox(height: 10),
              Text(
                _explanationMessage(label),
                textAlign: TextAlign.center,
                style: const TextStyle(
                  fontSize: 14,
                  color: Colors.white70,
                  height: 1.5,
                ),
              ),
              const SizedBox(height: 30),
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                  color: const Color(0xFF1E1E1E),
                  borderRadius: BorderRadius.circular(24),
                  border: Border.all(color: Colors.white10),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _resultItem(
                        'التروبونين: $troponinValue (${isTroponinHigh ? "مرتفع - مؤشر خطر" : "طبيعي"})',
                        isTroponinHigh,
                        Colors.redAccent
                    ),
                    _resultItem(
                        'CK-MB: $ckmbValue (${isCkmbHigh ? "مرتفع - مؤشر خطر" : "طبيعي"})',
                        isCkmbHigh,
                        Colors.redAccent
                    ),
                    _resultItem(
                        'الأعراض الصدرية',
                        record.draft.symptoms.contains('ألم في الصدر'),
                        color
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 30),
              _buildActionButton(
                text: "عرض تقرير المريض",
                isPrimary: true,
                onPressed: () {
                  final patient = session.getPatientById(record.draft.patientId);
                  if (patient != null) {
                    Get.to(() => PatientHistoryView());
                  } else {
                    Get.offAll(() => const DashboardView());
                  }
                },
              ),
              const SizedBox(height: 12),
              _buildActionButton(
                text: "تحليل حالة جديدة",
                isPrimary: false,
                onPressed: () {
                  session.clearDraft();
                  Get.offAll(() => const DashboardView());
                },
              ),
              const SizedBox(height: 40),
            ],
          ),
        ),
        bottomNavigationBar: _buildBottomNav(),
      ),
    );
  }

  Widget _buildActionButton({required String text, required VoidCallback onPressed, required bool isPrimary}) {
    return Container(
      width: double.infinity,
      height: 55,
      decoration: isPrimary
          ? BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFFE52D27), Color(0xFFB31217)]),
        borderRadius: BorderRadius.circular(30),
      )
          : BoxDecoration(
        borderRadius: BorderRadius.circular(30),
        border: Border.all(color: Colors.white24),
      ),
      child: ElevatedButton(
        onPressed: onPressed,
        style: ElevatedButton.styleFrom(
          backgroundColor: Colors.transparent,
          shadowColor: Colors.transparent,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
        ),
        child: Text(
          text,
          style: TextStyle(
            color: isPrimary ? Colors.white : Colors.white70,
            fontSize: 16,
            fontWeight: FontWeight.bold,
          ),
        ),
      ),
    );
  }

  Widget _resultItem(String title, bool active, Color activeColor) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: Row(
        children: [
          Icon(
            active ? Icons.warning_rounded : Icons.check_circle,
            color: active ? Colors.redAccent : Colors.greenAccent,
            size: 22,
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              title,
              style: const TextStyle(fontSize: 15, color: Colors.white),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBottomNav() {
    return BottomNavigationBar(
      backgroundColor: const Color(0xFF0B0B0D),
      selectedItemColor: const Color(0xFFE53935),
      unselectedItemColor: Colors.white38,
      currentIndex: 2,
      items: const [
        BottomNavigationBarItem(icon: Icon(Icons.assignment_outlined), label: 'الأعراض'),
        BottomNavigationBarItem(icon: Icon(Icons.science_outlined), label: 'الفحوصات'),
        BottomNavigationBarItem(icon: Icon(Icons.favorite), label: 'النتيجة'),
      ],
    );
  }
}