import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../api/api_service.dart';
import '../app_scope.dart';
import '../models/patient_profile.dart';
import '../widgets/app_drawer.dart'; // تأكد من المسار الصحيح
import 'login_view.dart';
import 'select_symptoms_view.dart';

// --- GetX Controller لإدارة الحالة ---
class AddPatientController extends GetxController {
  var isLoading = false.obs;
  var gender = 'ذكر'.obs;
  var hasStroke = false.obs;

  void setGender(String value) => gender.value = value;
  void setStroke(bool value) => hasStroke.value = value;
  void setLoading(bool value) => isLoading.value = value;
}

class AddPatientView extends StatefulWidget {
  const AddPatientView({super.key});

  @override
  State<AddPatientView> createState() => _AddPatientViewState();
}

class _AddPatientViewState extends State<AddPatientView> {
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _ageController = TextEditingController();

  // استدعاء الكنترولر
  final AddPatientController controller = Get.put(AddPatientController());
  bool _initialized = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_initialized) return;
    _initialized = true;

    // جلب البيانات الأولية من الـ Draft (إن وجدت)
    final draft = AppScope.of(context).draft;
    _nameController.text = draft.name;
    _ageController.text = draft.age?.toString() ?? '';
    controller.gender.value = draft.gender;
    controller.hasStroke.value = draft.previousStroke;
  }

  @override
  void dispose() {
    _nameController.dispose();
    _ageController.dispose();
    super.dispose();
  }

  // --- منطق الحفظ المرتبط بقاعدة البيانات (لم يتغير) ---
  Future<void> _savePatient() async {
    if (!_formKey.currentState!.validate()) return;

    final session = AppScope.of(context);
    if (!session.isAuthenticated || session.userId == null) {
      Get.snackbar('تنبيه', 'يلزم تسجيل الدخول أولاً', backgroundColor: Colors.white, colorText: Colors.black);
      return;
    }

    controller.setLoading(true);

    try {
      final draft = session.draft.copyWith(
        name: _nameController.text.trim(),
        age: int.tryParse(_ageController.text.trim()),
        gender: controller.gender.value,
        previousStroke: controller.hasStroke.value,
        symptoms: const [],
        troponin: null,
        ckmb: null,
        ecgImagePath: null,
        patientId: null,
      );

      final patientId = await ApiService.instance.savePatient(
        draft: draft,
        userId: session.userId!,
      );

      final profile = PatientProfile(
        id: patientId,
        userId: session.userId,
        fullName: draft.name,
        gender: draft.gender,
        age: draft.age,
        visitsCount: 0,
        createdAt: DateTime.now(),
        updatedAt: DateTime.now(),
      );

      await session.upsertPatient(profile);
      session.attachDraftToPatient(profile);

      if (!mounted) return;
      Get.to(() => const SelectSymptomsView());
    } catch (e) {
      Get.snackbar('خطأ', e.toString(), snackPosition: SnackPosition.BOTTOM, backgroundColor: Colors.red, colorText: Colors.white);
    } finally {
      controller.setLoading(false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0F0F0F),
      appBar: AppBar(
        backgroundColor: const Color(0xFF0F0F0F),
        elevation: 0,
        actions: [
          IconButton(
            onPressed: () async {
              await AppScope.of(context).logout();
              Get.offAll(() => LoginView());
            },
            icon: const Icon(Icons.logout_rounded, color: Colors.white70),
          ),
        ],
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      drawer: const AppDrawer(),
      body: SingleChildScrollView(
        padding: const EdgeInsets.symmetric(horizontal: 25),
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              const Text(
                "اضافة مريض",
                style: TextStyle(color: Colors.white, fontSize: 32, fontWeight: FontWeight.bold),
              ),
              const SizedBox(height: 30),
              const CircleAvatar(
                radius: 50,
                backgroundImage: AssetImage('assets/images/image3.jpg'),
              ),
              const SizedBox(height: 40),

              _buildLabel("الاسم"),
              _buildTextField(
                controller: _nameController,
                hint: "ادخل الاسم الكامل",
                validator: (v) => (v == null || v.trim().isEmpty) ? 'أدخل الاسم' : null,
              ),

              const SizedBox(height: 20),
              _buildLabel("الجنس"),
              Obx(() => _buildDropdownField(
                value: controller.gender.value,
                context: context,
                icon: controller.gender.value == 'ذكر' ? Icons.male : Icons.female,
                onChanged: (v) => controller.setGender(v),
              )),

              const SizedBox(height: 20),
              _buildLabel("العمر"),
              _buildTextField(
                controller: _ageController,
                hint: "ادخل العمر",
                keyboardType: TextInputType.number,
                validator: (v) => int.tryParse(v ?? '') == null ? 'أدخل عمرًا صحيحًا' : null,
              ),

              // const SizedBox(height: 20),
              // // تصميم التشيك بوكس المحدث
              // Container(
              //   decoration: BoxDecoration(
              //     color: const Color(0xFF1E1E1E),
              //     borderRadius: BorderRadius.circular(15),
              //   ),
              //   child: Obx(() => CheckboxListTile(
              //     title: const Text(
              //       "هل عانى المريض من جلطة سابقة؟",
              //       style: TextStyle(color: Colors.white, fontSize: 16),
              //     ),
              //     value: controller.hasStroke.value,
              //     onChanged: (bool? value) => controller.setStroke(value ?? false),
              //     activeColor: Colors.red,
              //     checkColor: Colors.white,
              //     controlAffinity: ListTileControlAffinity.leading,
              //     contentPadding: const EdgeInsets.symmetric(horizontal: 10),
              //   )),
              // ),

              const SizedBox(height: 60),

              // زر التقديم بتصميم الجراديانت والتحميل
              Obx(() => Container(
                width: double.infinity,
                height: 60,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFFE52D27), Color(0xFFB31217)],
                  ),
                  borderRadius: BorderRadius.circular(30),
                ),
                child: ElevatedButton(
                  onPressed: controller.isLoading.value ? null : _savePatient,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: Colors.transparent,
                    shadowColor: Colors.transparent,
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(30)),
                  ),
                  child: controller.isLoading.value
                      ? const CircularProgressIndicator(color: Colors.white)
                      : const Text(
                    "التالي",
                    style: TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                ),
              )),
              const SizedBox(height: 40),
            ],
          ),
        ),
      ),
    );
  }

  // --- المكونات المساعدة بنفس التصميم المطلوب ---

  Widget _buildLabel(String text) {
    return Align(
      alignment: Alignment.centerRight,
      child: Padding(
        padding: const EdgeInsets.only(bottom: 8, right: 5),
        child: Text(
          text,
          style: const TextStyle(color: Colors.white, fontSize: 18, fontWeight: FontWeight.w500),
        ),
      ),
    );
  }

  Widget _buildTextField({
    required TextEditingController controller,
    required String hint,
    TextInputType keyboardType = TextInputType.text,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      validator: validator,
      style: const TextStyle(color: Colors.white),
      decoration: InputDecoration(
        hintText: hint,
        hintStyle: const TextStyle(color: Colors.white38),
        filled: true,
        fillColor: const Color(0xFF1E1E1E),
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(15),
          borderSide: BorderSide.none,
        ),
        errorStyle: const TextStyle(color: Colors.redAccent),
      ),
    );
  }

  Widget _buildDropdownField({
    required BuildContext context,
    required String value,
    IconData? icon,
    required ValueChanged<String> onChanged,
  }) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 15),
      decoration: BoxDecoration(
        color: const Color(0xFF1E1E1E),
        borderRadius: BorderRadius.circular(15),
      ),
      child: ListTile(
        contentPadding: EdgeInsets.zero,
        leading: icon != null ? Icon(icon, color: Colors.white70) : null,
        title: Text(value, style: const TextStyle(color: Colors.white70)),
        trailing: const Icon(Icons.keyboard_arrow_down, color: Colors.white70),
        onTap: () {
          showModalBottomSheet(
            context: context,
            backgroundColor: const Color(0xFF1E1E1E),
            shape: const RoundedRectangleBorder(
              borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
            ),
            builder: (context) {
              return Container(
                padding: const EdgeInsets.symmetric(vertical: 20),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    const Text("اختر الجنس", style: TextStyle(color: Colors.white, fontSize: 18)),
                    const Divider(color: Colors.white12),
                    ListTile(
                      leading: const Icon(Icons.male, color: Colors.blue),
                      title: const Text("ذكر", style: TextStyle(color: Colors.white)),
                      onTap: () {
                        onChanged('ذكر');
                        Navigator.pop(context);
                      },
                    ),
                    ListTile(
                      leading: const Icon(Icons.female, color: Colors.pink),
                      title: const Text("أنثى", style: TextStyle(color: Colors.white)),
                      onTap: () {
                        onChanged('أنثى');
                        Navigator.pop(context);
                      },
                    ),
                  ],
                ),
              );
            },
          );
        },
      ),
    );
  }
}