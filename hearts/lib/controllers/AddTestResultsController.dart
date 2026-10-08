import 'package:flutter/material.dart';
import 'package:get/get.dart';

class AddTestResultsController extends GetxController {
  final formKey = GlobalKey<FormState>();

  // المتحكمات
  final troponinController = TextEditingController();
  final ckmbController = TextEditingController();

  // مجموعة لتتبع الفحوصات المكتملة (لتحديث الدوائر)
  var completedTests = <String>{}.obs;

  void setupListeners() {
    _addListener(troponinController, 'Troponin');
    _addListener(ckmbController, 'CK-MB');
  }

  void _addListener(TextEditingController controller, String key) {
    controller.addListener(() {
      if (controller.text.isNotEmpty) {
        completedTests.add(key);
      } else {
        completedTests.remove(key);
      }
    });
  }

  @override
  void onClose() {
    troponinController.dispose();
    ckmbController.dispose();
    super.onClose();
  }
}