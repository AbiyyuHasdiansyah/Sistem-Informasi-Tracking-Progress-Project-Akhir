# PRD (Product Requirement Document)
# Sistem Informasi Tracking Progress Project Akhir

Dokumen ini disusun berdasarkan analisis terhadap struktur proyek yang terdapat pada folder aplikasi mobile Flutter dan backend Laravel.

## 1. Latar Belakang
Proses pelacakan progress project akhir mahasiswa selama ini cenderung dilakukan secara manual dan tersebar di berbagai media, seperti chat, file dokumen, dan catatan pribadi. Kondisi ini membuat perkembangan project sulit dipantau, dokumentasi menjadi tidak konsisten, dan proses review dari dosen menjadi kurang terstruktur.

Berdasarkan implementasi yang saat ini ada, proyek ini dikembangkan sebagai sistem berbasis mobile dan API yang memungkinkan mahasiswa mengirimkan progress project secara teratur, sementara dosen dapat melihat dan menilai progress tersebut melalui alur yang terintegrasi.

## 2. Tujuan
Tujuan utama dari sistem ini adalah:
- Memudahkan mahasiswa dalam mengirimkan progress project akhir melalui aplikasi mobile.
- Menyediakan ruang penyimpanan progress yang terorganisir dan dapat diakses kembali.
- Memungkinkan dosen untuk melihat hasil submission mahasiswa dan memberikan review atau catatan.
- Meningkatkan transparansi dan efisiensi proses bimbingan project akhir.
- Menjadi dasar pengembangan sistem tracking progress yang dapat terus dikembangkan ke fitur yang lebih lengkap.

## 3. Ruang Lingkup
### In Scope
- Autentikasi pengguna melalui register, login, dan logout.
- Akses profil pengguna setelah login.
- Tampilan dashboard untuk mahasiswa yang menampilkan profil dan riwayat progress.
- Fitur submit progress project dari aplikasi mobile.
- Fitur edit dan hapus progress milik mahasiswa.
- Upload file laporan pendukung berupa dokumen PDF, DOC, atau DOCX.
- Penyimpanan dan pemanggilan file terkait progress.
- Review progress oleh dosen melalui perubahan status dan catatan reviewer.
- API backend yang mendukung integrasi dengan aplikasi mobile.

### Out of Scope
- Chat real-time antar pengguna.
- Notifikasi push otomatis ke perangkat.
- Integrasi dengan sistem akademik kampus secara penuh.
- Dashboard admin yang lengkap dan terkelola secara visual.
- Fitur penilaian otomatis berbasis AI.
- Modul absensi, keuangan, atau manajemen administrasi kampus lainnya.

## 4. Pengguna & Peran
### 4.1 Mahasiswa
Peran mahasiswa adalah pengguna utama sistem.
- Melakukan registrasi dan login.
- Melihat profil pribadi dan data akademik terkait.
- Mengirimkan progress project akhir.
- Melihat riwayat progress yang pernah dikirim.
- Mengedit atau menghapus progress yang dimilikinya.

### 4.2 Dosen
Peran dosen adalah pengguna yang menilai progress mahasiswa.
- Melihat progress yang masuk dari mahasiswa yang berada dalam tanggung jawabnya.
- Memberikan review melalui status progress dan catatan dosen.
- Mengakses progress yang relevan dengan kelas atau mata kuliah yang dibimbing.

### 4.3 Admin
Peran admin berada pada level sistem secara umum.
- Mengelola data referensi dan pengguna secara administratif.
- Menjaga kelangsungan operasi sistem.
- Memastikan akses dan data berjalan sesuai aturan bisnis.

## 5. Alur Bisnis
Alur bisnis utama sistem yang terimplementasi saat ini adalah sebagai berikut:
1. Pengguna melakukan registrasi akun sebagai mahasiswa.
2. Pengguna login ke aplikasi mobile melalui API backend.
3. Setelah login, aplikasi menampilkan dashboard profil dan daftar riwayat progress.
4. Mahasiswa mengisi data progress project, termasuk judul, deskripsi, dan file laporan.
5. Sistem menyimpan progress dengan status awal Pending.
6. Dosen melakukan review terhadap progress yang masuk.
7. Status progress dapat diperbarui menjadi Revisi, Diterima, atau bentuk status yang setara.
8. Mahasiswa dapat melakukan edit atau hapus terhadap progress miliknya.
9. Semua data progress dapat diakses melalui API dan ditampilkan kembali pada aplikasi.

## 6. Kebutuhan Fungsional
Sistem harus menyediakan fitur-fitur berikut:
- Registrasi akun pengguna.
- Login dan logout pengguna dengan autentikasi berbasis token.
- Pengambilan profil pengguna beserta data mahasiswa terkait.
- Menampilkan daftar riwayat progress per pengguna.
- Submit progress project baru melalui aplikasi mobile.
- Upload file laporan dalam format yang diperbolehkan.
- Edit progress milik mahasiswa.
- Hapus progress milik mahasiswa.
- Lihat detail progress dan status validasi.
- Review progress oleh dosen dengan menyertakan catatan.
- Akses file laporan dan file database sesuai hak akses pengguna.
- Penyimpanan data progress yang terhubung ke mahasiswa dan kelas mata kuliah.

## 7. Kebutuhan Non-Fungsional
- Keamanan: sistem wajib menggunakan autentikasi token dan membatasi akses sesuai peran pengguna.
- Performa: API harus mampu merespons operasi dasar seperti login, melihat profil, dan submit progress secara cepat.
- Keandalan: data progress harus tersimpan secara aman dan konsisten.
- Ketersediaan: sistem harus dapat digunakan selama proses bimbingan project berlangsung.
- Kemudahan penggunaan: antarmuka mobile harus sederhana, jelas, dan mudah dipahami oleh mahasiswa.
- Skalabilitas: arsitektur backend harus mendukung penambahan fitur dan pengguna di masa depan.

## 8. Struktur Data Utama (Entitas)
Entitas utama yang terlihat pada proyek ini adalah:
- User: akun pengguna sistem, mencakup data login dan role.
- Mahasiswa: data mahasiswa yang terkait dengan user, NIM, nomor HP, dan kelas.
- Dosen: data pengguna yang bertindak sebagai reviewer atau pembimbing.
- ProgramStudi: data program studi yang terkait dengan kelas mahasiswa.
- Kelas: data kelas mahasiswa.
- MatKuliah: data mata kuliah.
- KelasMatKuliah: relasi antara kelas dan mata kuliah yang digunakan dalam tracking progress.
- ProgressProject: entitas utama untuk data progress project akhir mahasiswa.
- ProgressReviewHistory: catatan atau riwayat review progress.

## 9. Spesifikasi Teknis
### Frontend Mobile
- Flutter
- Dart
- Package utama: http, shared_preferences, file_picker
- Tampilan utama: login, register, dashboard home, submit progress

### Backend
- Laravel
- PHP
- REST API
- Laravel Sanctum untuk autentikasi token
- MySQL sebagai basis data utama
- Route API untuk auth, profile, progress, dan file download

### Pengembangan dan Pengujian
- Flutter test untuk aplikasi mobile
- PHPUnit untuk backend
- Git sebagai alat kontrol versi

## 10. Asumsi & Batasan
- Pengguna membutuhkan koneksi internet saat menggunakan aplikasi.
- Aplikasi ini fokus pada pencatatan dan review progress project akhir, bukan sistem akademik lengkap.
- Ukuran file upload dibatasi oleh konfigurasi backend.
- Fitur notifikasi real-time belum menjadi bagian utama pada versi saat ini.
- UI mobile saat ini lebih banyak difokuskan pada mahasiswa, sementara fitur dosen masih terbatas pada backend dan alur review.
- Integrasi dengan sistem kampus lain belum dilakukan.

## 11. Kriteria Keberhasilan
Kriteria keberhasilan sistem ini adalah:
- Mahasiswa mampu melakukan login, submit progress, dan melihat riwayat progress dengan lancar.
- Dosen mampu melihat progress dan memberikan review dengan status yang jelas.
- Data progress tersimpan dengan baik dan dapat diakses kembali melalui API.
- Proses bimbingan project akhir menjadi lebih terstruktur dibandingkan proses manual.
- Pengguna dapat menyelesaikan alur utama sistem tanpa mengalami error yang signifikan.
- Aplikasi mobile dan backend dapat berjalan terintegrasi dalam satu alur kerja yang konsisten.
