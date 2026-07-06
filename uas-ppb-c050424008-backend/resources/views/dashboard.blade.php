<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Tracking Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body style="background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%); min-height: 100vh;">
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm mb-4" style="background: linear-gradient(90deg, #4f46e5 0%, #2563eb 100%);">
        <div class="container">
            <div>
                <span class="navbar-brand mb-0 h4 fw-bold">Sistem Informasi Tracking Progress</span>
                <div class="small text-white-50">Dashboard Dosen & Validasi Project Akhir</div>
            </div>
            <form action="{{ route('logout') }}" method="POST" class="mb-0">
                @csrf
                <button type="submit" class="btn btn-light btn-sm fw-semibold">
                    <i class="bi bi-box-arrow-right me-1"></i> Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="alert alert-success rounded-4 shadow-sm border-0">
                <i class="bi bi-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('warning'))
            <div class="alert alert-warning rounded-4 shadow-sm border-0">
                <i class="bi bi-exclamation-triangle me-2"></i>{{ session('warning') }}
            </div>
        @endif

        <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="card-body p-4">
                <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4">
                    <div>
                        <h3 class="fw-bold mb-1 text-dark">
                            <i class="bi bi-clipboard-check me-2 text-primary"></i>Validasi Progress Mahasiswa
                        </h3>
                        <p class="text-muted mb-0">Pantau progress, cek laporan, dan lakukan validasi dengan lebih cepat.</p>
                    </div>
                    <div class="badge bg-primary-subtle text-primary px-3 py-2 rounded-pill mt-3 mt-md-0">
                        <i class="bi bi-people me-1"></i> {{ $allProgress->count() }} data progress
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th>Mahasiswa</th>
                                <th>Mata Kuliah</th>
                                <th>Judul Project</th>
                                <th>Status</th>
                                <th>Berkas</th>
                                <th class="text-center">Validasi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($allProgress as $progress)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar rounded-circle d-flex align-items-center justify-content-center text-white" style="width: 42px; height: 42px; background: linear-gradient(135deg, #4f46e5, #3b82f6);">
                                                <i class="bi bi-person-fill"></i>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">{{ $progress->mahasiswa->user->name ?? 'N/A' }}</div>
                                                <small class="text-muted">{{ $progress->mahasiswa->nim ?? '-' }}</small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $progress->kelasMataKuliah->mataKuliah->nama_mk ?? 'N/A' }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $progress->judul_project }}</div>
                                    </td>
                                    <td>
                                        @php
                                            $statusClass = match($progress->status) {
                                                'Disetujui', 'Diterima', 'Setuju' => 'bg-success',
                                                'Ditolak', 'Revisi', 'Tolak' => 'bg-warning text-dark',
                                                default => 'bg-info text-dark',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }} rounded-pill px-3 py-2">{{ $progress->status }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex flex-column gap-2">
                                            @if($progress->file_laporan)
                                                <div class="d-flex align-items-center gap-2">
                                                    <a href="{{ route('progress.preview', ['id' => $progress->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill" title="Lihat laporan">
                                                        <i class="bi bi-eye"></i>
                                                    </a>
                                                    <span class="small text-muted">Laporan</span>
                                                </div>
                                            @else
                                                <span class="small text-muted">Belum ada laporan</span>
                                            @endif
                                        </div>
                                        @if($progress->link_gdrive)
                                            <div class="mt-2">
                                                <a href="{{ $progress->link_gdrive }}" target="_blank" rel="noopener" class="btn btn-outline-info btn-sm rounded-pill" title="Buka Google Drive">
                                                    <i class="bi bi-cloud-arrow-up me-1"></i> Drive
                                                </a>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($progress->hasBeenValidated())
                                            <div class="badge bg-success-subtle text-success rounded-pill px-3 py-2">
                                                <i class="bi bi-check2-circle me-1"></i> Sudah divalidasi
                                            </div>
                                        @else
                                            <form action="{{ route('progress.review', ['id' => $progress->id]) }}" method="POST" class="d-flex flex-column gap-2" style="min-width: 260px;">
                                                @csrf
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light"><i class="bi bi-award"></i></span>
                                                    <select name="status" class="form-select form-select-sm rounded-end-3">
                                                        <option value="Pending" {{ $progress->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="Disetujui" {{ in_array($progress->status, ['Disetujui', 'Diterima']) ? 'selected' : '' }}>Disetujui</option>
                                                        <option value="Ditolak" {{ in_array($progress->status, ['Ditolak', 'Revisi']) ? 'selected' : '' }}>Ditolak</option>
                                                    </select>
                                                </div>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-light"><i class="bi bi-chat-left-text"></i></span>
                                                    <textarea name="catatan_dosen" class="form-control rounded-end-3" rows="2" placeholder="Catatan dosen">{{ old('catatan_dosen', $progress->catatan_dosen) }}</textarea>
                                                </div>
                                                <button type="submit" class="btn btn-primary btn-sm rounded-pill">
                                                    <i class="bi bi-save me-1"></i> Simpan Validasi
                                                </button>
                                            </form>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        <i class="bi bi-inbox-fill fs-3 d-block mb-2"></i>
                                        Belum ada data progress dari aplikasi mobile.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>