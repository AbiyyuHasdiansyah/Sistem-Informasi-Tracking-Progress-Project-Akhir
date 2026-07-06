import 'dart:convert';

import 'package:flutter/foundation.dart';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';

class ApiService {
  static String get baseUrl {
    if (kIsWeb) {
      return normalizeBaseUrl('http://localhost:8000/api');
    }

    switch (defaultTargetPlatform) {
      case TargetPlatform.android:
        return normalizeBaseUrl('http://10.0.2.2:8000/api');
      case TargetPlatform.iOS:
      case TargetPlatform.macOS:
      case TargetPlatform.windows:
      case TargetPlatform.linux:
      default:
        return normalizeBaseUrl('http://127.0.0.1:8000/api');
    }
  }

  static String normalizeBaseUrl(String value) {
    final trimmed = value.trim();
    if (trimmed.isEmpty) {
      return 'http://127.0.0.1:8000/api';
    }

    final fixed = trimmed.replaceFirst(RegExp(r'^https?//'), 'http://');
    return fixed.contains('://') ? fixed : 'http://$fixed';
  }

  static Future<String?> getStoredToken() async {
    final prefs = await SharedPreferences.getInstance();
    return prefs.getString('auth_token');
  }

  static Future<void> saveToken(String token) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('auth_token', token);
  }

  static Future<void> clearToken() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('auth_token');
  }

  static Future<Map<String, dynamic>> login(String email, String password) async {
    final response = await http.post(
      Uri.parse('$baseUrl/login'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({'email': email, 'password': password}),
    ).timeout(const Duration(seconds: 15));

    final decoded = _decodeJson(response);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    throw Exception(decoded['message'] ?? 'Login gagal. Pastikan backend Laravel berjalan.');
  }

  static Future<Map<String, dynamic>> register({
    required String name,
    required String email,
    required String password,
    required String passwordConfirmation,
    required String nim,
    required String noHp,
    required int kelasId,
    required String role,
  }) async {
    final response = await http.post(
      Uri.parse('$baseUrl/register'),
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
      },
      body: jsonEncode({
        'name': name,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        'nim': nim,
        'no_hp': noHp,
        'kelas_id': kelasId,
        'role': role,
      }),
    ).timeout(const Duration(seconds: 15));

    final decoded = _decodeJson(response);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    throw Exception(decoded['message'] ?? 'Registrasi gagal. Pastikan backend Laravel berjalan.');
  }

  static Future<Map<String, dynamic>> getProfile(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/profile'),
      headers: await _headers(token: token),
    ).timeout(const Duration(seconds: 15));

    final decoded = _decodeJson(response);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    throw Exception(decoded['message'] ?? 'Gagal mengambil profil. Pastikan token valid.');
  }

  static Future<Map<String, dynamic>> logout(String token) async {
    final response = await http.post(
      Uri.parse('$baseUrl/logout'),
      headers: await _headers(token: token),
    ).timeout(const Duration(seconds: 15));

    final decoded = _decodeJson(response);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    throw Exception(decoded['message'] ?? 'Logout gagal.');
  }

  static Future<List<dynamic>> getProgressHistory(String token) async {
    final response = await http.get(
      Uri.parse('$baseUrl/progress/riwayat'),
      headers: await _headers(token: token),
    ).timeout(const Duration(seconds: 15));

    final decoded = _decodeJson(response);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      if (decoded['data'] is List) {
        return List<dynamic>.from(decoded['data'] as List);
      }
      return <dynamic>[];
    }

    throw Exception(decoded['message'] ?? 'Gagal mengambil riwayat progress.');
  }

  static Future<Map<String, dynamic>> submitProgress(
    String token,
    Map<String, dynamic> data, {
    List<http.MultipartFile> files = const [],
  }) async {
    final request = http.MultipartRequest('POST', Uri.parse('$baseUrl/progress/submit'));
    request.headers['Authorization'] = 'Bearer $token';
    request.fields.addAll(
      data.map((key, value) => MapEntry(key, value.toString())),
    );
    if (files.isNotEmpty) {
      request.files.addAll(files);
    }

    final streamedResponse = await request.send();
    final responseBody = await streamedResponse.stream.bytesToString();
    final decoded = _decodeResponseBody(responseBody);

    if (streamedResponse.statusCode >= 200 && streamedResponse.statusCode < 300) {
      return decoded;
    }

    throw Exception(_buildErrorMessage(decoded, 'Gagal mengirim progress.'));
  }

  static Future<Map<String, dynamic>> updateProgress(
    String token,
    int id,
    Map<String, dynamic> data, {
    List<http.MultipartFile> files = const [],
  }) async {
    final request = http.MultipartRequest('POST', Uri.parse('$baseUrl/progress/$id'));
    request.headers['Authorization'] = 'Bearer $token';
    request.headers['Accept'] = 'application/json';
    request.fields['_method'] = 'PATCH';
    request.fields.addAll(
      data.map((key, value) => MapEntry(key, value.toString())),
    );
    if (files.isNotEmpty) {
      request.files.addAll(files);
    }

    final streamedResponse = await request.send();
    final responseBody = await streamedResponse.stream.bytesToString();
    final decoded = _decodeResponseBody(responseBody);

    if (streamedResponse.statusCode >= 200 && streamedResponse.statusCode < 300) {
      return decoded;
    }

    throw Exception(_buildErrorMessage(decoded, 'Gagal memperbarui progress.'));
  }

  static Future<Map<String, dynamic>> deleteProgress(String token, int id) async {
    final response = await http.delete(
      Uri.parse('$baseUrl/progress/$id'),
      headers: await _headers(token: token),
    ).timeout(const Duration(seconds: 15));

    final decoded = _decodeJson(response);
    if (response.statusCode >= 200 && response.statusCode < 300) {
      return decoded;
    }

    throw Exception(decoded['message'] ?? 'Gagal menghapus progress.');
  }

  static Future<Map<String, String>> _headers({required String token}) async {
    return {
      'Authorization': 'Bearer $token',
      'Accept': 'application/json',
      'Content-Type': 'application/json',
    };
  }

  static String _buildErrorMessage(Map<String, dynamic> decoded, String fallback) {
    final message = decoded['message']?.toString();
    if (message != null && message.isNotEmpty) {
      return message;
    }

    if (decoded['errors'] is Map) {
      final errors = decoded['errors'] as Map;
      final messages = <String>[];
      errors.forEach((key, value) {
        if (value is List) {
          for (final item in value) {
            if (item is String && item.isNotEmpty) {
              messages.add(item);
            }
          }
        } else if (value is String && value.isNotEmpty) {
          messages.add(value);
        }
      });

      if (messages.isNotEmpty) {
        return messages.join(' ');
      }
    }

    return fallback;
  }

  static Map<String, dynamic> _decodeJson(http.Response response) {
    return _decodeResponseBody(response.body);
  }

  static Map<String, dynamic> _decodeResponseBody(String body) {
    if (body.isEmpty) {
      return <String, dynamic>{};
    }

    try {
      return jsonDecode(body) as Map<String, dynamic>;
    } catch (_) {
      return <String, dynamic>{'message': 'Respons server bukan JSON. Pastikan backend Laravel berjalan dan route API benar.'};
    }
  }
}
