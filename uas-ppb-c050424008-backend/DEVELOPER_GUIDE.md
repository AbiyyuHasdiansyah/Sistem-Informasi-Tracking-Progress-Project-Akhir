# 🚀 QUICK START GUIDE - Developer Reference

Panduan cepat untuk memulai mengembangkan Sistem Informasi Tracking Progress Project Akhir.

---

## 📦 Installation & Setup

### 1. Clone Repository
```bash
git clone <repository-url>
cd uas-ppb-c050424008-backend
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Setup
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
```bash
php artisan migrate --seed
```

### 5. Run Server
```bash
php artisan serve
npm run dev  # Vite dev server
```

Server akan berjalan di: `http://localhost:8000`

---

## 📁 Project Structure

```
app/
├── Models/              # Eloquent models
│   ├── ProgressProject.php        ← Core model
│   ├── KelasMatKuliah.php
│   ├── Mahasiswa.php
│   ├── Dosen.php
│   ├── MatKuliah.php
│   ├── Kelas.php
│   └── ActivityLog.php
├── Http/Controllers/
│   ├── API/
│   │   ├── AuthController.php
│   │   └── ProgressController.php
│   └── Web/
│       └── WebProgressController.php
└── Traits/
    └── LogsActivity.php

database/
├── migrations/          # Database schema
├── seeders/            # Seeder data
└── factories/          # Model factories

resources/
├── views/              # Blade templates
├── js/                 # Frontend JS
└── css/                # Styling

routes/
├── api.php             # REST API routes
└── web.php             # Web routes
```

---

## 🔌 API Endpoints Reference

### Authentication
```
POST   /api/register          Register user
POST   /api/login             Login user
GET    /api/profile           Get user profile
POST   /api/logout            Logout user
```

### Progress Tracking
```
GET    /api/progress/riwayat  Get progress history
POST   /api/progress/submit   Submit new progress
```

### Web Dashboard
```
GET    /                      Dashboard list
GET    /progress/create       Create form
POST   /progress              Store progress
GET    /progress/{id}/edit    Edit form
PUT    /progress/{id}         Update progress
DELETE /progress/{id}         Delete progress
```

---

## 📊 Database Schema Quick Reference

### Main Tables

**progress_projects**
```
id, mahasiswa_id, kelas_mata_kuliah_id,
judul_project, deskripsi_progress,
persentase_estimasi, file_database, file_laporan,
link_gdrive, link_github, link_youtube,
status (Pending|Revisi|Diterima),
catatan_dosen, submitted_at, deleted_at,
created_at, updated_at
```

**kelas_mata_kuliah**
```
id, kelas_id, mata_kuliah_id, dosen_id,
created_at, updated_at
```

**activity_logs**
```
id, user_id, action, model, model_id,
changes (JSON), old_value, new_value,
ip_address, user_agent, created_at, updated_at
```

---

## 💾 Using Models

### Eager Loading (Mencegah N+1 Query)
```php
// ✅ GOOD - Eager loading
$progress = ProgressProject::with(['mahasiswa', 'kelasMataKuliah.mataKuliah'])->get();

// ❌ BAD - N+1 query problem
$progress = ProgressProject::all();
```

### Querying Progress
```php
// Get progress dengan pagination
$progress = ProgressProject::with('mahasiswa')
    ->latest()
    ->paginate(15);

// Filter by status
$pending = ProgressProject::where('status', 'Pending')->get();

// Get deleted items (soft deletes)
$deleted = ProgressProject::onlyTrashed()->get();

// Restore soft deleted item
$progress->restore();
```

### Activity Logging
```php
// Get all logs for a progress
$logs = ActivityLog::forModel('App\Models\ProgressProject', $progressId)
    ->orderBy('created_at', 'desc')
    ->get();

// Get status changes only
$statusLogs = ActivityLog::forModel('App\Models\ProgressProject', $progressId)
    ->byAction('status_changed')
    ->get();
```

---

## 🔐 Authorization & Access Control

### Check Role
```php
// In controller
if (Auth::user()->role === 'dosen') {
    // Dosen-specific logic
}

if (Auth::user()->role === 'admin') {
    // Admin-specific logic
}
```

### File Access Control
```php
// Only dosen pembimbing can edit/delete
$dosen = Auth::user()->dosen;
if ($progress->kelasMataKuliah->dosen_id !== $dosen->id) {
    abort(403, 'Unauthorized');
}
```

---

## 📝 File Upload Handling

### Validation Rules
```php
$request->validate([
    'file_database' => 'nullable|file|mimes:sql|max:10240',    // 10MB SQL
    'file_laporan' => 'nullable|file|mimes:doc,docx,pdf|max:25600', // 25MB PDF/Doc
]);
```

### Upload Storage Paths
```
Database:  storage/app/public/progress/databases/
Laporan:   storage/app/public/progress/laporans/
Public:    http://localhost:8000/storage/progress/...
```

### Delete Files Safely
```php
if ($progress->file_database && Storage::disk('public')->exists($progress->file_database)) {
    Storage::disk('public')->delete($progress->file_database);
}
```

---

## 🧪 Testing with cURL

### Get Token
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Content-Type: application/json" \
  -d '{"email": "user@example.com", "password": "password"}'
```

### Use Token in Request
```bash
curl -X GET http://localhost:8000/api/progress/riwayat \
  -H "Authorization: Bearer YOUR_TOKEN_HERE"
```

### Upload File
```bash
curl -X POST http://localhost:8000/api/progress/submit \
  -H "Authorization: Bearer YOUR_TOKEN_HERE" \
  -F "kelas_mata_kuliah_id=1" \
  -F "judul_project=My Project" \
  -F "deskripsi_progress=Week 1" \
  -F "persentase_estimasi=30" \
  -F "file_database=@/path/to/file.sql"
```

---

## 🐛 Common Issues & Solutions

### Issue: Class not found
**Solution:** Make sure model class name uses PascalCase and import:
```php
use App\Models\ProgressProject;
```

### Issue: MIME type validation error
**Solution:** Ensure file extensions match:
- Database: `.sql` only
- Laporan: `.doc`, `.docx`, `.pdf` only

### Issue: N+1 query problem
**Solution:** Use eager loading:
```php
ProgressProject::with('mahasiswa', 'kelasMataKuliah')->get();
```

### Issue: File not found on delete
**Solution:** Check file existence first:
```php
if (Storage::disk('public')->exists($path)) {
    Storage::disk('public')->delete($path);
}
```

### Issue: Soft deleted records showing in query
**Solution:** Exclude soft deleted records:
```php
// Exclude soft deleted (default)
$progress = ProgressProject::where('status', 'Pending')->get();

// Include soft deleted
$progress = ProgressProject::withTrashed()->get();

// Only soft deleted
$progress = ProgressProject::onlyTrashed()->get();
```

---

## 📚 Key Technologies

| Technology | Version | Purpose |
|-----------|---------|---------|
| Laravel | 12.0 | Backend framework |
| Laravel Sanctum | 4.0 | API authentication |
| PHP | 8.2+ | Server language |
| MySQL/SQLite | Latest | Database |
| Blade | Latest | Templating |
| Tailwind CSS | 4.0 | Styling |
| Vite | 7.0.7 | Build tool |

---

## 📋 Code Standards

### Naming Conventions
```php
// Classes - PascalCase
class ProgressProject { }

// Methods - camelCase
public function getProgressData() { }

// Variables - camelCase
$progressId = 1;

// Constants - UPPER_SNAKE_CASE
const MAX_FILE_SIZE = 10240;

// Database tables - snake_case (plural)
progress_projects

// Database columns - snake_case
kelas_mata_kuliah_id
```

### Code Style
```php
// Use type hints
public function store(Request $request): RedirectResponse

// Use proper indentation (4 spaces)
public function method()
{
    if ($condition) {
        // Code here
    }
}

// Use meaningful variable names
$mahasiswaProjects = ...  // Good
$mp = ...                  // Bad
```

---

## 🔍 Useful Artisan Commands

```bash
# Create new model with migration
php artisan make:model ProgressProject -m

# Create controller
php artisan make:controller ProgressController

# Run migrations
php artisan migrate

# Create migration
php artisan make:migration add_column_to_table

# Seed database
php artisan db:seed

# Clear cache
php artisan cache:clear

# Run tests
php artisan test

# Tinker REPL
php artisan tinker

# Check routes
php artisan route:list
```

---

## 📖 Documentation Files

- **API_DOCUMENTATION.md** - Complete API reference
- **IMPROVEMENTS.md** - All improvements made
- **README.md** - Project overview
- **Database Diagram** - (Create ER diagram separately)

---

## 🚀 Deployment Checklist

Before deploying to production:

- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear cache: `php artisan cache:clear`
- [ ] Rebuild config: `php artisan config:cache`
- [ ] Optimize autoloader: `composer install --optimize-autoloader`
- [ ] Set environment to production in `.env`
- [ ] Enable HTTPS
- [ ] Set up backup strategy
- [ ] Configure error logging
- [ ] Test all endpoints
- [ ] Test file uploads
- [ ] Verify activity logs working

---

## 🤝 Contributing

When adding new features:

1. Create feature branch: `git checkout -b feature/feature-name`
2. Follow code standards (PSR-12)
3. Add activity logging if modifying models
4. Update API documentation
5. Test thoroughly
6. Create pull request

---

## 📞 Support Resources

- **Framework Docs:** https://laravel.com/docs
- **Sanctum Docs:** https://laravel.com/docs/sanctum
- **Database Docs:** https://laravel.com/docs/eloquent
- **API Standards:** https://jsonapi.org

---

## 📅 Version History

| Version | Date | Status | Notes |
|---------|------|--------|-------|
| 1.0.1 | 2026-07-04 | Production | All improvements implemented |
| 1.0.0 | 2026-07-03 | Initial | MVP released |

---

**Last Updated:** 2026-07-04  
**Maintained By:** Development Team  
**Status:** ✅ Production Ready
