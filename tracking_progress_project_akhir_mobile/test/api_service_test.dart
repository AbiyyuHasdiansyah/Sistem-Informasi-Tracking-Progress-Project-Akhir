import 'package:flutter_test/flutter_test.dart';
import 'package:tracking_progress_project_akhir_mobile/services/api_service.dart';

void main() {
  test('normalizes malformed http base URLs', () {
    expect(
      ApiService.normalizeBaseUrl('http//localhost:8000/api'),
      'http://localhost:8000/api',
    );
  });

  test('adds scheme for scheme-less urls', () {
    expect(
      ApiService.normalizeBaseUrl('localhost:8000/api'),
      'http://localhost:8000/api',
    );
  });
}
