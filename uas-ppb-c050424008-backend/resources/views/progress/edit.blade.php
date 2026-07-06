<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Progress Project</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light py-5">
    <div class="container" style="max-width: 600px;">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <h4 class="mb-4">Validasi Progress Project</h4>
                @if($progress->hasBeenValidated())
                    <div class="alert alert-success">Validasi untuk progress ini sudah pernah dilakukan.</div>
                @else
                    <form action="{{ route('progress.review', ['id' => $progress->id]) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label">Judul Project Akhir</label>
                            <input type="text" class="form-control" value="{{ $progress->judul_project }}" disabled>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Status Validasi</label>
                            <select name="status" class="form-select">
                                <option value="Pending" {{ $progress->status == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Disetujui" {{ in_array($progress->status, ['Disetujui', 'Diterima']) ? 'selected' : '' }}>Disetujui</option>
                                <option value="Ditolak" {{ in_array($progress->status, ['Ditolak', 'Revisi']) ? 'selected' : '' }}>Ditolak</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Catatan Dosen</label>
                            <textarea name="catatan_dosen" class="form-control" rows="3">{{ $progress->catatan_dosen }}</textarea>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">Simpan Validasi</button>
                            <a href="{{ url('/') }}" class="btn btn-secondary">Batal</a>
                        </div>
                    </form>
                @endif
            </div>
        </div>
    </div>
</body>
</html>