import 'dart:io';
import 'package:dio/dio.dart';
import '../api/api_service.dart';

class ErrorHandler {
  static String getMessage(dynamic error) {
    if (error is ApiException) {
      return _handleApiException(error);
    }

    if (error is SocketException) {
      return "لا يوجد اتصال بالإنترنت";
    }

    if (error is DioException) {
      switch (error.type) {
        case DioExceptionType.connectionTimeout:
          return "انتهت مهلة الاتصال";
        case DioExceptionType.sendTimeout:
          return "فشل إرسال الطلب";
        case DioExceptionType.receiveTimeout:
          return "تأخر استجابة الخادم";
        case DioExceptionType.badResponse:
          return _handleStatusCode(error.response?.statusCode, error.response?.data);
        case DioExceptionType.cancel:
          return "تم إلغاء الطلب";
        default:
          return "حدث خطأ غير متوقع";
      }
    }

    if (error is FileSystemException) {
      return "تعذر الوصول إلى الملف";
    }

    if (error is FormatException) {
      return "صيغة البيانات غير صحيحة";
    }

    return "حدث خطأ غير معروف";
  }

  static String _handleApiException(ApiException error) {
    switch (error.statusCode) {
      case 400:
        return error.message.isNotEmpty ? error.message : "طلب غير صالح";
      case 401:
        return "انتهت الجلسة أو بيانات الدخول غير صحيحة";
      case 403:
        return "ليس لديك صلاحية للوصول";
      case 404:
        // return "البيان المطلوب غير موجود";
      case 422:
        return error.message.isNotEmpty ? error.message : "البيانات المدخلة غير صحيحة";
      case 429:
        return "تم إرسال طلبات كثيرة، حاول لاحقًا";
      case 500:
        return "حدث خطأ في الخادم، حاول مرة أخرى";
      default:
        return error.message.isNotEmpty
            ? error.message
            : "حدث خطأ غير متوقع";
    }
  }

  static String _handleStatusCode(int? statusCode, dynamic data) {
    switch (statusCode) {
      case 400:
        return "طلب غير صالح";
      case 401:
        return "غير مصرح لك، الرجاء تسجيل الدخول";
      case 403:
        return "ليس لديك صلاحية";
      case 404:
        return "العنصر غير موجود";
      case 422:
        return _handleValidationError(data);
      case 429:
        return "تم إرسال طلبات كثيرة، حاول لاحقًا";
      case 500:
        return "حدث خطأ في الخادم";
      default:
        return "حدث خطأ (كود: $statusCode)";
    }
  }

  static String _handleValidationError(dynamic data) {
    try {
      if (data is Map && data['errors'] != null) {
        final errors = Map<String, dynamic>.from(data['errors']);
        final firstKey = errors.keys.isNotEmpty ? errors.keys.first : null;
        if (firstKey != null) {
          final firstValue = errors[firstKey];
          if (firstValue is List && firstValue.isNotEmpty) {
            return firstValue.first.toString();
          }
        }
      }

      if (data is Map && data['message'] != null) {
        return data['message'].toString();
      }
    } catch (_) {}

    return "البيانات المدخلة غير صحيحة";
  }
}