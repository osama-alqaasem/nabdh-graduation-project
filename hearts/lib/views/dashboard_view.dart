import 'dart:async';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:url_launcher/url_launcher.dart';

import '../api/api_service.dart';
import '../controllers/session_controller.dart';
import '../models/app_models.dart';
import '../widget_build.dart';
import '../widgets/app_drawer.dart';
import 'add_patient_view.dart';
import 'existing_patients_view.dart';
import 'history_view.dart';

class DashboardView extends StatefulWidget {
  const DashboardView({super.key});

  @override
  State<DashboardView> createState() => _DashboardViewState();
}

class _DashboardViewState extends State<DashboardView> {
  late final PageController _pageController;
  Timer? _timer;
  int _pageIndex = 0;

  Future<List<DashboardBanner>>? _bannersFuture;

  @override
  void initState() {
    super.initState();
    _pageController = PageController();
    _bannersFuture = ApiService.instance.fetchBanners();

    final session = Get.find<SessionController>();
    _timer = Timer.periodic(const Duration(seconds: 4), (_) => _advancePage());
    session.load().then((_) {
      session.loadAllData();
    });
  }

  @override
  void dispose() {
    _timer?.cancel();
    _pageController.dispose();
    super.dispose();
  }

  void _advancePage() async {
    if (!_pageController.hasClients) return;
    final banners = await (_bannersFuture ?? Future.value([]));
    if (banners.isEmpty) return;

    _pageIndex = (_pageIndex + 1) % banners.length;
    _pageController.animateToPage(
      _pageIndex,
      duration: const Duration(milliseconds: 450),
      curve: Curves.easeInOut,
    );
  }

  Color _colorForLabel(String label) {
    switch (label) {
      case 'طبيعي': return Colors.green;
      case 'اضطراب بنبض القلب': return Colors.orange;
      case 'مصاب بجلطة سابقة': return Colors.blue;
      case 'مصاب بجلطة': return Colors.red;
      default: return Colors.white;
    }
  }

  String _fixImageUrl(String url) {
    if (url.startsWith('http')) return url;
    return "http://192.168.137.1:8000$url";
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFF0B0B0D),
      drawer: const AppDrawer() ,
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        centerTitle: true,
        title: const Text('لوحة التحكم',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        iconTheme: const IconThemeData(color: Colors.white),
        actions: [
          // IconButton(
          //   icon: const Icon(Icons.history, color: Colors.redAccent),
          //   onPressed: () => Get.to(() => const HistoryView()),
          // ),
        ],
      ),
      body: GetBuilder<SessionController>(
        builder: (session) {
          final analyses = session.userHistory;
          int count(String label) {
            return analyses.where((r) => (r.result?.label ?? '') == label).length;
          }

          final resultCards = [
            {'label': 'طبيعي', 'count': count('طبيعي')},
            {'label': 'اضطراب بنبض القلب', 'count': count('اضطراب بنبض القلب')},
            {'label': 'مصاب بجلطة', 'count': count('مصاب بجلطة')},
            {'label': 'مصاب بجلطة سابقة', 'count': count('مصاب بجلطة سابقة')},
          ];

          return ListView(
            padding: const EdgeInsets.all(18),
            children: [
              /// ================== 🔥 BANNERS (Images Only) ==================
              FutureBuilder<List<DashboardBanner>>(
                future: _bannersFuture,
                builder: (context, snapshot) {
                  final banners = snapshot.data ?? [];
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const SizedBox(height: 200, child: Center(child: CircularProgressIndicator()));
                  }
                  if (banners.isEmpty) return const SizedBox.shrink();

                  return SizedBox(
                    height: 200,
                    child: PageView.builder(
                      controller: _pageController,
                      itemCount: banners.length,
                      itemBuilder: (context, index) {
                        final banner = banners[index];
                        return Container(
                          margin: const EdgeInsets.symmetric(horizontal: 4),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(20),
                            child: Stack(
                              fit: StackFit.expand,
                              children: [
                                // التعديل هنا: استخدام دالة جلب رابط البانر
                                Image.network(
                                  banner.fullImageUrl, // سيقوم تلقائياً ببناء الرابط الصحيح
                                  fit: BoxFit.cover,
                                  errorBuilder: (context, error, stackTrace) => const Icon(Icons.broken_image),
                                ),
                                Container(
                                  decoration: BoxDecoration(
                                    gradient: LinearGradient(
                                      colors: [Colors.black.withOpacity(0.7), Colors.transparent],
                                      begin: Alignment.bottomCenter,
                                      end: Alignment.topCenter,
                                    ),
                                  ),
                                ),
                                Padding(
                                  padding: const EdgeInsets.all(14),
                                  child: Column(
                                    mainAxisAlignment: MainAxisAlignment.end,
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(banner.title,
                                          style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
                                      if ((banner.description ?? '').isNotEmpty)
                                        Text(banner.description!,
                                            style: const TextStyle(color: Colors.white70, fontSize: 12)),
                                    ],
                                  ),
                                )
                              ],
                            ),
                          ),
                        );
                      },
                    ),
                  );
                },
              ),

              const SizedBox(height: 22),

              /// ================== ACTIONS (Equal Size) ==================
              Row(
                children: [
                  Expanded(
                    child: _ActionCard(
                      icon: Icons.person_add_alt_1,
                      title: 'إضافة مريض جديد',
                      subtitle: 'إنشاء ملف جديد وبدء التحليل',
                      onTap: () => Get.to(() => const AddPatientView()),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: _ActionCard(
                      icon: Icons.folder_open,
                      title: 'اضافة بيانات لمريض \n موجود',
                      subtitle: 'اختيار مريض وإضافة زيارة',
                      onTap: () => Get.to(() => const ExistingPatientsView()),
                    ),
                  ),
                ],
              ),

              const SizedBox(height: 18),

              /// ================== USER ==================
              PhoneCard(
                child: Row(
                  children: [
                    const Icon(Icons.account_circle, color: Colors.redAccent),
                    const SizedBox(width: 10),
                    Text(session.user?['email'] ?? '',
                        style: const TextStyle(color: Colors.white)),
                  ],
                ),
              ),

              const SizedBox(height: 18),

              /// ================== STATS ==================
              PhoneCard(
                child: Row(
                  children: [
                    Expanded(
                      child: _SmallStat(
                        title: 'المرضى',
                        value: '${session.userPatients.length}',
                        icon: Icons.people_alt,
                      ),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: _SmallStat(
                        title: 'التحاليل',
                        value: '${session.userHistory.length}',
                        icon: Icons.analytics_outlined,
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 18),

              /// ================== RESULTS ==================
              PhoneCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('نتائج التحليلات', style: TextStyle(color: Colors.white70)),
                    const SizedBox(height: 12),
                    ...resultCards.map((card) {
                      final label = card['label'] as String;
                      final count = card['count'] as int;
                      final color = _colorForLabel(label);
                      return Padding(
                        padding: const EdgeInsets.only(bottom: 10),
                        child: Row(
                          children: [
                            Container(
                              width: 10,
                              height: 10,
                              decoration: BoxDecoration(color: color, shape: BoxShape.circle),
                            ),
                            const SizedBox(width: 10),
                            Expanded(child: Text(label, style: const TextStyle(color: Colors.white))),
                            Text('$count', style: TextStyle(color: color)),
                          ],
                        ),
                      );
                    }),
                  ],
                ),
              ),
            ],
          );
        },
      ),
    );
  }
}

/// ================== ACTION CARD (Modified for Equal Height) ==================
class _ActionCard extends StatelessWidget {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  const _ActionCard({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return Material(
      color: const Color(0xFF1B1B1D),
      borderRadius: BorderRadius.circular(24),
      child: InkWell(
        borderRadius: BorderRadius.circular(24),
        onTap: onTap,
        child: Container(
          height: 150, // تم تحديد الارتفاع لضمان تساوي المربعين
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(24),
            border: Border.all(color: Colors.white10),
          ),
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Icon(icon, color: Colors.redAccent, size: 28),
              const SizedBox(height: 12),
              Text(title,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 14)),
              const SizedBox(height: 4),
              Text(subtitle,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: const TextStyle(color: Colors.white54, fontSize: 11)),
            ],
          ),
        ),
      ),
    );
  }
}

class _SmallStat extends StatelessWidget {
  final String title;
  final String value;
  final IconData icon;

  const _SmallStat({required this.title, required this.value, required this.icon});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(14),
      decoration: BoxDecoration(
        color: const Color(0xFF252525),
        borderRadius: BorderRadius.circular(18),
        border: Border.all(color: Colors.white10),
      ),
      child: Row(
        children: [
          Icon(icon, color: Colors.redAccent),
          const SizedBox(width: 10),
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(value, style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
              Text(title, style: const TextStyle(color: Colors.white54, fontSize: 12)),
            ],
          ),
        ],
      ),
    );
  }
}