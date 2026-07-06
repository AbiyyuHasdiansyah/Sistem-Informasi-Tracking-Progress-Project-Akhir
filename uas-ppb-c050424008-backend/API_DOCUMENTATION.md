# API Documentation - Sistem Informasi Tracking Progress Project Akhir

## Overview

REST API backend untuk sistem tracking progress project akhir mahasiswa. API menggunakan **Laravel Sanctum** untuk autentikasi berbasis token dan mendukung role-based access control.

**Base URL:** `http://localhost:8000/api`  
**Authentication:** Bearer Token (Sanctum)  
**Response Format:** JSON

---

## Authentication

### 1. Register Mahasiswa
```http
POST /api/register
Content-Type: application/json

{
  "name": "John Doe",
  "email": "john@example.com",
  "password": "password123",
  "password_confirmation": "password123",
  "nim": "050424001",
  "no_hp": "081234567890",
  "kelas_id": 1,
  "role": "mahasiswa"
}
```

**Response (201 Created):**
```json
{
  "message": "Registration successful",
  "data": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "mahasiswa"
  },
  "token": "1|XXXXXXXXXXXXXXXXXXXXX"
}
```

---

### 2. Login
```http
POST /api/login
Content-Type: application/json

{
  "email": "john@example.com",
  "password": "password123"
}
```

**Response (200 OK):**
```json
{
  "message": "Login successful",
  "data": {
    "user": {
      "id": 1,
      "name": "John Doe",
      "email": "john@example.com",
      "role": "mahasiswa"
    },
    "mahasiswa": {
      "id": 1,
      "nim": "050424001",
      "kelas": {
        "id": 1,
        "nama_kelas": "IF-4A",
        "program_studi": {
          "id": 1,
          "nama_prodi": "Teknik Informatika"
        }
      }
    }
  },
  "token": "1|XXXXXXXXXXXXXXXXXXXXX"
}
```

---

### 3. Get Profile
```http
GET /api/profile
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "user": {
    "id": 1,
    "name": "John Doe",
    "email": "john@example.com",
    "role": "mahasiswa"
  },
  "mahasiswa": {
    "id": 1,
    "nim": "050424001",
    "kelas": {
      "nama_kelas": "IF-4A",
      "program_studi": {
        "nama_prodi": "Teknik Informatika"
      }
    }
  }
}
```

---

### 4. Logout
```http
POST /api/logout
Authorization: Bearer {token}
```

**Response (200 OK):**
```json
{
  "message": "Logout successful"
}
```

---

## Progress Tracking API

### 1. Get Progress History
Mendapatkan daftar history tracking progress mahasiswa yang login.

```http
GET /api/progress/riwayat
Authorization: Bearer {token}
```

**Query Parameters:**
- `per_page` (optional): Jumlah item per halaman (default: 15)
- `page` (optional): Halaman yang diinginkan (default: 1)
- `status` (optional): Filter berdasarkan status (Pending, Revisi, Diterima)

**Response (200 OK):**
```json
{
  "data": [
    {
      "id": 1,
      "mahasiswa_id": 1,
      "kelas_mata_kuliah_id": 1,
      "judul_project": "Aplikasi Manajemen Perpustakaan",
      "deskripsi_progress": "Membuat halaman login dan dashboard",
      "persentase_estimasi": 30,
      "file_database": "progress/databases/database_2024.sql",
      "file_laporan": "progress/laporans/laporan_week1.pdf",
      "link_gdrive": "https://drive.google.com/file/d/xxx",
      "link_github": "https://github.com/user/repo",
      "link_youtube": "https://youtube.com/watch?v=xxx",
      "status": "Pending",
      "catatan_dosen": null,
      "submitted_at": "2026-07-04T10:30:00Z",
      "created_at": "2026-07-04T10:30:00Z",
      "updated_at": "2026-07-04T10:30:00Z"
    }
  ],
  "links": {
    "first": "http://localhost:8000/api/progress/riwayat?page=1",
    "last": "http://localhost:8000/api/progress/riwayat?page=1",
    "prev": null,
    "next": null
  },
  "meta": {
    "current_page": 1,
    "from": 1,
    "last_page": 1,
    "per_page": 15,
    "to": 1,
    "total": 1
  }
}
```

---

### 2. Submit Progress Baru
Mengirimkan submisi progress project baru dari aplikasi mobile.

```http
POST /api/progress/submit
Authorization: Bearer {token}
Content-Type: multipart/form-data

{
  "kelas_mata_kuliah_id": 1,
  "judul_project": "Aplikasi Manajemen Perpustakaan",
  "deskripsi_progress": "Membuat halaman login dan dashboard",
  "persentase_estimasi": 30,
  "file_database": <binary_sql_file>,
  "file_laporan": <binary_pdf_file>,
  "link_gdrive": "https://drive.google.com/file/d/xxx",
  "link_github": "https://github.com/user/repo",
  "link_youtube": "https://youtube.com/watch?v=xxx"
}
```

**Validation Rules:**
- `kelas_mata_kuliah_id` (required): ID kelas mata kuliah yang sudah ada
- `judul_project` (required): String max 255 karakter
- `deskripsi_progress` (required): String max 1000 karakter
- `persentase_estimasi` (required): Integer 0-100
- `file_database` (optional): File .sql max 10MB
- `file_laporan` (optional): File .doc/.docx/.pdf max 25MB
- `link_gdrive` (optional): URL yang valid
- `link_github` (optional): URL yang valid
- `link_youtube` (optional): URL yang valid

**Response (201 Created):**
```json
{
  "message": "Progress project akhir berhasil dikirim!",
  "data": {
    "id": 1,
    "mahasiswa_id": 1,
    "kelas_mata_kuliah_id": 1,
    "judul_project": "Aplikasi Manajemen Perpustakaan",
    "deskripsi_progress": "Membuat halaman login dan dashboard",
    "persentase_estimasi": 30,
    "file_database": "progress/databases/database_2024.sql",
    "file_laporan": "progress/laporans/laporan_week1.pdf",
    "link_gdrive": "https://drive.google.com/file/d/xxx",
    "link_github": "https://github.com/user/repo",
    "link_youtube": "https://youtube.com/watch?v=xxx",
    "status": "Pending",
    "catatan_dosen": null,
    "submitted_at": "2026-07-04T10:30:00Z",
    "created_at": "2026-07-04T10:30:00Z",
    "updated_at": "2026-07-04T10:30:00Z"
  }
}
```

**Error Response (422 Unprocessable Entity):**
```json
{
  "message": "Validation failed",
  "errors": {
    "kelas_mata_kuliah_id": ["The kelas mata kuliah id field is required."],
    "file_database": ["The file database must be a file with a mimes extension: sql."],
    "file_laporan": ["The file laporan may not be greater than 25600 kilobytes."]
  }
}
```

---

## Web Controller API (Dashboard Dosen)

### 1. List All Progress (Dashboard)
```http
GET /
(Session-based, tidak perlu token)
```

**Features:**
- Pagination (15 items per halaman)
- Filter berdasarkan role dosen (hanya tampil progress dari kelas mereka)
- Eager load: mahasiswa, kelas_mata_kuliah, mata_kuliah
- Sorted by latest

**Response:** Blade template dengan variabel `$allProgress`

---

### 2. Create Progress Form
```http
GET /progress/create
(Session-based)
```

**Response:** Blade template form

---

### 3. Store Progress
```http
POST /progress
Content-Type: multipart/form-data

{
  "mahasiswa_id": 1,
  "kelas_mata_kuliah_id": 1,
  "judul_project": "...",
  "deskripsi_progress": "...",
  "persentase_estimasi": 30,
  "file_database": <file>,
  "file_laporan": <file>,
  "link_github": "...",
  "link_gdrive": "...",
  "link_youtube": "..."
}
```

---

### 4. Edit Progress Form
```http
GET /progress/{id}/edit
(Session-based)
```

**Response:** Blade template form dengan existing data

---

### 5. Update Progress
```http
PUT /progress/{id}
Content-Type: application/x-www-form-urlencoded

{
  "judul_project": "...",
  "deskripsi_progress": "...",
  "persentase_estimasi": 50,
  "status": "Revisi",
  "catatan_dosen": "Silakan perbaiki bagian X",
  "link_github": "...",
  "link_gdrive": "...",
  "link_youtube": "..."
}
```

**Status Options:**
- `Pending` - Baru dikirim, belum direview
- `Revisi` - Perlu diperbaiki
- `Diterima` - Progress disetujui

**Response:** Redirect dengan flash message

---

### 6. Delete Progress
```http
DELETE /progress/{id}
(Session-based)
```

**Features:**
- Soft delete (data tidak benar-benar dihapus)
- Fisik file akan dihapus dari storage
- Activity log akan dicatat

**Response:** Redirect dengan flash message

---

## Status Codes

| Code | Meaning |
|------|---------|
| 200 | OK - Request berhasil |
| 201 | Created - Resource berhasil dibuat |
| 400 | Bad Request - Request tidak valid |
| 401 | Unauthorized - Token tidak valid atau expired |
| 403 | Forbidden - User tidak memiliki akses |
| 404 | Not Found - Resource tidak ditemukan |
| 422 | Unprocessable Entity - Validation error |
| 500 | Internal Server Error - Server error |

---

## Error Handling

Semua error response mengikuti format berikut:

```json
{
  "message": "Error description",
  "errors": {
    "field_name": ["Error message 1", "Error message 2"]
  }
}
```

---

## File Upload

### Allowed MIME Types
- **Database:** `.sql` (max 10MB)
- **Laporan:** `.doc`, `.docx`, `.pdf` (max 25MB)

### Storage Path
- Database: `storage/app/public/progress/databases/`
- Laporan: `storage/app/public/progress/laporans/`

### Access
```
http://localhost:8000/storage/progress/databases/filename.sql
http://localhost:8000/storage/progress/laporans/filename.pdf
```

---

## Activity Logging

Setiap perubahan pada progress project akan dicatat di `activity_logs` table:

- `created` - Progress dibuat
- `updated` - Progress diupdate
- `status_changed` - Status progress berubah
- `deleted` - Progress dihapus

**Query Activity Log:**
```php
$logs = ActivityLog::forModel('App\Models\ProgressProject', $progressId)
    ->orderBy('created_at', 'desc')
    ->get();
```

---

## Relationships

### ProgressProject
```
- mahasiswa: BelongsTo(Mahasiswa)
- kelasMataKuliah: BelongsTo(KelasMatKuliah)
  - kelas: BelongsTo(Kelas)
  - mataKuliah: BelongsTo(MatKuliah)
  - dosen: BelongsTo(Dosen)
```

### KelasMatKuliah
```
- kelas: BelongsTo(Kelas)
- mataKuliah: BelongsTo(MatKuliah)
- dosen: BelongsTo(Dosen)
- progressProjects: HasMany(ProgressProject)
```

### Mahasiswa
```
- user: BelongsTo(User)
- kelas: BelongsTo(Kelas)
- progressProjects: HasMany(ProgressProject)
- kelasMataKulahs: HasManyThrough(KelasMatKuliah)
```

---

## Testing with cURL

### Register
```bash
curl -X POST http://localhost:8000/api/register \
  -H "Content-Type: application/json" \
  -d '{
    "name": "John Doe",
    "email": "john@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "nim": "050424001",
    "no_hp": "081234567890",
    "kelas_id": 1,
    "role": "mahasiswa"
  }'
```

### Login
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{
    "email": "john@example.com",
    "password": "password123"
  }'
```

### Get Progress History
```bash
curl -X GET http://localhost:8000/api/progress/riwayat \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Submit Progress
```bash
curl -X POST http://localhost:8000/api/progress/submit \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "kelas_mata_kuliah_id=1" \
  -F "judul_project=Aplikasi Mobile" \
  -F "deskripsi_progress=Week 1 progress" \
  -F "persentase_estimasi=30" \
  -F "file_database=@/path/to/database.sql" \
  -F "file_laporan=@/path/to/report.pdf" \
  -F "link_github=https://github.com/user/repo"
```

---

## Best Practices

1. **Token Management**
   - Simpan token dengan aman (preferably di secure storage)
   - Refresh token jika expired
   - Logout untuk invalidate token

2. **Error Handling**
   - Selalu check response status code
   - Parse error messages untuk user feedback
   - Implement retry logic untuk network errors

3. **File Uploads**
   - Validasi file di client sebelum upload
   - Tampilkan progress bar untuk file besar
   - Implement resumable uploads untuk file >25MB

4. **Performance**
   - Gunakan pagination untuk list endpoints
   - Cache frequently accessed data
   - Minimize file upload size dengan compression

5. **Security**
   - Selalu gunakan HTTPS di production
   - Validasi input di client dan server
   - Implement rate limiting
   - Use CORS untuk control access

---

## Changelog

### Version 1.0.0 (2026-07-04)
- ✅ Initial API implementation
- ✅ Authentication dengan Sanctum
- ✅ Progress CRUD operations
- ✅ File upload untuk database dan laporan
- ✅ Activity logging untuk audit trail
- ✅ Soft deletes untuk data integrity
- ✅ Pagination support
- ✅ MIME type validation
- ✅ Role-based access control

---

## Support

Untuk pertanyaan atau issues, silakan hubungi development team.

**Last Updated:** 2026-07-04
