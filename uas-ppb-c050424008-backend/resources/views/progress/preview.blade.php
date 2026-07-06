<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Laporan Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body style="background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%); min-height: 100vh;">
    <div class="container py-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
            <div>
                <h3 class="fw-bold mb-1 text-dark">
                    <i class="bi bi-file-earmark-text me-2 text-primary"></i>Preview Laporan Progress
                </h3>
                <p class="text-muted mb-0">{{ $progress->judul_project }}</p>
            </div>
            <div class="d-flex gap-2 mt-3 mt-md-0">
                <a href="{{ url('/') }}" class="btn btn-outline-secondary rounded-pill">
                    <i class="bi bi-arrow-left me-1"></i> Kembali
                </a>
            </div>
        </div>

        <div class="card shadow-lg border-0 rounded-4 overflow-hidden">
            <div class="card-body p-3 p-md-4">
                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <small class="text-muted d-block">Mahasiswa</small>
                            <div class="fw-semibold">{{ $progress->mahasiswa->user->name ?? '-' }}</div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="p-3 rounded-3 bg-light">
                            <small class="text-muted d-block">Mata Kuliah</small>
                            <div class="fw-semibold">{{ $progress->kelasMataKuliah->mataKuliah->nama_mk ?? '-' }}</div>
                        </div>
                    </div>
                </div>

                @php
                    $filename = basename($progress->file_laporan);
                    $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    $isPreviewable = in_array($extension, ['pdf', 'png', 'jpg', 'jpeg']);
                @endphp

                @if($isPreviewable)
                    <div class="border rounded-4 overflow-hidden shadow-sm">
                        <iframe src="{{ $fileUrl }}" title="Preview Laporan" class="w-100" style="min-height: 80vh; border: 0;"></iframe>
                    </div>
                @else
                    <div class="border rounded-4 p-4 bg-light text-center">
                        <i class="bi bi-file-earmark-text fs-1 text-primary mb-3"></i>
                        <h5 class="fw-semibold mb-2">Download file</h5>
                        <p class="text-muted mb-3">File ini tidak dapat ditampilkan langsung di halaman ini. Anda dapat mengunduh file melalui tombol di bawah.</p>
                        <a href="{{ $fileUrl }}" class="btn btn-primary rounded-pill" target="_blank" rel="noopener">
                            <i class="bi bi-download me-1"></i> Download file
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</body>
</html>
