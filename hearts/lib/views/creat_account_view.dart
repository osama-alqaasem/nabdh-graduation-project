import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../api/api_service.dart';
import '../app_scope.dart';
import '../widget_build.dart';
import 'dashboard_view.dart';
import 'login_view.dart';

class CreateAccountView extends StatefulWidget {
  const CreateAccountView({super.key});

  @override
  State<CreateAccountView> createState() => _CreateAccountViewState();
}

class _CreateAccountViewState extends State<CreateAccountView> {
  final _formKey = GlobalKey<FormState>();
  final _nameController = TextEditingController();
  final _emailController = TextEditingController();
  final _passwordController = TextEditingController();
  final _confirmPasswordController = TextEditingController();

  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _nameController.dispose();
    _emailController.dispose();
    _passwordController.dispose();
    _confirmPasswordController.dispose();
    super.dispose();
  }

  Future<void> _register() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final data = await ApiService.instance.register(
        name: _nameController.text.trim(),
        email: _emailController.text.trim(),
        password: _passwordController.text,
      );

      final token = (data['token'] ?? data['access_token'] ?? data['jwt'] ?? '').toString();
      final user = (data['user'] as Map?)?.cast<String, dynamic>();

      if (token.isEmpty) {
        throw Exception('لم يتم استلام توكن من الخادم');
      }

      final session = AppScope.of(context);
      await session.saveAuth(token: token, user: user);
      if (user == null) {
        try {
          final me = await ApiService.instance.me();
          final meUser = (me['user'] as Map?)?.cast<String, dynamic>() ?? me;
          await session.saveAuth(token: token, user: meUser);
        } catch (_) {}
      }

      if (!mounted) return;
      Get.offAll(() => const DashboardView());
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0B0B0D),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(20),
          child: Column(
            children: [
              const SizedBox(height: 20),
              const ScreenHeader(
                title: 'إنشاء حساب',
                subtitle: 'أنشئ حسابًا جديدًا للوصول إلى لوحة التحليل والمتابعة',
                icon: Icons.person_add,
              ),
              const SizedBox(height: 20),
              PhoneCard(
                child: Form(
                  key: _formKey,
                  child: Column(
                    children: [
                      _Field(
                        controller: _nameController,
                        label: 'الاسم',
                        hint: 'اسم المستخدم',
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) return 'الاسم مطلوب';
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),
                      _Field(
                        controller: _emailController,
                        label: 'البريد الإلكتروني',
                        hint: 'example@email.com',
                        keyboardType: TextInputType.emailAddress,
                        validator: (value) {
                          if (value == null || value.trim().isEmpty) return 'البريد مطلوب';
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),
                      _Field(
                        controller: _passwordController,
                        label: 'كلمة المرور',
                        hint: '••••••••',
                        obscureText: true,
                        validator: (value) {
                          if (value == null || value.length < 6) return 'كلمة المرور لا تقل عن 6 أحرف';
                          return null;
                        },
                      ),
                      const SizedBox(height: 14),
                      _Field(
                        controller: _confirmPasswordController,
                        label: 'تأكيد كلمة المرور',
                        hint: '••••••••',
                        obscureText: true,
                        validator: (value) {
                          if (value != _passwordController.text) return 'كلمتا المرور غير متطابقتين';
                          return null;
                        },
                      ),
                      const SizedBox(height: 18),
                      if (_error != null)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Text(_error!, style: const TextStyle(color: Colors.redAccent)),
                        ),
                      AppButton(text: 'إنشاء الحساب', onPressed: _register, loading: _loading),
                      const SizedBox(height: 12),
                      TextButton(
                        onPressed: () => Get.off(() => const LoginView()),
                        child: const Text('لديك حساب بالفعل؟ تسجيل الدخول', style: TextStyle(color: Colors.white70)),
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Field extends StatelessWidget {
  final TextEditingController controller;
  final String label;
  final String hint;
  final TextInputType? keyboardType;
  final bool obscureText;
  final String? Function(String?) validator;

  const _Field({
    required this.controller,
    required this.label,
    required this.hint,
    required this.validator,
    this.keyboardType,
    this.obscureText = false,
  });

  @override
  Widget build(BuildContext context) {
    return TextFormField(
      controller: controller,
      validator: validator,
      keyboardType: keyboardType,
      obscureText: obscureText,
      style: const TextStyle(color: Colors.white),
      textAlign: TextAlign.right,
      decoration: InputDecoration(
        labelText: label,
        hintText: hint,
        labelStyle: const TextStyle(color: Colors.white70),
        hintStyle: const TextStyle(color: Colors.white24),
        filled: true,
        fillColor: const Color(0xFF252525),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(16), borderSide: BorderSide.none),
      ),
    );
  }
}
