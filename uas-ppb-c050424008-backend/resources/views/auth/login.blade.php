<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistem Informasi Tracking Progress</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css">
</head>
<body style="background: linear-gradient(135deg, #f8fbff 0%, #eef4ff 100%); min-height: 100vh;">
    <div class="container py-5">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-10">
                <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
                    <div class="row g-0">
                        <div class="col-md-6 p-5 text-white d-flex flex-column justify-content-center" style="background: linear-gradient(135deg, #4f46e5 0%, #2563eb 100%);">
                            <div class="mb-4 d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 56px; height: 56px; background: rgba(255,255,255,0.18);">
                                <i class="bi bi-shield-lock-fill fs-4"></i>
                            </div>
                            <h2 class="fw-bold mb-3">Selamat datang kembali</h2>
                            <p class="mb-0 opacity-75">Masuk ke dashboard dosen untuk memantau dan memvalidasi progress project akhir mahasiswa.</p>
                        </div>
                        <div class="col-md-6 bg-white p-5 d-flex flex-column justify-content-center">
                            <div class="text-center mb-4">
                                <h3 class="fw-bold text-dark">Login Web Sistem</h3>
                                <p class="text-muted mb-0">Silakan masuk dengan akun Anda.</p>
                            </div>

                            @if ($errors->any())
                                <div class="alert alert-danger rounded-3 border-0">
                                    <i class="bi bi-exclamation-triangle me-2"></i>{{ $errors->first() }}
                                </div>
                            @endif

                            <form action="{{ url('/login') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label for="email" class="form-label fw-semibold">Email Address</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-envelope"></i></span>
                                        <input type="email" name="email" class="form-control" id="email" value="{{ old('email') }}" placeholder="you@example.com" required>
                                    </div>
                                </div>
                                <div class="mb-4">
                                    <label for="password" class="form-label fw-semibold">Password</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                                        <input type="password" name="password" class="form-control" id="password" placeholder="Masukkan password" required>
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary w-100 rounded-pill fw-semibold py-2">
                                    <i class="bi bi-box-arrow-in-right me-2"></i>Masuk Ke Sistem
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>