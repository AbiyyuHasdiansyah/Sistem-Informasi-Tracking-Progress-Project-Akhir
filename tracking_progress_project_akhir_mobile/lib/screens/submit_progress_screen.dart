import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:tracking_progress_project_akhir_mobile/services/api_service.dart';

class SubmitProgressScreen extends StatefulWidget {
  const SubmitProgressScreen({super.key});

  @override
  State<SubmitProgressScreen> createState() => _SubmitProgressScreenState();
}

class _SubmitProgressScreenState extends State<SubmitProgressScreen> {
  final _formKey = GlobalKey<FormState>();
  final _judulController = TextEditingController();
  final _deskripsiController = TextEditingController();
  PlatformFile? _laporanFile;
  bool _loading = false;
  String? _error;

  @override
  void dispose() {
    _judulController.dispose();
    _deskripsiController.dispose();
    super.dispose();
  }

  Future<void> _pickFile() async {
    final result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['pdf', 'doc', 'docx'],
      allowMultiple: false,
    );

    if (result == null || result.files.isEmpty) return;

    setState(() {
      _laporanFile = result.files.first;
    });
  }

  Future<List<http.MultipartFile>> _buildFiles() async {
    final files = <http.MultipartFile>[];

    if (_laporanFile != null) {
      files.add(await _buildMultipartFile('file_laporan', _laporanFile!));
    }

    return files;
  }

  Future<http.MultipartFile> _buildMultipartFile(String fieldName, PlatformFile file) async {
    if (!kIsWeb && file.path != null && file.path!.isNotEmpty) {
      return http.MultipartFile.fromPath(fieldName, file.path!);
    }

    if (file.bytes != null) {
      return http.MultipartFile.fromBytes(
        fieldName,
        file.bytes!,
        filename: file.name,
      );
    }

    throw Exception('File tidak dapat diproses karena format tidak didukung.');
  }

  Future<void> _submit() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final token = await ApiService.getStoredToken();
      if (token == null || token.isEmpty) {
        throw Exception('Silakan login ulang');
      }

      final files = await _buildFiles();
      final result = await ApiService.submitProgress(
        token,
        {
          'kelas_mata_kuliah_id': '1',
          'judul_project': _judulController.text.trim(),
          'deskripsi_progress': _deskripsiController.text.trim(),
          'persentase_estimasi': 0,
        },
        files: files,
      );

      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'] ?? 'Progress berhasil dikirim')),
      );
      Navigator.of(context).pop();
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Kirim Progress'),
        backgroundColor: Colors.transparent,
        elevation: 0,
        foregroundColor: const Color(0xFF111827),
      ),
      extendBodyBehindAppBar: true,
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFF5F7FF), Color(0xFFEEF2FF)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.fromLTRB(20, 90, 20, 24),
            child: Card(
              elevation: 0,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      if (_error != null)
                        Container(
                          margin: const EdgeInsets.only(bottom: 12),
                          padding: const EdgeInsets.all(12),
                          decoration: BoxDecoration(
                            color: Colors.red.shade50,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(_error!, style: const TextStyle(color: Colors.red)),
                        ),
                      _buildModernField(
                        controller: _judulController,
                        label: 'Judul Project',
                        icon: Icons.title_rounded,
                        validator: (value) => (value == null || value.trim().isEmpty) ? 'Judul wajib diisi' : null,
                      ),
                      const SizedBox(height: 12),
                      _buildModernField(
                        controller: _deskripsiController,
                        label: 'Deskripsi Progress',
                        icon: Icons.description_outlined,
                        maxLines: 5,
                        validator: (value) => (value == null || value.trim().isEmpty) ? 'Deskripsi wajib diisi' : null,
                      ),
                      const SizedBox(height: 12),
                      _buildUploadCard(
                        title: 'Upload File Laporan',
                        subtitle: _laporanFile?.name ?? 'Pilih file PDF / DOC / DOCX',
                        icon: Icons.upload_file_rounded,
                        onPressed: _pickFile,
                      ),
                      const SizedBox(height: 20),
                      FilledButton.icon(
                        style: FilledButton.styleFrom(
                          padding: const EdgeInsets.symmetric(vertical: 14),
                          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                        ),
                        onPressed: _loading ? null : _submit,
                        icon: _loading
                            ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                            : const Icon(Icons.send_rounded),
                        label: Text(_loading ? 'Mengirim...' : 'Kirim Progress'),
                      ),
                    ],
                  ),
                ),
              ),
            ),
          ),
        ),
      ),
    );
  }

  Widget _buildModernField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    int maxLines = 1,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      maxLines: maxLines,
      minLines: 1,
      decoration: InputDecoration(
        labelText: label,
        prefixIcon: Icon(icon, color: const Color(0xFF4F46E5)),
        filled: true,
        fillColor: const Color(0xFFF8FAFF),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
        enabledBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: BorderSide(color: Colors.grey.shade200),
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Color(0xFF4F46E5), width: 1.4),
        ),
        errorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Colors.redAccent),
        ),
        focusedErrorBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(16),
          borderSide: const BorderSide(color: Colors.redAccent, width: 1.4),
        ),
      ),
      validator: validator,
    );
  }

  Widget _buildUploadCard({
    required String title,
    required String subtitle,
    required IconData icon,
    required VoidCallback onPressed,
  }) {
    return InkWell(
      onTap: onPressed,
      borderRadius: BorderRadius.circular(18),
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          color: const Color(0xFFF8FAFF),
          border: Border.all(color: Colors.grey.shade200),
          borderRadius: BorderRadius.circular(18),
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: const Color(0xFFEEF2FF),
                borderRadius: BorderRadius.circular(14),
              ),
              child: Icon(icon, color: const Color(0xFF4F46E5)),
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(title, style: const TextStyle(fontWeight: FontWeight.w600)),
                  const SizedBox(height: 2),
                  Text(subtitle, style: TextStyle(color: Colors.grey.shade600)),
                ],
              ),
            ),
            const Icon(Icons.attach_file_rounded, color: Color(0xFF4F46E5)),
          ],
        ),
      ),
    );
  }
}
