import 'package:flutter/material.dart';
import 'package:get/get.dart';

import 'controllers/session_controller.dart';

class AppScope {
  static SessionController of(BuildContext context) => Get.find<SessionController>();
}
