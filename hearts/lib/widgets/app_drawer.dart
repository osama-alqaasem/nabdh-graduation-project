import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:font_awesome_flutter/font_awesome_flutter.dart';
import 'package:get/get.dart';
import 'package:url_launcher/url_launcher.dart';

import '../app_scope.dart';
import '../controllers/session_controller.dart';
import '../views/Patient_Details_View.dart';
import '../views/login_view.dart';

class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  static const String _privacyPolicyText = '''
نحترم خصوصية المستخدمين ونسعى إلى حماية البيانات الشخصية والمعلومات الطبية داخل التطبيق.
تُستخدم البيانات فقط لغرض عرض النتائج الطبية، حفظ السجل، وتحسين تجربة الاستخدام.
لا يتم مشاركة البيانات مع أي طرف خارجي إلا وفق ما يتطلبه الاستخدام داخل التطبيق أو بإذن المستخدم.
يجب على المستخدم التأكد من صحة البيانات المدخلة قبل الاعتماد على النتائج.
''';

  static const String _adsPhone = '+967774290329';
  static const String _adsWhatsApp = '+967774290329';

  static const String _teamEmail = 'osamaalqaasem@gmail.com';
  static const String _teamPhone = '+967774290329';
  static const String _instagramUrl = 'https://instagram.com/_o2ai';

  Future<void> _copyToClipboard(BuildContext context, String text) async {
    await Clipboard.setData(ClipboardData(text: text));
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        const SnackBar(content: Text('تم نسخ الرقم')),
      );
    }
  }

  Future<void> _launchExternal(String url) async {
    final uri = Uri.parse(url);
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> _launchPhone(String phone) async {
    final uri = Uri.parse('tel:$phone');
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> _launchEmail(String email) async {
    final uri = Uri.parse('mailto:$email');
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  Future<void> _launchWhatsApp(String phone) async {
    final digitsOnly = phone.replaceAll(RegExp(r'\D'), '');
    final uri = Uri.parse('https://wa.me/$digitsOnly');
    await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  void _showPrivacyPolicyDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor:  Colors.black,
          title: const Text(
            'سياسة الخصوصية',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
          content: SingleChildScrollView(
            child: Text(
              _privacyPolicyText,
              style: const TextStyle(color: Colors.white70, height: 1.6),
              textAlign: TextAlign.right,
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Get.back(),
              child: const Text('رجوع' , style: TextStyle(color: Colors.white),),
            ),
          ],
        );
      },
    );
  }

  void _showAdsContactDialog(BuildContext context) {
    showDialog(
      context: context,
      builder: (dialogContext) {
        return AlertDialog(
          backgroundColor:  Colors.black,
          title: const Text(
            'التواصل مع الإعلانات',
            style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
          ),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              _contactRow(
                context: context,
                icon: Icons.phone,
                title: 'رقم الاتصال',
                value: _adsPhone,
                onTap: () => _launchPhone(_adsPhone),
                onCopy: () => _copyToClipboard(context, _adsPhone),
              ),
              const SizedBox(height: 12),
              _contactRow(
                context: context,
                icon: FontAwesomeIcons.whatsapp,
                title: 'واتساب',
                value: _adsWhatsApp,
                onTap: () => _launchWhatsApp(_adsWhatsApp),
                onCopy: () => _copyToClipboard(context, _adsWhatsApp),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Get.back(),
              child: const Text('رجوع' , style: TextStyle(color: Colors.white),),
            ),
          ],
        );
      },
    );
  }

  Widget _contactRow({
    required BuildContext context,
    required IconData icon,
    required String title,
    required String value,
    required VoidCallback onTap,
    required VoidCallback onCopy,
  }) {
    return Container(
      padding: const EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: const Color(0xFF101012),
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: Colors.white10),
      ),
      child: Row(
        children: [
          FaIcon(icon, color: const Color(0xFFE5212E)),
          const SizedBox(width: 10),
          Expanded(
            child: GestureDetector(
              onTap: onTap,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    title,
                    style: const TextStyle(
                      color: Colors.white70,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  const SizedBox(height: 4),
                  Text(
                    value,
                    style: const TextStyle(color: Colors.white),
                  ),
                ],
              ),
            ),
          ),
          IconButton(
            onPressed: onCopy,
            icon: const Icon(Icons.copy, color: Colors.white70),
          ),
        ],
      ),
    );
  }

  Widget _teamIconItem({
    required BuildContext context,
    required IconData icon,
    required String label,
    required VoidCallback onTap,
  }) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          // Container(
          //   width: 44,
          //   height: 44,
          //   decoration: BoxDecoration(
          //     color: const Color(0xFF1E1E1E),
          //     shape: BoxShape.circle,
          //     border: Border.all(color: Colors.white10),
          //   ),
          //   child:
           FaIcon(icon, color: const Color(0xFFE5212E), size: 27),

          const SizedBox(height: 6),
          Text(
            label,
            style: const TextStyle(color: Colors.white54, fontSize: 12),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final SessionController session = AppScope.of(context);

    return Drawer(
      backgroundColor: const Color(0xFF101012),
      child: Column(
        children: [
          UserAccountsDrawerHeader(
            decoration: const BoxDecoration(color: Color(0xFF1E1E1E)),
            currentAccountPicture: CircleAvatar(
              backgroundColor: const Color(0xFFE5212E),
              child: Text(
                session.user?['name'] != null &&
                    session.user!['name'].toString().isNotEmpty
                    ? session.user!['name'].toString()[0]
                    : "-",
                style: const TextStyle(fontSize: 24, color: Colors.white),
              ),
            ),
            accountName: Text(
              session.user != null && session.user!['name'] != null
                  ? "مرحبا ${session.user!['name']}"
                  : "جارٍ التحميل...",
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            accountEmail: Text(
              session.user?['email']?.toString() ?? "",
              style: const TextStyle(fontSize: 14),
            ),
          ),
          ListTile(
            leading: const Icon(Icons.history, color: Color(0xFFE5212E)),
            title: const Text("سجل المرضى", style: TextStyle(color: Colors.white)),
            onTap: () {
              Navigator.pop(context);
              Get.to(() => const PatientHistoryView());
            },
          ),
          ListTile(
            leading: const Icon(Icons.privacy_tip, color: Color(0xFFE5212E)),
            title: const Text("سياسة الخصوصية", style: TextStyle(color: Colors.white)),
            onTap: () {
              _showPrivacyPolicyDialog(context);
            },
          ),
          ListTile(
            leading: const Icon(Icons.campaign, color: Color(0xFFE5212E)),
            title: const Text("التواصل مع الإعلانات", style: TextStyle(color: Colors.white)),
            onTap: () {
              _showAdsContactDialog(context);
            },
          ),
          const Spacer(),
          const Divider(color: Colors.white10),
          const Padding(
            padding: EdgeInsets.only(top: 6, bottom: 6),
            child: Text(
              'تطوير فريق نبض',
              style: TextStyle(
                color: Colors.white70,
                fontWeight: FontWeight.bold,
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceEvenly,
              children: [
                _teamIconItem(
                  context: context,
                  icon: Icons.email,
                  label: 'Email',
                  onTap: () => _launchEmail(_teamEmail),
                ),
                _teamIconItem(
                  context: context,
                  icon: Icons.phone,
                  label: 'Call',
                  onTap: () => _launchPhone(_teamPhone),
                ),
                _teamIconItem(
                  context: context,
                  icon: FontAwesomeIcons.instagram,
                  label: 'Instagram',
                  onTap: () => _launchExternal(_instagramUrl),
                ),
              ],
            ),
          ),
          const SizedBox(height: 10),
          const Text(
            ' الاصدار :v 1.0.0 ',
            style: TextStyle(color: Colors.white38, fontSize: 12),
          ),
          const SizedBox(height: 10),
          const Divider(color: Colors.white10),
          ListTile(
            leading: const Icon(Icons.logout, color: Colors.red),
            title: const Text("تسجيل الخروج", style: TextStyle(color: Colors.grey)),
            onTap: () async {
              Navigator.pop(context);
              await session.logout();
              Get.offAll(() => const LoginView());
            },
          ),
          const SizedBox(height: 20),
        ],
      ),
    );
  }
}