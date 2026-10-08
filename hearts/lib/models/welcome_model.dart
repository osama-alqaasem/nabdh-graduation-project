import 'package:flutter/material.dart';

class OnboardingSlide {
  final String title;
  final String description;
  final IconData icon;
  final List<String> bullets;

  const OnboardingSlide({
    required this.title,
    required this.description,
    required this.icon,
    this.bullets = const [],
  });
}
