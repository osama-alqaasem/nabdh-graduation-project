import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../api/api_service.dart';
import '../app_scope.dart';
import '../widget_build.dart';
import 'creat_account_view.dart';
import 'dashboard_view.dart';

class LoginView extends StatefulWidget {
  const LoginView({super.key});

  @override
  State<LoginView> createState() => _LoginViewState();
}

class _LoginViewState extends State<LoginView> {
  final _formKey = GlobalKey<FormState>();
  final _email = TextEditingController();
  final _password = TextEditingController();
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _email.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final data = await ApiService.instance.login(
        email: _email.text.trim(),
        password: _password.text,
      );

      final token = (data['token'] ?? data['access_token'] ?? data['jwt'] ?? '').toString();
      final user = (data['user'] as Map?)?.cast<String, dynamic>() ?? (data['data'] as Map?)?.cast<String, dynamic>();

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
              const SizedBox(height: 30),
              const ScreenHeader(
                title: 'تسجيل الدخول',
                subtitle: 'ادخل إلى لوحة التحليل والمتابعة',
                icon: Icons.login,
              ),
              const SizedBox(height: 28),
              PhoneCard(
                child: Form(
                  key: _formKey,
                  child: Column(
                    children: [
                      _Field(
                        controller: _email,
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
                        controller: _password,
                        label: 'كلمة المرور',
                        hint: '••••••••',
                        obscureText: true,
                        validator: (value) {
                          if (value == null || value.isEmpty) return 'كلمة المرور مطلوبة';
                          return null;
                        },
                      ),
                      const SizedBox(height: 18),
                      if (_error != null)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 12),
                          child: Text(_error!, style: const TextStyle(color: Colors.redAccent)),
                        ),
                      AppButton(text: 'دخول', onPressed: _login, loading: _loading),
                      const SizedBox(height: 12),
                      TextButton(
                        onPressed: () => Get.to(() => const CreateAccountView()),
                        child: const Text('ليس لديك حساب؟ إنشاء حساب', style: TextStyle(color: Colors.white70)),
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
