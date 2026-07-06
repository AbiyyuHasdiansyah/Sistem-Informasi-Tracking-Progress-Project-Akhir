<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ProgressProject;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class WebProgressController extends Controller
{
    /**
     * READ: Menampilkan semua data tracking progress di dashboard web dosen dengan pagination
     */
    public function index()
    {
        // Ambil role user untuk authorization
        $user = Auth::user();
        
        // Jika dosen, tampilkan hanya progress dari kelas mereka
        // Jika admin, tampilkan semua
        if ($user && $user->role === 'dosen') {
            $dosen = $user->dosen;
            $allProgress = ProgressProject::whereHas('kelasMataKuliah', function ($query) use ($dosen) {
                $query->where('dosen_id', $dosen->id);
            })
            ->with(['mahasiswa.user', 'kelasMataKuliah.mataKuliah', 'kelasMataKuliah.dosen'])
            ->latest()
            ->paginate(15);
        } else {
            $allProgress = ProgressProject::with(['mahasiswa.user', 'kelasMataKuliah.mataKuliah'])
                ->latest()
                ->paginate(15);
        }
        
        return view('dashboard', compact('allProgress'));
    }

    /**
     * CREATE: Form tambah progress manual dari web (jika diperlukan)
     */
    public function create()
    {
        return view('progress.create');
    }

    /**
     * STORE: Menyimpan progress baru dari form web dengan validasi file
     */
    public function store(Request $request)
    {
        $request->validate([
            'mahasiswa_id' => 'required|exists:mahasiswas,id',
            'kelas_mata_kuliah_id' => 'required|exists:kelas_mata_kuliah,id',
            'judul_project' => 'required|string|max:255',
            'deskripsi_progress' => 'nullable|string|max:1000',
            'persentase_estimasi' => 'nullable|integer|between:0,100',
            'file_database' => 'nullable|file|mimes:sql|max:10240',  // Max 10MB
            'file_laporan' => 'nullable|file|mimes:doc,docx,pdf|max:25600', // Max 25MB
            'link_github' => 'nullable|url',
            'link_gdrive' => 'nullable|url',
            'link_youtube' => 'nullable|url',
        ], [
            'file_database.mimes' => 'File database harus berformat .sql',
            'file_database.max' => 'File database maksimal 10MB',
            'file_laporan.mimes' => 'File laporan harus berformat .doc, .docx, atau .pdf',
            'file_laporan.max' => 'File laporan maksimal 25MB',
        ]);

        // Handle file upload untuk database
        $dbPath = null;
        if ($request->hasFile('file_database')) {
            $file = $request->file('file_database');
            $dbPath = $file->store('progress/databases', 'public');
        }

        // Handle file upload untuk laporan
        $laporanPath = null;
        if ($request->hasFile('file_laporan')) {
            $file = $request->file('file_laporan');
            $laporanPath = $file->store('progress/laporans', 'public');
        }

        ProgressProject::create([
            'mahasiswa_id' => $request->mahasiswa_id,
            'kelas_mata_kuliah_id' => $request->kelas_mata_kuliah_id,
            'judul_project' => $request->judul_project,
            'deskripsi_progress' => $request->deskripsi_progress,
            'persentase_estimasi' => $request->persentase_estimasi,
            'file_database' => $dbPath,
            'file_laporan' => $laporanPath,
            'link_github' => $request->link_github,
            'link_gdrive' => $request->link_gdrive,
            'link_youtube' => $request->link_youtube,
            'status' => 'Pending',
            'submitted_at' => now(),
        ]);

        return redirect('/')->with('success', 'Data progress project berhasil ditambahkan!');
    }

    /**
     * EDIT: Form untuk mengubah data tracking progress
     */
    public function edit($id)
    {
        $progress = ProgressProject::with(['mahasiswa', 'kelasMataKuliah'])->findOrFail($id);
        
        $user = Auth::user();
        if ($user->role === 'dosen' && $progress->kelasMataKuliah->dosen_id !== $user->dosen->id) {
            abort(403, 'Anda tidak memiliki akses untuk memvalidasi progress ini.');
        }
        
        return view('progress.edit', compact('progress'));
    }

    public function preview($id)
    {
        $progress = ProgressProject::with(['mahasiswa.user', 'kelasMataKuliah.mataKuliah'])->findOrFail($id);

        $user = Auth::user();
        if ($user->role === 'dosen' && $progress->kelasMataKuliah->dosen_id !== $user->dosen->id) {
            abort(403, 'Anda tidak memiliki akses untuk melihat file ini.');
        }

        if (!$progress->file_laporan) {
            abort(404, 'Tidak ada laporan yang tersedia untuk progress ini.');
        }

        $fileUrl = route('progress.file', ['id' => $progress->id]);

        return view('progress.preview', compact('progress', 'fileUrl'));
    }

    public function file($id)
    {
        $progress = ProgressProject::findOrFail($id);

        $user = Auth::user();
        if ($user->role === 'dosen' && $progress->kelasMataKuliah->dosen_id !== $user->dosen->id) {
            abort(403, 'Anda tidak memiliki akses untuk melihat file ini.');
        }

        if (!$progress->file_laporan) {
            abort(404, 'Tidak ada laporan yang tersedia untuk progress ini.');
        }

        $fullPath = storage_path('app/public/' . $progress->file_laporan);
        if (!file_exists($fullPath)) {
            abort(404, 'File tidak ditemukan.');
        }

        $filename = basename($progress->file_laporan);
        $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        $mimeTypes = [
            'pdf' => 'application/pdf',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'txt' => 'text/plain',
            'html' => 'text/html',
            'doc' => 'application/msword',
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ];

        $contentType = $mimeTypes[$extension] ?? 'application/octet-stream';

        return response()->file($fullPath, [
            'Content-Type' => $contentType,
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    /**
     * REVIEW: Validasi status progress oleh dosen
     */
    public function review(Request $request, $id)
    {
        $progress = ProgressProject::findOrFail($id);

        $user = Auth::user();
        if ($user->role === 'dosen' && $progress->kelasMataKuliah->dosen_id !== $user->dosen->id) {
            abort(403, 'Anda tidak memiliki akses untuk memvalidasi progress ini.');
        }

        if ($progress->hasBeenValidated()) {
            return redirect('/')->with('warning', 'Validasi untuk progress ini sudah pernah dilakukan.');
        }

        $request->validate([
            'status' => 'required|in:Pending,Revisi,Diterima,Disetujui,Ditolak,Setuju,Tolak',
            'catatan_dosen' => 'nullable|string|max:1000',
        ]);

        $normalizedStatus = ProgressProject::normalizeStatus($request->status);
        $statusChanged = $progress->status !== $normalizedStatus;
        $oldStatus = $progress->status;

        $progress->update([
            'status' => $normalizedStatus,
            'catatan_dosen' => $request->catatan_dosen,
        ]);

        if ($statusChanged) {
            \Log::info("Progress #{$id} status changed from {$oldStatus} to {$request->status} by user " . Auth::id());
        }

        return redirect('/')->with('success', 'Status progress berhasil diperbarui!');
    }

    /**
     * UPDATE: Disimpan untuk kompatibilitas, tetapi alur utama memakai review
     */
    public function update(Request $request, $id)
    {
        return $this->review($request, $id);
    }

    /**
     * DELETE: Menghapus data progress beserta file fisik pendukungnya (soft delete)
     */
    public function destroy($id)
    {
        $progress = ProgressProject::findOrFail($id);
        
        // Cek authorization
        $user = Auth::user();
        if ($user->role === 'dosen' && $progress->kelasMataKuliah->dosen_id !== $user->dosen->id) {
            abort(403, 'Anda tidak memiliki akses untuk menghapus progress ini.');
        }

        // Hapus file fisik jika ada
        if ($progress->file_database && Storage::disk('public')->exists($progress->file_database)) {
            Storage::disk('public')->delete($progress->file_database);
        }
        if ($progress->file_laporan && Storage::disk('public')->exists($progress->file_laporan)) {
            Storage::disk('public')->delete($progress->file_laporan);
        }

        // Soft delete
        $progress->delete();

        return redirect('/')->with('success', 'Data progress project berhasil dihapus!');
    }
}