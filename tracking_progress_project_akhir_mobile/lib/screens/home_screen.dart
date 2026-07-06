import 'package:file_picker/file_picker.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'package:tracking_progress_project_akhir_mobile/screens/login_screen.dart';
import 'package:tracking_progress_project_akhir_mobile/screens/submit_progress_screen.dart';
import 'package:tracking_progress_project_akhir_mobile/services/api_service.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key});

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  Map<String, dynamic>? _profile;
  List<dynamic> _history = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadData();
  }

  Future<void> _loadData() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final token = await ApiService.getStoredToken();
      if (token == null || token.isEmpty) {
        if (!mounted) return;
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const LoginScreen()),
          (route) => false,
        );
        return;
      }

      final profile = await ApiService.getProfile(token);
      final history = await ApiService.getProgressHistory(token);

      if (!mounted) return;
      setState(() {
        _profile = profile;
        _history = history;
      });
    } catch (e) {
      setState(() => _error = e.toString());
    } finally {
      if (mounted) {
        setState(() => _loading = false);
      }
    }
  }

  Future<void> _logout() async {
    try {
      final token = await ApiService.getStoredToken();
      if (token != null && token.isNotEmpty) {
        await ApiService.logout(token);
      }
    } catch (_) {
      // abaikan error logout, tetap lanjut ke login
    } finally {
      await ApiService.clearToken();
    }

    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(
      MaterialPageRoute(builder: (_) => const LoginScreen()),
      (route) => false,
    );
  }

  Color _statusColor(String status) {
    final normalized = status.toLowerCase();
    if (normalized.contains('diterima') || normalized.contains('setuju')) {
      return Colors.green;
    }
    if (normalized.contains('revisi') || normalized.contains('tolak')) {
      return Colors.orange;
    }
    return Colors.blue;
  }

  Future<void> _showEditDialog(Map<String, dynamic> item) async {
    final formKey = GlobalKey<FormState>();
    final titleController = TextEditingController(text: item['judul_project']?.toString() ?? '');
    final descriptionController = TextEditingController(text: item['deskripsi_progress']?.toString() ?? '');

    final dialogContext = context;
    PlatformFile? selectedFile;

    await showDialog<void>(
      context: dialogContext,
      barrierDismissible: false,
      builder: (dialogContext) {
        return Dialog(
          insetPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 24),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(24)),
          child: StatefulBuilder(
            builder: (context, setDialogState) {
              return Padding(
                padding: const EdgeInsets.all(20),
                child: Form(
                  key: formKey,
                  child: Column(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Text(
                        'Edit Progress',
                        style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.w700),
                      ),
                      const SizedBox(height: 16),
                      _buildModernField(
                        controller: titleController,
                        label: 'Judul Project',
                        icon: Icons.title_rounded,
                        validator: (value) => (value == null || value.trim().isEmpty) ? 'Judul wajib diisi' : null,
                      ),
                      const SizedBox(height: 12),
                      _buildModernField(
                        controller: descriptionController,
                        label: 'Deskripsi Progress',
                        icon: Icons.description_outlined,
                        maxLines: 5,
                        validator: (value) => (value == null || value.trim().isEmpty) ? 'Deskripsi wajib diisi' : null,
                      ),
                      const SizedBox(height: 12),
                      _buildUploadCard(
                        title: 'Upload File Laporan',
                        subtitle: selectedFile?.name ?? 'Pilih file PDF / DOC / DOCX',
                        icon: Icons.upload_file_rounded,
                        onPressed: () async {
                          final result = await FilePicker.platform.pickFiles(
                            type: FileType.custom,
                            allowedExtensions: ['pdf', 'doc', 'docx'],
                            allowMultiple: false,
                          );

                          if (result == null || result.files.isEmpty) return;

                          setDialogState(() {
                            selectedFile = result.files.first;
                          });
                        },
                      ),
                      const SizedBox(height: 20),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () => Navigator.of(dialogContext).pop(),
                              style: OutlinedButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              ),
                              child: const Text('Batal'),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            child: FilledButton(
                              onPressed: () async {
                                if (!formKey.currentState!.validate()) return;

                                final navigator = Navigator.of(dialogContext);
                                final messenger = ScaffoldMessenger.of(dialogContext);
                                final token = await ApiService.getStoredToken();
                                if (token == null || token.isEmpty) {
                                  if (!mounted) return;
                                  navigator.pop();
                                  return;
                                }

                                try {
                                  final files = <http.MultipartFile>[];
                                  if (selectedFile != null) {
                                    files.add(await _buildMultipartFile('file_laporan', selectedFile!));
                                  }

                                  final result = await ApiService.updateProgress(
                                    token,
                                    item['id'] as int,
                                    {
                                      'judul_project': titleController.text.trim(),
                                      'deskripsi_progress': descriptionController.text.trim(),
                                      'persentase_estimasi': 0,
                                    },
                                    files: files,
                                  );

                                  if (!mounted) return;
                                  navigator.pop();
                                  setState(() {
                                    final index = _history.indexWhere((entry) {
                                      final map = entry as Map<String, dynamic>;
                                      return map['id'] == item['id'];
                                    });
                                    if (index != -1) {
                                      _history[index] = {
                                        ...(_history[index] as Map<String, dynamic>),
                                        ...(result['data'] as Map<String, dynamic>? ?? <String, dynamic>{}),
                                      };
                                    }
                                  });
                                  messenger.showSnackBar(
                                    SnackBar(content: Text(result['message'] ?? 'Progress berhasil diperbarui')),
                                  );
                                } catch (e) {
                                  if (!mounted) return;
                                  messenger.showSnackBar(
                                    SnackBar(content: Text(e.toString())),
                                  );
                                }
                              },
                              style: FilledButton.styleFrom(
                                padding: const EdgeInsets.symmetric(vertical: 14),
                                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
                              ),
                              child: const Text('Simpan'),
                            ),
                          ),
                        ],
                      ),
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

  Widget _buildModernField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    TextInputType? keyboardType,
    List<TextInputFormatter>? inputFormatters,
    int maxLines = 1,
    int? maxLength,
    String? hintText,
    String? suffixText,
    String? Function(String?)? validator,
  }) {
    return TextFormField(
      controller: controller,
      keyboardType: keyboardType,
      inputFormatters: inputFormatters,
      maxLines: maxLines,
      minLines: 1,
      maxLength: maxLength,
      decoration: InputDecoration(
        labelText: label,
        hintText: hintText,
        prefixIcon: Icon(icon, color: const Color(0xFF4F46E5)),
        suffixText: suffixText,
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

  Future<void> _deleteProgress(Map<String, dynamic> item) async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (dialogContext) => AlertDialog(
        title: const Text('Hapus Progress'),
        content: const Text('Apakah Anda yakin ingin menghapus progress ini?'),
        actions: [
          TextButton(onPressed: () => Navigator.of(dialogContext).pop(false), child: const Text('Batal')),
          FilledButton(
            onPressed: () => Navigator.of(dialogContext).pop(true),
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Hapus'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    try {
      final token = await ApiService.getStoredToken();
      if (token == null || token.isEmpty) {
        throw Exception('Silakan login ulang');
      }

      final result = await ApiService.deleteProgress(token, item['id'] as int);
      if (!mounted) return;
      setState(() {
        _history.removeWhere((entry) {
          final map = entry as Map<String, dynamic>;
          return map['id'] == item['id'];
        });
      });
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(result['message'] ?? 'Progress berhasil dihapus')),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(e.toString())),
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    final user = (_profile?['user'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final mahasiswaFromTopLevel = (_profile?['mahasiswa'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final mahasiswaFromUser = (user['mahasiswa'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final mahasiswa = mahasiswaFromTopLevel.isNotEmpty ? mahasiswaFromTopLevel : mahasiswaFromUser;
    final kelas = (mahasiswa['kelas'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final prodi = (kelas['program_studi'] as Map<String, dynamic>?) ?? <String, dynamic>{};
    final role = (user['role'] ?? '').toString().toLowerCase();
    final isMahasiswa = role == 'mahasiswa';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Dashboard Progress'),
        actions: [
          IconButton(onPressed: _logout, icon: const Icon(Icons.logout_rounded)),
        ],
        backgroundColor: const Color(0xFF4F46E5),
        foregroundColor: Colors.white,
        shape: const RoundedRectangleBorder(
          borderRadius: BorderRadius.vertical(bottom: Radius.circular(24)),
        ),
      ),
      body: Container(
        decoration: const BoxDecoration(
          gradient: LinearGradient(
            colors: [Color(0xFFF5F7FF), Color(0xFFEEF2FF)],
            begin: Alignment.topCenter,
            end: Alignment.bottomCenter,
          ),
        ),
        child: RefreshIndicator(
          onRefresh: _loadData,
          child: _loading
              ? const Center(child: CircularProgressIndicator())
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    if (_error != null)
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: Colors.red.shade50,
                          borderRadius: BorderRadius.circular(16),
                        ),
                        child: Text(_error!, style: const TextStyle(color: Colors.red)),
                      )
                    else ...[
                      Container(
                        padding: const EdgeInsets.all(18),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(24),
                          boxShadow: [
                            BoxShadow(
                              color: Colors.black.withValues(alpha: 0.05),
                              blurRadius: 16,
                              offset: const Offset(0, 8),
                            ),
                          ],
                        ),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                CircleAvatar(
                                  radius: 24,
                                  backgroundColor: const Color(0xFFEEF2FF),
                                  child: const Icon(Icons.person_outline, color: Color(0xFF4F46E5)),
                                ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment: CrossAxisAlignment.start,
                                    children: [
                                      Text(
                                        'Selamat datang, ${user['name'] ?? '-'}',
                                        style: Theme.of(context).textTheme.titleLarge?.copyWith(fontWeight: FontWeight.bold),
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                        user['email']?.toString() ?? '-',
                                        style: TextStyle(color: Colors.grey.shade600),
                                      ),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 16),
                            Wrap(
                              spacing: 8,
                              runSpacing: 8,
                              children: [
                                _infoChip(Icons.badge_outlined, 'NIM: ${mahasiswa['nim'] ?? '-'}'),
                                _infoChip(Icons.class_outlined, 'Kelas: ${kelas['nama_kelas'] ?? '-'}'),
                                _infoChip(Icons.school_outlined, 'Prodi: ${prodi['nama_prodi'] ?? '-'}'),
                              ],
                            ),
                          ],
                        ),
                      ),
                      const SizedBox(height: 16),
                      Row(
                        children: [
                          Expanded(
                            child: Text(
                              'Riwayat Progress',
                              style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.bold),
                            ),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                            decoration: BoxDecoration(
                              color: const Color(0xFF4F46E5).withValues(alpha: 0.1),
                              borderRadius: BorderRadius.circular(999),
                            ),
                            child: Text(
                              '${_history.length} item',
                              style: const TextStyle(color: Color(0xFF4F46E5), fontWeight: FontWeight.w600),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      if (_history.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(16),
                          decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: const Text('Belum ada progress yang dikirim.'),
                        )
                      else
                        ..._history.map((item) {
                          final map = item as Map<String, dynamic>;
                          final status = (map['validation_status'] ?? map['status'] ?? 'Pending').toString();
                          final note = map['validation_note'] ?? '';
                          return Container(
                            margin: const EdgeInsets.only(bottom: 12),
                            padding: const EdgeInsets.all(16),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(20),
                              boxShadow: [
                                BoxShadow(
                                  color: Colors.black.withValues(alpha: 0.05),
                                  blurRadius: 12,
                                  offset: const Offset(0, 6),
                                ),
                              ],
                            ),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                Row(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment: CrossAxisAlignment.start,
                                        children: [
                                          Text(
                                            map['judul_project'] ?? 'Tanpa judul',
                                            style: Theme.of(context).textTheme.titleMedium?.copyWith(fontWeight: FontWeight.w600),
                                          ),
                                          const SizedBox(height: 6),
                                          Text(
                                            map['deskripsi_progress'] ?? '-',
                                            maxLines: 3,
                                            overflow: TextOverflow.ellipsis,
                                          ),
                                          const SizedBox(height: 6),
                                          Text('Status validasi: $status'),
                                          if (note.toString().isNotEmpty) ...[
                                            const SizedBox(height: 4),
                                            Text('Catatan dosen: $note'),
                                          ],
                                        ],
                                      ),
                                    ),
                                    const SizedBox(width: 12),
                                    Chip(
                                      label: Text(status),
                                      backgroundColor: _statusColor(status),
                                      labelStyle: const TextStyle(color: Colors.white),
                                    ),
                                  ],
                                ),
                                if (isMahasiswa) ...[
                                  const SizedBox(height: 10),
                                  Wrap(
                                    spacing: 8,
                                    runSpacing: 8,
                                    children: [
                                      SizedBox(
                                        height: 40,
                                        child: FilledButton.icon(
                                          onPressed: () => _showEditDialog(map),
                                          icon: const Icon(Icons.edit, size: 18),
                                          label: const Text('Edit'),
                                        ),
                                      ),
                                      SizedBox(
                                        height: 40,
                                        child: FilledButton.icon(
                                          onPressed: () => _deleteProgress(map),
                                          icon: const Icon(Icons.delete, size: 18),
                                          label: const Text('Hapus'),
                                          style: FilledButton.styleFrom(backgroundColor: Colors.red.shade600),
                                        ),
                                      ),
                                    ],
                                  ),
                                ],
                              ],
                            ),
                          );
                        }),
                    ],
                  ],
                ),
        ),
      ),
      floatingActionButton: isMahasiswa
          ? FloatingActionButton.extended(
              onPressed: () async {
                await Navigator.of(context).push(
                  MaterialPageRoute(builder: (_) => const SubmitProgressScreen()),
                );
                _loadData();
              },
              icon: const Icon(Icons.add),
              label: const Text('Tambah Progress'),
              backgroundColor: const Color(0xFF4F46E5),
            )
          : null,
    );
  }

  Widget _infoChip(IconData icon, String text) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
      decoration: BoxDecoration(
        color: const Color(0xFFEEF2FF),
        borderRadius: BorderRadius.circular(999),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          Icon(icon, size: 16, color: const Color(0xFF4F46E5)),
          const SizedBox(width: 6),
          Text(text, style: const TextStyle(color: Color(0xFF4338CA))),
        ],
      ),
    );
  }
}
