import 'dart:io';
import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:get/get.dart';
import 'package:pdf/pdf.dart';
import 'package:pdf/widgets.dart' as pw;
import 'package:printing/printing.dart';

import '../api/api_config.dart';
import '../controllers/session_controller.dart';
import '../models/app_models.dart';
import '../widget_build.dart';

class _PatientHistoryGroup {
  final String key;
  final int? patientId;
  final String patientName;
  final List<AnalysisRecord> records;

  _PatientHistoryGroup({
    required this.key,
    required this.patientId,
    required this.patientName,
    required this.records,
  });

  AnalysisRecord get latestRecord => records.first;
}

List<_PatientHistoryGroup> _buildPatientGroups(
    List<AnalysisRecord> history,
    SessionController controller,
    ) {
  final Map<String, List<AnalysisRecord>> grouped = {};

  for (final record in history) {
    final patientId = record.draft.patientId;
    final patient = controller.getPatientById(patientId);

    final fallbackName = (patient != null && patient.fullName.trim().isNotEmpty)
        ? patient.fullName.trim()
        : record.draft.name.trim();

    final key = patientId != null
        ? 'id_$patientId'
        : 'fallback_${fallbackName.toLowerCase()}|${record.draft.age ?? 0}|${record.draft.gender.toLowerCase()}';

    grouped.putIfAbsent(key, () => <AnalysisRecord>[]).add(record);
  }

  final groups = grouped.entries.map((entry) {
    final records = List<AnalysisRecord>.from(entry.value)
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt));

    final latest = records.first;
    final patientId = latest.draft.patientId;
    final patient = controller.getPatientById(patientId);

    final patientName = (patient != null && patient.fullName.trim().isNotEmpty)
        ? patient.fullName.trim()
        : (latest.draft.name.trim().isNotEmpty ? latest.draft.name.trim() : 'غير معروف');

    return _PatientHistoryGroup(
      key: entry.key,
      patientId: patientId,
      patientName: patientName,
      records: records,
    );
  }).toList();

  groups.sort((a, b) => b.latestRecord.createdAt.compareTo(a.latestRecord.createdAt));
  return groups;
}

class PatientHistoryView extends StatelessWidget {
  const PatientHistoryView({super.key});

  Color _statusColor(String? label) {
    switch (label) {
      case 'طبيعي':
        return Colors.greenAccent;
      case 'اضطراب بنبض القلب':
        return Colors.orange;
      case 'مصاب بجلطة سابقة':
        return Colors.blueAccent;
      case 'مصاب بجلطة':
        return const Color(0xFFE53935);
      default:
        return Colors.white38;
    }
  }

  @override
  Widget build(BuildContext context) {
    return GetBuilder<SessionController>(
      builder: (controller) {
        final history = controller.userHistory;
        final groups = _buildPatientGroups(history, controller);

        return Scaffold(
          backgroundColor: const Color(0xFF0B0B0D),
          appBar: AppBar(
            backgroundColor: Colors.transparent,
            elevation: 0,
            title: const Text(
              "سجل المرضى",
              style: TextStyle(color: Colors.white, fontWeight: FontWeight.bold),
            ),
            centerTitle: true,
            leading: IconButton(
              icon: const Icon(Icons.arrow_back_ios, color: Color(0xFFE53935)),
              onPressed: () => Get.back(),
            ),
          ),
          body: groups.isEmpty
              ? const Center(
            child: Text(
              "لا يوجد مرضى مضافين في السجل حالياً",
              style: TextStyle(color: Colors.white38, fontSize: 16),
            ),
          )
              : ListView.builder(
            padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10),
            physics: const BouncingScrollPhysics(),
            itemCount: groups.length,
            itemBuilder: (context, index) {
              final group = groups[index];
              final item = group.latestRecord;
              final label = item.result?.label ?? 'غير محدد';
              final color = _statusColor(label);
              final bool isSafe = label == 'طبيعي';

              return GestureDetector(
                onTap: () => Get.to(() => DetailedPatientProfile(group: group)),
                child: Container(
                  margin: const EdgeInsets.only(bottom: 15),
                  padding: const EdgeInsets.all(18),
                  decoration: BoxDecoration(
                    color: const Color(0xFF1E1E1E),
                    borderRadius: BorderRadius.circular(24),
                    border: Border.all(color: Colors.white10),
                  ),
                  child: Row(
                    children: [
                      Container(
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: color.withOpacity(0.1),
                          shape: BoxShape.circle,
                          boxShadow: [
                            BoxShadow(
                              color: color.withOpacity(0.05),
                              blurRadius: 10,
                            )
                          ],
                        ),
                        child: Icon(
                          isSafe ? Icons.verified_user_rounded : Icons.warning_amber_rounded,
                          color: color,
                          size: 28,
                        ),
                      ),
                      const SizedBox(width: 15),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              group.patientName,
                              style: const TextStyle(
                                color: Colors.white,
                                fontSize: 17,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                            const SizedBox(height: 5),
                            Text(
                              "الحالة: $label",
                              style: TextStyle(
                                color: color.withOpacity(0.7),
                                fontSize: 13,
                              ),
                            ),
                          ],
                        ),
                      ),
                      const Icon(Icons.arrow_forward_ios, color: Colors.white10, size: 14),
                    ],
                  ),
                ),
              );
            },
          ),
        );
      },
    );
  }
}

class DetailedPatientProfile extends StatelessWidget {
  final _PatientHistoryGroup group;

  const DetailedPatientProfile({super.key, required this.group});

  Color _statusColor(String? label) {
    switch (label) {
      case 'طبيعي':
        return Colors.greenAccent;
      case 'اضطراب بنبض القلب':
        return Colors.orange;
      case 'مصاب بجلطة سابقة':
        return Colors.blueAccent;
      case 'مصاب بجلطة':
        return const Color(0xFFE53935);
      default:
        return Colors.white;
    }
  }

  String? _resolveImagePath(String? path) {
    if (path == null || path
        .trim()
        .isEmpty) return null;

    final finalPath = path.trim();
    if (finalPath.startsWith('http://') || finalPath.startsWith('https://')) {
      return finalPath;
    }

    return "${ApiConfig.baseStorageUrl}/$finalPath";
  }

  Future<Uint8List?> _readImageBytes(String path) async {
    try {
      final resolvedPath = _resolveImagePath(path);
      if (resolvedPath == null || resolvedPath.isEmpty) return null;

      if (resolvedPath.startsWith('http://') ||
          resolvedPath.startsWith('https://')) {
        final client = HttpClient();
        final request = await client.getUrl(Uri.parse(resolvedPath));
        final response = await request.close();

        if (response.statusCode != HttpStatus.ok) {
          return null;
        }

        final bytes = <int>[];
        await for (final chunk in response) {
          bytes.addAll(chunk);
        }
        return Uint8List.fromList(bytes);
      }

      final file = File(resolvedPath);
      if (await file.exists()) {
        return await file.readAsBytes();
      }
    } catch (_) {}
    return null;
  }

  List<String> _symptomsOf(AnalysisRecord record) {
    final symptoms = record.draft.symptoms;

    const Map<String, String> symptomsArabicMap = {
      "chest_pain": "ألم في الصدر",
      "shortness_of_breath": "ضيق في التنفس",
      "dizziness": "دوخة",
      "fatigue": "إرهاق",
      "palpitations": "خفقان القلب",
      "nausea": "غثيان",
      "sweating": "تعرق",
      "fainting": "إغماء",
      "arm_pain": "ألم في الذراع",
      "jaw_pain": "ألم في الفك",
      "back_pain": "ألم في الظهر",
      "rapid_heartbeat": "تسارع ضربات القلب",
      "irregular_heartbeat": "عدم انتظام ضربات القلب",
    };

    if (symptoms is List && symptoms.isNotEmpty) {
      return symptoms.map((e) {
        final key = e.toString().trim();

        return symptomsArabicMap[key] ??
            key
                .replaceAll("_", " ")
                .replaceAllMapped(
              RegExp(r'\b\w'),
                  (m) => m.group(0)!.toUpperCase(),
            );
      }).toList();
    }

    return const [];
  }

  List<String> _medicalAdvice(String label) {
    switch (label) {
      case 'مصاب بجلطة':
        return [
          'مراجعة الطبيب أو الطوارئ فوراً عند وجود ألم صدر أو ضيق نفس أو تعرّق شديد.',
          'الالتزام بالخطة العلاجية التي يحددها الطبيب وعدم إيقاف الأدوية من نفسك.',
          'الراحة وتجنب المجهود العنيف إلى أن يتم تقييم الحالة طبياً.',
          'الاهتمام بضغط الدم والسكر والدهون وتقليل الملح والوجبات الدسمة.',
        ];
      case 'مصاب بجلطة سابقة':
        return [
          'المتابعة الدورية مع الطبيب والالتزام بالأدوية الموصوفة.',
          'المشي الخفيف أو الرياضة المعتدلة بعد موافقة الطبيب.',
          'تقليل الدهون المشبعة والوجبات السريعة، والإكثار من الماء والغذاء المتوازن.',
          'مراقبة أي أعراض جديدة مثل ألم الصدر أو الخفقان أو ضيق النفس.',
        ];
      case 'اضطراب بنبض القلب':
        return [
          'تقليل المنبهات مثل القهوة ومشروبات الطاقة إذا كانت تزيد الأعراض.',
          'تنظيم النوم والراحة وتجنب الإجهاد الشديد.',
          'متابعة الطبيب عند تكرار الخفقان أو الدوخة أو الإغماء.',
          'الابتعاد عن التدخين وأي مواد قد تؤثر على القلب.',
        ];
      default:
        return [
          'الاستمرار على نمط حياة صحي ومتوازن.',
          'مراجعة الطبيب عند ظهور اي أعراض غير معتادة.',
          'تجنب الإفراط في المجهود إذا ظهرت أعراض مثل ألم الصدر أو ضيق النفس.',
        ];
    }
  }

  List<String> _markerNotes(AnalysisRecord record) {
    final troponin = (record.draft.troponin ?? 0).toDouble();
    final ckmb = (record.draft.ckmb ?? 0).toDouble();

    final isHigh = troponin >= 0.06 || ckmb >= 5;

    if (isHigh) {
      return [
        'ملاحظة: ننصح بإعادة الفحصين للتأكد ومراجعة الطبيب.',
      ];
    }
    return [

    ];
  }

  String _reportTitle(String label) {
    switch (label) {
      case 'مصاب بجلطة':
        return ' \n  التوصيات :';
      case 'مصاب بجلطة سابقة':
        return ' \n  التوصيات :';
      case 'اضطراب بنبض القلب':
        return ' \n  التوصيات :';
      default:
        return ' \n  التوصيات :';
    }
  }

  @override
  Widget build(BuildContext context) {
    return GetBuilder<SessionController>(
      builder: (controller) {
        final latest = group.latestRecord;
        final label = latest.result?.label ?? 'غير محدد';
        final color = _statusColor(label);

        final patientName = group.patientName;

        return Scaffold(
          backgroundColor: const Color(0xFF0B0B0D),
          appBar: AppBar(
            backgroundColor: Colors.transparent,
            elevation: 0,
            title: const Text(
                "ملف المريض", style: TextStyle(fontWeight: FontWeight.bold ,color: Colors.white)),
            centerTitle: true,
            actions: [
              IconButton(
                icon: const Icon(
                    Icons.picture_as_pdf, color: Color(0xFFE53935)),
                onPressed: () => _generatePDF(context, group, patientName),
              )
            ],
          ),
          body: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                ...group.records.map((record) {
                  final label = record.result?.label ?? 'غير محدد';
                  final color = _statusColor(label);
                  final ecgPath = record.draft.ecgImagePath?.toString();
                  final symptoms = _symptomsOf(record);

                  return Column(
                    children: [
                      if (ecgPath != null && ecgPath.isNotEmpty)
                        Container(
                          margin: const EdgeInsets.only(bottom: 10),
                          decoration: BoxDecoration(
                            borderRadius: BorderRadius.circular(24),
                            border: Border.all(color: Colors.white10),
                          ),
                          child: ClipRRect(
                            borderRadius: BorderRadius.circular(24),
                            child: Builder(
                              builder: (_) {
                                String? finalPath = ecgPath;
                                if (finalPath != null && finalPath.isNotEmpty) {
                                  if (!finalPath.startsWith('http')) {
                                    finalPath =
                                    "${ApiConfig.baseStorageUrl}/$finalPath";
                                  }
                                  return Image.network(
                                    finalPath,
                                    height: 220,
                                    width: double.infinity,
                                    fit: BoxFit.cover,
                                    errorBuilder: (_, __, ___) =>
                                    const SizedBox(
                                      height: 220,
                                      child: Center(child: Icon(
                                          Icons.broken_image,
                                          color: Colors.white38)),
                                    ),
                                  );
                                }
                                return const SizedBox(
                                  height: 220,
                                  child: Center(child: Icon(
                                      Icons.image_not_supported,
                                      color: Colors.white38)),
                                );
                              },
                            ),
                          ),
                        ),

                      const SizedBox(height: 10),
                      PhoneCard(
                        child: Column(
                          children: [
                            InfoCard(title: "الاسم", value: group.patientName),
                            InfoCard(title: "العمر",
                                value: "${record.draft.age} سنة"),
                            InfoCard(
                                title: "الجنس", value: record.draft.gender),
                            InfoCard(title: "تاريخ الفحص",
                                value: record.createdAt.toString().substring(
                                    0, 10)),
                          ],
                        ),
                      ),
                      const SizedBox(height: 15),
                      PhoneCard(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text("النتائج المخبرية:", style: TextStyle(
                                color: Colors.white54,
                                fontSize: 14,
                                fontWeight: FontWeight.bold)),
                            const SizedBox(height: 5),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: symptoms.map<Widget>((s) {
                                return Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 12, vertical: 6),
                                  decoration: BoxDecoration(
                                    color: Colors.white.withOpacity(0.05),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Text(s, style: const TextStyle(
                                      color: Colors.white, fontSize: 13)),
                                );
                              }).toList(),
                            ),
                            const Divider(color: Colors.white10, height: 30),
                            InfoCard(title: "Troponin",
                                value: "${record.draft.troponin ?? 0} ng/mL"),
                            InfoCard(title: "CK-MB",
                                value: "${record.draft.ckmb ?? 0} ng/mL"),
                          ],
                        ),
                      ),
                      const SizedBox(height: 20),
                      Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: color.withOpacity(0.05),
                          borderRadius: BorderRadius.circular(24),
                          border: Border.all(color: color.withOpacity(0.5)),
                        ),
                        child: Row(
                          children: [
                            Icon(label == 'طبيعي'
                                ? Icons.verified_user_rounded
                                : Icons.warning_amber_rounded, color: color,
                                size: 30),
                            const SizedBox(width: 15),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text("التشخيص النهائي",
                                      style: TextStyle(
                                          color: Colors.white60, fontSize: 12)),
                                  Text(label, style: TextStyle(color: color,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 20)),
                                ],
                              ),
                            )
                          ],
                        ),
                      ),
                      const SizedBox(height: 30),
                      const Divider(color: Colors.white24, thickness: 1),
                      const SizedBox(height: 20),
                    ],
                  );
                }),
                AppButton(
                  text: "مشاركة التقرير الطبي الكامل",
                  onPressed: () =>
                      _generatePDF(context, group, group.patientName),
                ),
                const SizedBox(height: 40),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _generatePDF(BuildContext context,
      _PatientHistoryGroup group,
      String patientName,) async {
    pw.Widget buildRightBullet(String text) {
      return pw.Padding(
        padding: const pw.EdgeInsets.symmetric(vertical: 2),
        child: pw.Row(
          mainAxisAlignment: pw.MainAxisAlignment.end,
          crossAxisAlignment: pw.CrossAxisAlignment.start,
          children: [
            pw.Text(
              "• ",
              style: const pw.TextStyle(fontSize: 14),
            ),
            pw.Expanded(
              child: pw.Text(
                text,
                textAlign: pw.TextAlign.right,
              ),
            ),
          ],
        ),
      );
    }

    final fontData = await rootBundle.load("assets/fonts/Cairo-Regular.ttf");
    final ttf = pw.Font.ttf(fontData);
    final pdf = pw.Document(theme: pw.ThemeData.withFont(base: ttf, bold: ttf));

    final records = List<AnalysisRecord>.from(group.records)
      ..sort((a, b) => b.createdAt.compareTo(a.createdAt));

    for (final record in records) {
      final symptoms = _symptomsOf(record);

      final label = record.result?.label ?? 'غير محدد';
      final advice = _medicalAdvice(label);
      final markerNotes = _markerNotes(record);

      pw.MemoryImage? ecgImage;
      final ecgPath = record.draft.ecgImagePath?.toString();
      if (ecgPath != null && ecgPath.isNotEmpty) {
        final imageBytes = await _readImageBytes(ecgPath);
        if (imageBytes != null) {
          ecgImage = pw.MemoryImage(imageBytes);
        }
      }

      pdf.addPage(
        pw.MultiPage(
          pageFormat: PdfPageFormat.a4,
          theme: pw.ThemeData.withFont(base: ttf, bold: ttf),
          textDirection: pw.TextDirection.rtl,
          build: (pw.Context context) {
            return [
              pw.Row(
                mainAxisAlignment: pw.MainAxisAlignment.spaceBetween,
                children: [
                  pw.Text(
                    "تقرير تطبيق نبض",
                    style: pw.TextStyle(
                      fontSize: 22,
                      fontWeight: pw.FontWeight.bold,
                    ),
                  ),
                  pw.Text(
                    "تاريخ الفحص: ${record.createdAt.toString().substring(
                        0, 10)}",
                  ),
                ],
              ),
              pw.Divider(thickness: 1.5),
              pw.SizedBox(height: 20),
              pw.Text(
                "بيانات المريض:",
                style: pw.TextStyle(
                  fontSize: 16,
                  fontWeight: pw.FontWeight.bold,
                ),
              ),
              buildRightBullet("الاسم الكامل: $patientName"),
              buildRightBullet("العمر: ${record.draft.age} سنة"),
              buildRightBullet("الجنس: ${record.draft.gender}"),
              pw.SizedBox(height: 15),
              pw.Text(
                "الأعراض والنتائج:",
                style: pw.TextStyle(
                  fontSize: 16,
                  fontWeight: pw.FontWeight.bold,
                ),
              ),

              pw.Text(
                "الأعراض: ${symptoms.isNotEmpty
                    ? symptoms.join(' - ')
                    : 'لا توجد أعراض'}",
              ),
              pw.SizedBox(height: 7),
              pw.Text("مستوى Troponin: ${record.draft.troponin ?? 0} ng/mL"),
              pw.Text("مستوى CK-MB: ${record.draft.ckmb ?? 0} ng/mL"),
              if (ecgImage != null) ...[
                pw.SizedBox(height: 15),
                pw.Text("صورة تخطيط القلب (ECG):"),
                pw.Center(
                  child: pw.Container(
                    height: 180,
                    child: pw.Image(ecgImage),
                  ),
                ),
              ],
              pw.SizedBox(height: 20),

              pw.Container(
                padding: const pw.EdgeInsets.all(15),
                decoration: const pw.BoxDecoration(color: PdfColors.grey100),
                child: pw.Row(
                  children: [
                    pw.Text(
                      "نتيجة التنبؤ النهائية: ",
                      style: pw.TextStyle(fontWeight: pw.FontWeight.bold),
                    ),
                    pw.Text(
                      label,
                      style: pw.TextStyle(fontSize: 18, color: PdfColors.red),
                    ),
                  ],
                ),
              ),

              pw.SizedBox(height: 30),
              pw.Text(
                _reportTitle(label),
                style: pw.TextStyle(
                  fontSize: 16,
                  fontWeight: pw.FontWeight.bold,
                ),
              ),
              pw.SizedBox(height: 8),
              ...advice.map(
                    (line) => buildRightBullet(line),
              ),
              ...markerNotes.map(
                    (line) => buildRightBullet(line),
              ),
              pw.SizedBox(height: 20),

            ];
          },
          footer: (pw.Context context) {
            return pw.Padding(
              padding: const pw.EdgeInsets.only(top: 10),
              child: pw.Center(
                child: pw.Text(
                  "تم إنتاج هذا التقرير بواسطة تطبيق نبض الذكي",
                  style: const pw.TextStyle(
                      fontSize: 10, color: PdfColors.grey),
                ),
              ),
            );
          },
        ),
      );
    }

    await Printing.layoutPdf(
      onLayout: (format) async => pdf.save(),
      name: 'Pulse_Report_$patientName.pdf',
    );
  }
}