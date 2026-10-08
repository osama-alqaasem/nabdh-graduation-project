import 'package:flutter/material.dart';
import 'package:get/get.dart';

import 'controllers/session_controller.dart';
import 'views/dashboard_view.dart';
import 'views/login_view.dart';
import 'views/welcome_view.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final session = Get.put(SessionController(), permanent: true);
  await session.load();

  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return GetMaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'نبض - تحليل القلب',
      locale: const Locale('ar'),
      builder: (context, child) {
        return Directionality(
          textDirection: TextDirection.rtl,
          child: child ?? const SizedBox.shrink(),
        );
      },
      home: GetBuilder<SessionController>(
        builder: (session) {
          if (!session.isReady) {
            return const _SplashScreen();
          }

          if (!session.onboardingSeen) {
            return const WelcomeView();
          }

          if (!session.isAuthenticated) {
            return const LoginView();
          }

          return const DashboardView();
        },
      ),
    );
  }
}

class _SplashScreen extends StatelessWidget {
  const _SplashScreen();

  @override
  Widget build(BuildContext context) {
    return const Scaffold(
      backgroundColor: Color(0xFF0B0B0D),
      body: Center(
        child: CircularProgressIndicator(color: Colors.red),
      ),
    );
  }
}
