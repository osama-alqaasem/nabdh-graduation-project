import 'dart:io';

import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:image_picker/image_picker.dart';

import '../app_scope.dart';
import '../widget_build.dart';
import 'run_analysis_view.dart';

class UploadEcgView extends StatefulWidget {
  const UploadEcgView({super.key});

  @override
  State<UploadEcgView> createState() => _UploadEcgViewState();
}

class _UploadEcgViewState extends State<UploadEcgView> {
  final ImagePicker _picker = ImagePicker();
  String? _imagePath;
  String? _error;
  bool _initialized = false;

  @override
  void didChangeDependencies() {
    super.didChangeDependencies();
    if (_initialized) return;
    _initialized = true;
    _imagePath = AppScope.of(context).draft.ecgImagePath;
  }

  Future<void> _pickImage() async {
    final XFile? image = await _picker.pickImage(
      source: ImageSource.gallery,
      imageQuality: 90,
    );

    if (image != null) {
      setState(() {
        _imagePath = image.path;
        _error = null;
      });
    }
  }

  void _onNextPressed() {
    if (_imagePath == null || _imagePath!.isEmpty) {
      setState(() => _error = 'يرجى اختيار صورة ECG');
      return;
    }

    final session = AppScope.of(context);
    session.updateDraft((current) => current.copyWith(ecgImagePath: _imagePath));
    Get.to(() => const RunAnalysisView());
  }

  @override
  Widget build(BuildContext context) {
    final preview = _imagePath != null && _imagePath!.isNotEmpty && File(_imagePath!).existsSync()
        ? File(_imagePath!)
        : null;

    return Scaffold(
      backgroundColor: const Color(0xFF0B0B0D),
      appBar: AppBar(
        backgroundColor: Colors.transparent,
        elevation: 0,
        title: const Text('ECG', style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold)),
        iconTheme: const IconThemeData(color: Colors.white),
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(18),
        child: Column(
          children: [
            const ScreenHeader(
              title: 'ارفع صورة تخطيط القلب',
              subtitle: 'اختر صورة ECG من المعرض ثم أكمل عملية التحليل.',
              icon: Icons.monitor_heart,
            ),
            const SizedBox(height: 20),
            PhoneCard(
              child: Column(
                children: [
                  if (preview != null)
                    ClipRRect(
                      borderRadius: BorderRadius.circular(18),
                      child: Image.file(
                        preview,
                        width: double.infinity,
                        height: 220,
                        fit: BoxFit.cover,
                      ),
                    )
                  else
                    Container(
                      height: 220,
                      width: double.infinity,
                      decoration: BoxDecoration(
                        color: const Color(0xFF252525),
                        borderRadius: BorderRadius.circular(18),
                        border: Border.all(color: Colors.white10),
                      ),
                      child: const Center(
                        child: Icon(Icons.image_outlined, color: Colors.white38, size: 54),
                      ),
                    ),
                  if (_error != null) ...[
                    const SizedBox(height: 12),
                    Text(_error!, style: const TextStyle(color: Colors.redAccent)),
                  ],
                  const SizedBox(height: 14),
                  AppButton(text: 'اختيار صورة ECG', onPressed: _pickImage),
                  const SizedBox(height: 12),
                  AppButton(text: 'التالي', onPressed: _onNextPressed),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}
