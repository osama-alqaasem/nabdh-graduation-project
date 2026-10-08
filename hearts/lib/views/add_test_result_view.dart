import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../app_scope.dart';
import '../controllers/AddTestResultsController.dart';
import 'uplod_ecg_view.dart';

class AddTestResultsView extends StatefulWidget {
  const AddTestResultsView({super.key});

  @override
  State<AddTestResultsView> createState() => _AddTestResultsViewState();
}

class _AddTestResultsViewState extends State<AddTestResultsView> {
  // تعريف الـ Controller باستخدام GetX
  final controller = Get.put(AddTestResultsController());
  bool _initialized = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    // جلب البيانات من AppScope (قاعدة البيانات) مرة واحدة عند الفتح
    if (!_initialized) {
      final draft = AppScope.of(context).draft;
      controller.troponinController.text = draft.troponin?.toString() ?? '';
      controller.ckmbController.text = draft.ckmb?.toString() ?? '';

      // إعداد المستمعين لتحديث الدوائر بعد تحميل البيانات
      controller.setupListeners();
      _initialized = true;
    }
  }

  void _next() {
    if (!controller.formKey.currentState!.validate()) return;

    // حفظ البيانات في AppScope (بدون تغيير منطق قاعدة البيانات)
    AppScope.of(context).updateDraft((current) => current.copyWith(
      troponin: double.tryParse(controller.troponinController.text.trim()),
      ckmb: double.tryParse(controller.ckmbController.text.trim()),
    ));

    Get.to(() => const UploadEcgView());
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F0F),
      resizeToAvoidBottomInset: true,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios, color: Colors.red),
          onPressed: () => Get.back(),
        ),
      ),
      body: SingleChildScrollView(
        physics: const BouncingScrollPhysics(),
        padding: const EdgeInsets.symmetric(horizontal: 20),
        child: Form(
          key: controller.formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                "اضافة نتائج الفحوصات",
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 32,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 30),

              // حاوية الفحوصات (تصميم الكود الأول)
              Container(
                decoration: BoxDecoration(
                  color: const Color(0xFF1E1E1E),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Column(
                  children: [
                    const Padding(
                      padding: EdgeInsets.only(left: 60, right: 15, top: 20, bottom: 10),
                      child: Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text("فحص", style: TextStyle(color: Colors.white60, fontSize: 14)),
                          Text("معدل", style: TextStyle(color: Colors.white60, fontSize: 14)),
                          Text("وحدة", style: TextStyle(color: Colors.white60, fontSize: 14)),
                        ],
                      ),
                    ),
                    _buildDivider(),
                    _buildTestInputRow('Troponin', 'ng/mL', controller.troponinController),
                    _buildDivider(),
                    _buildTestInputRow('CK-MB', 'ng/mL', controller.ckmbController),
                    const SizedBox(height: 10),
                  ],
                ),
              ),

              const SizedBox(height: 100),

              // زر التالـي بتصميمه المتدرج الأصلي
              Container(
                width: double.infinity,
                height: 60,
                margin: const EdgeInsets.only(bottom: 30),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFE52D27), Color(0xFFB31217)],
                  ),
                  borderRadius: BorderRadius.circular(30),
                ),
                child: ElevatedButton(
                  onPressed: _next,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.transparent,
                    shadowColor: Colors.transparent,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                  ),
                  child: const Text(
                    "التالي",
                    style: TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  Widget _buildTestInputRow(String testName, String unit, TextEditingController textController) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 15, vertical: 15),
      child: Row(
        children: [
          // الدائرة التفاعلية باستخدام Obx لتحديثها تلقائياً
          Obx(() {
            final isCompleted = controller.completedTests.contains(testName);
            return Container(
              width: 28,
              height: 28,
              decoration: BoxDecoration(
                shape: BoxShape.circle,
                color: isCompleted ? Colors.red : Colors.grey.shade800,
                boxShadow: [
                  if (isCompleted)
                    BoxShadow(
                      color: Colors.red.withOpacity(0.6),
                      blurRadius: 10,
                      spreadRadius: 1,
                    ),
                ],
              ),
            );
          }),
          const SizedBox(width: 15),
          Text(
            testName,
            style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w500),
          ),
          const Spacer(),
          // حقل الإدخال الصغير
          Container(
            width: 70,
            height: 45,
            decoration: BoxDecoration(
              color: const Color(0xFF0F0F0F),
              borderRadius: BorderRadius.circular(10),
              border: Border.all(color: Colors.white10),
            ),
            child: TextFormField(
              controller: textController,
              keyboardType: const TextInputType.numberWithOptions(decimal: true),
              style: const TextStyle(color: Colors.white, fontSize: 16),
              textAlign: TextAlign.center,
              validator: (v) {
                if (v == null || v.isEmpty) return '!';
                if (double.tryParse(v) == null) return '?';
                return null;
              },
              decoration: const InputDecoration(
                contentPadding: EdgeInsets.zero,
                border: InputBorder.none,
                hintText: '0',
                hintStyle: TextStyle(color: Colors.white24),
                errorStyle: TextStyle(height: 0, fontSize: 10),
              ),
            ),
          ),
          const SizedBox(width: 15),
          Text(unit, style: const TextStyle(color: Colors.white60, fontSize: 16)),
        ],
      ),
    );
  }

  Widget _buildDivider() => const Divider(color: Colors.white10, height: 1);

  Widget _buildBottomNav() {
    return BottomNavigationBar(
      backgroundColor: const Color(0xFF0F0F0F),
      selectedItemColor: Colors.red,
      unselectedItemColor: Colors.white30,
      currentIndex: 1,
      items: const [
        BottomNavigationBarItem(icon: Icon(Icons.assignment_outlined), label: 'الاعراض'),
        BottomNavigationBarItem(icon: Icon(Icons.science_outlined), label: 'فحوصات'),
        BottomNavigationBarItem(icon: Icon(Icons.favorite), label: 'النتائج'),
      ],
    );
  }
}