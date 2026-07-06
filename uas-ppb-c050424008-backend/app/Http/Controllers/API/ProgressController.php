<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\ProgressProject;
use App\Models\Mahasiswa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

class ProgressController extends Controller
{
    // Melihat daftar histori tracking progress mahasiswa yang sedang login
    public function index(Request $request)
    {
        $user = $request->user();
        $role = strtolower($user->role ?? '');

        $query = ProgressProject::query();

        if ($role === 'mahasiswa') {
            $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();

            if (!$mahasiswa) {
                return response()->json(['message' => 'Data mahasiswa tidak ditemukan'], 404);
            }

            $query->where('mahasiswa_id', $mahasiswa->id);
        } elseif ($role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen) {
                return response()->json(['message' => 'Data dosen tidak ditemukan'], 404);
            }

            $query->whereHas('kelasMataKuliah', function ($q) use ($dosen) {
                $q->where('dosen_id', $dosen->id);
            });
        } else {
            $query->whereNotNull('id');
        }

        $progress = $query
            ->with(['mahasiswa.user', 'kelasMataKuliah.mataKuliah'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($item) use ($role, $user) {
                $isOwner = $role === 'mahasiswa' && $item->mahasiswa && $item->mahasiswa->user_id === $user->id;
                $item->can_edit = $isOwner;
                $item->can_delete = $isOwner;
                $item->can_validate = $role === 'dosen';
                $item->validation_status = $item->status;
                $item->validation_note = $item->catatan_dosen;
                return $item;
            });

        return response()->json(['data' => $progress], 200);
    }

    // Mengirimkan submisi progress project baru dari Flutter
    public function store(Request $request)
    {
        $mahasiswa = Mahasiswa::where('user_id', $request->user()->id)->first();

        $validator = Validator::make($request->all(), [
            'kelas_mata_kuliah_id' => 'required|exists:kelas_mata_kuliah,id',
            'judul_project' => 'required|string|max:255',
            'deskripsi_progress' => 'required|string|max:1000',
            'persentase_estimasi' => 'nullable|integer|between:0,100',
            'file_database' => 'nullable|file|mimes:sql|max:10240', // Max 10MB .sql
            'file_laporan' => 'nullable|file|mimes:doc,docx,pdf|max:25600', // Max 25MB Doc/PDF
            'link_gdrive' => 'nullable|url',
            'link_github' => 'nullable|url',
            'link_youtube' => 'nullable|url',
        ], [
            'file_database.mimes' => 'File database harus berformat .sql',
            'file_database.max' => 'File database maksimal 10MB',
            'file_laporan.mimes' => 'File laporan harus berformat .doc, .docx, atau .pdf',
            'file_laporan.max' => 'File laporan maksimal 25MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Handle upload file .sql
        $dbPath = null;
        if ($request->hasFile('file_database')) {
            $dbPath = $request->file('file_database')->store('progress/databases', 'public');
        }

        // Handle upload dokumen laporan
        $laporanPath = null;
        if ($request->hasFile('file_laporan')) {
            $laporanPath = $request->file('file_laporan')->store('progress/laporans', 'public');
        }

        $progress = ProgressProject::create([
            'mahasiswa_id' => $mahasiswa->id,
            'kelas_mata_kuliah_id' => $request->kelas_mata_kuliah_id,
            'judul_project' => $request->judul_project,
            'deskripsi_progress' => $request->deskripsi_progress,
            'persentase_estimasi' => $request->input('persentase_estimasi', 0),
            'file_database' => $dbPath,
            'file_laporan' => $laporanPath,
            'link_gdrive' => $request->link_gdrive,
            'link_github' => $request->link_github,
            'link_youtube' => $request->link_youtube,
            'status' => 'Pending',
            'submitted_at' => now()
        ]);

        return response()->json([
            'message' => 'Progress project akhir berhasil dikirim!',
            'data' => $progress
        ], 201);
    }

    public function file(Request $request, $id, $type)
    {
        $user = $request->user();
        $role = strtolower($user->role ?? '');

        $progress = ProgressProject::findOrFail($id);

        if ($role === 'mahasiswa') {
            $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
            if (!$mahasiswa || $progress->mahasiswa_id !== $mahasiswa->id) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke file ini'], 403);
            }
        } elseif ($role === 'dosen') {
            $dosen = $user->dosen;
            if (!$dosen || !$progress->kelasMataKuliah || $progress->kelasMataKuliah->dosen_id !== $dosen->id) {
                return response()->json(['message' => 'Anda tidak memiliki akses ke file ini'], 403);
            }
        } else {
            return response()->json(['message' => 'Akses tidak diizinkan'], 403);
        }

        $allowedTypes = ['file_laporan', 'file_database'];
        if (!in_array($type, $allowedTypes, true)) {
            return response()->json(['message' => 'Jenis file tidak valid'], 400);
        }

        $filePath = $progress->{$type};
        if (!$filePath || !Storage::disk('public')->exists($filePath)) {
            return response()->json(['message' => 'File tidak ditemukan'], 404);
        }

        $download = $request->boolean('download', false);
        $fullPath = Storage::disk('public')->path($filePath);
        $filename = basename($filePath);

        if ($download) {
            return response()->download($fullPath, $filename);
        }

        return response()->file($fullPath, [
            'Content-Disposition' => 'inline; filename="' . $filename . '"',
        ]);
    }

    public function update(Request $request, $id)
    {
        $user = $request->user();

        if (strtolower($user->role ?? '') !== 'mahasiswa') {
            return response()->json(['message' => 'Hanya mahasiswa yang dapat mengubah progress sendiri'], 403);
        }

        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan'], 404);
        }

        $progress = ProgressProject::where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->firstOrFail();

        $validator = Validator::make($request->all(), [
            'judul_project' => 'sometimes|required|string|max:255',
            'deskripsi_progress' => 'sometimes|required|string|max:1000',
            'persentase_estimasi' => 'sometimes|required|integer|between:0,100',
            'file_laporan' => 'nullable|file|mimes:doc,docx,pdf|max:25600',
            'link_gdrive' => 'nullable|url',
            'link_github' => 'nullable|url',
            'link_youtube' => 'nullable|url',
        ], [
            'file_laporan.mimes' => 'File laporan harus berformat .doc, .docx, atau .pdf',
            'file_laporan.max' => 'File laporan maksimal 25MB',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $progress->fill($request->only([
            'judul_project',
            'deskripsi_progress',
            'persentase_estimasi',
            'link_gdrive',
            'link_github',
            'link_youtube',
        ]));

        if ($request->hasFile('file_laporan')) {
            if ($progress->file_laporan && Storage::disk('public')->exists($progress->file_laporan)) {
                Storage::disk('public')->delete($progress->file_laporan);
            }

            $progress->file_laporan = $request->file('file_laporan')->store('progress/laporans', 'public');
        }

        $progress->save();

        return response()->json([
            'message' => 'Progress berhasil diperbarui',
            'data' => $progress,
        ], 200);
    }

    public function destroy(Request $request, $id)
    {
        $user = $request->user();

        if (strtolower($user->role ?? '') !== 'mahasiswa') {
            return response()->json(['message' => 'Hanya mahasiswa yang dapat menghapus progress sendiri'], 403);
        }

        $mahasiswa = Mahasiswa::where('user_id', $user->id)->first();
        if (!$mahasiswa) {
            return response()->json(['message' => 'Data mahasiswa tidak ditemukan'], 404);
        }

        $progress = ProgressProject::where('id', $id)
            ->where('mahasiswa_id', $mahasiswa->id)
            ->firstOrFail();

        $progress->delete();

        return response()->json([
            'message' => 'Progress berhasil dihapus',
        ], 200);
    }

    public function review(Request $request, $id)
    {
        $user = $request->user();

        if (strtolower($user->role ?? '') !== 'dosen') {
            return response()->json(['message' => 'Hanya dosen yang dapat memvalidasi progress'], 403);
        }

        $progress = ProgressProject::with('kelasMataKuliah')->findOrFail($id);
        $dosen = $user->dosen;

        if (!$dosen || $progress->kelasMataKuliah->dosen_id !== $dosen->id) {
            return response()->json(['message' => 'Anda tidak memiliki akses untuk memvalidasi progress ini'], 403);
        }

        if ($progress->hasBeenValidated()) {
            return response()->json(['message' => 'Validasi untuk progress ini sudah pernah dilakukan'], 409);
        }

        $validator = Validator::make($request->all(), [
            'status' => 'required|in:Pending,Revisi,Diterima,Disetujui,Ditolak,Setuju,Tolak',
            'catatan_dosen' => 'nullable|string|max:1000',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $normalizedStatus = ProgressProject::normalizeStatus($request->status);

        $progress->update([
            'status' => $normalizedStatus,
            'catatan_dosen' => $request->catatan_dosen,
        ]);

        return response()->json([
            'message' => 'Status validasi berhasil diperbarui',
            'data' => $progress,
        ], 200);
    }
}