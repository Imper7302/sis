# SİS (Sistemli İdarəetmə Sistemi) - Layihə Sənədləşməsi

## 📋 Layihə Haqqında

**SİS** - İşçilərin həftəlik iş hesabatlarını idarə etmək üçün hazırlanmış peşəkar veb əsaslı idarəetmə sistemidir. Sistem MySQL və PHP texnologiyaları əsasında qurulub və modern CRUD (Create, Read, Update, Delete) əməliyyatları ilə işləyir.

### Əsas Xüsusiyyətlər

- ✅ İstifadəçi autentifikasiyası və rol əsaslı giriş nəzarəti
- ✅ İşçilərin idarə edilməsi (CRUD əməliyyatları)
- ✅ Həftəlik iş hesabatlarının yaradılması və izlənməsi
- ✅ Sektor və vəzifə idarəetməsi
- ✅ Qiymətləndirmə sistemi (Əla, Yaxşı, Kafi)
- ✅ Dashboard statistikaları və hesabatlar
- ✅ Log sisteminin avtomatik yaradılması
- ✅ Responsive dizayn

---

## 🗂️ Layihə Strukturu

```
sis/
├── api/                          # API endpointləri
│   ├── get_employee.php         # İşçi məlumatlarını almaq
│   └── get_work.php             # İş məlumatlarını almaq
│
├── assets/                       # Statik fayllar
│   ├── css/                     # Stil faylları
│   │   ├── header.css
│   │   ├── login.css
│   │   ├── navigation.css
│   │   ├── style.css
│   │   └── weekly_w_in.css
│   ├── images/                  # Şəkillər
│   │   └── logo.png
│   ├── js/                      # JavaScript faylları
│   │   ├── applications.js
│   │   └── main.js
│   └── uploads/                 # Yüklənmiş fayllar
│       └── applications/
│
├── config/                       # Konfiqurasiya faylları
│   ├── config.php               # Ümumi parametrlər
│   └── database.php             # Verilənlər bazası bağlantısı
│
├── crud/                         # CRUD modulları
│   ├── employees/               # İşçi idarəetməsi
│   │   ├── index.php
│   │   ├── create.php
│   │   └── delete.php
│   ├── sector-vezife/           # Sektor və vəzifə idarəetməsi
│   │   └── index.php
│   ├── users/                   # İstifadəçi idarəetməsi
│   │   ├── index.php
│   │   ├── create.php
│   │   ├── update.php
│   │   └── change_password.php
│   └── weekly_works/            # Həftəlik hesabatlar
│       ├── index.php
│       ├── api_create.php
│       ├── api_update.php
│       ├── api_delete.php
│       └── api_update_qiymet.php
│
├── includes/                     # Ümumi komponentlər
│   ├── auth.php                 # Autentifikasiya
│   ├── header.php               # Səhifə başlığı
│   ├── footer.php               # Səhifə altlığı
│   └── navigation.php           # Menyu naviqasiyası
│
├── logs/                         # Sistem logları
│   ├── log_2026_01_29.txt
│   └── log_2026_01_30.txt
│
├── dashboard.php                 # Əsas idarəetmə paneli
├── index.php                     # Ana səhifə
├── login.php                     # Giriş səhifəsi
├── logout.php                    # Çıxış əməliyyatı
├── setup.php                     # İlkin quraşdırma
├── create_crud.php              # CRUD generator
└── sis_db-boş.sql               # Verilənlər bazası strukturu
```

---

## 🗄️ Verilənlər Bazası Strukturu

### 1. **users** - İstifadəçilər cədvəli

Sistemdə qeydiyyatdan keçmiş istifadəçilərin məlumatları.

| Sahə | Tip | Açıqlama |
|-------|-----|----------|
| `id` | INT | Unikal ID (Primary Key) |
| `username` | VARCHAR(50) | İstifadəçi adı (Unikal) |
| `email` | VARCHAR(255) | E-poçt ünvanı |
| `password` | VARCHAR(255) | Şifrələnmiş parol (bcrypt) |
| `role` | ENUM | İstifadəçi rolu: 'superadmin', 'isci' |
| `is_active` | TINYINT(1) | Aktiv status (1=Aktiv, 0=Deaktiv) |
| `fullname` | VARCHAR(100) | Tam ad |
| `last_login` | DATETIME | Son giriş tarixi |
| `created_at` | TIMESTAMP | Yaradılma tarixi |

**Default istifadəçi:**
- Username: `a.hamidova`
- Rol: `superadmin`
- Şifrə: Bcrypt ilə şifrələnib

---

### 2. **employees** - İşçilər cədvəli

Təşkilatda çalışan işçilərin məlumatları.

| Sahə | Tip | Açıqlama |
|-------|-----|----------|
| `id` | INT | Unikal ID |
| `user_id` | INT | İstifadəçi ID-si (Foreign Key → users) |
| `ad` | VARCHAR(50) | Ad |
| `soyad` | VARCHAR(50) | Soyad |
| `sector_id` | INT | Sektor ID-si (Foreign Key → sectors) |
| `position_id` | INT | Vəzifə ID-si (Foreign Key → positions) |
| `created_at` | TIMESTAMP | Yaradılma tarixi |

**Əlaqələr:**
- `user_id` → `users(id)`
- `sector_id` → `sectors(id)`
- `position_id` → `positions(id)`

---

### 3. **sectors** - Sektorlar cədvəli

Təşkilatdakı müxtəlif sektorlar/departamentlər.

| Sahə | Tip | Açıqlama |
|-------|-----|----------|
| `id` | INT | Unikal ID |
| `name` | VARCHAR(100) | Sektor adı |
| `created_at` | TIMESTAMP | Yaradılma tarixi |

---

### 4. **positions** - Vəzifələr cədvəli

İşçilərin vəzifə adları.

| Sahə | Tip | Açıqlama |
|-------|-----|----------|
| `id` | INT | Unikal ID |
| `name` | VARCHAR(100) | Vəzifə adı |
| `created_at` | TIMESTAMP | Yaradılma tarixi |

---

### 5. **weekly_works** - Həftəlik işlər cədvəli

İşçilərin həftəlik iş fəaliyyət hesabatları.

| Sahə | Tip | Açıqlama |
|-------|-----|----------|
| `id` | INT | Unikal ID |
| `employee_id` | INT | İşçi ID-si (Foreign Key → employees) |
| `start_date` | DATE | Başlama tarixi |
| `end_date` | DATE | Bitmə tarixi |
| `worked_days` | INT | İşlənmiş gün sayı |
| `veten_muraciyet` | INT | Vətəndaş müraciəti sayı |
| `teskilat_muraciyet` | INT | Təşkilat müraciəti sayı |
| `sorqu` | INT | Sorğu sayı |
| `imtina` | INT | İmtina sayı |
| `arayish` | INT | Arayış sayı |
| `geri_qaytarilan` | INT | Geri qaytarılan sənəd sayı |
| `imtina_gulhuseyn` | INT | Gülhüseyn tərəfindən imtinalar |
| `imtina_aynur` | INT | Aynur tərəfindən imtinalar |
| `imtina_adil` | INT | Adil tərəfindən imtinalar |
| `tesekkur` | INT | Təşəkkür sayı |
| `status` | ENUM | Status: 'is_rejim', 'mezuniyyet', 'xestelik', 'ezamiyyet' |
| `qiymetlendirme_id` | TINYINT | Qiymətləndirmə (Foreign Key → qiymetlendirmeler) |
| `created_at` | TIMESTAMP | Yaradılma tarixi |
| `updated_at` | TIMESTAMP | Yenilənmə tarixi |

**Əlaqələr:**
- `employee_id` → `employees(id)`
- `qiymetlendirme_id` → `qiymetlendirmeler(id)`

---

### 6. **qiymetlendirmeler** - Qiymətləndirmə cədvəli

İş performansının qiymətləndirilməsi.

| Sahə | Tip | Açıqlama |
|-------|-----|----------|
| `id` | TINYINT | Unikal ID |
| `code` | VARCHAR(20) | Kod (Unikal) |
| `title` | VARCHAR(50) | Başlıq |
| `score` | TINYINT | Xal |

**Mövcud qiymətləndirmələr:**
1. **Əla** - Kod: `ela`, Xal: 3
2. **Yaxşı** - Kod: `yaxsi`, Xal: 2
3. **Kafi** - Kod: `kafi`, Xal: 1

---

## 🔐 Autentifikasiya və Səlahiyyətlər

### Roller (Roles)

Sistemdə 2 növ istifadəçi rolu mövcuddur:

#### 1. **superadmin** - Super Administrator
- Bütün modulara tam giriş hüququ
- İstifadəçi idarəetməsi
- İşçi idarəetməsi
- Sektor və vəzifə idarəetməsi
- Dashboard statistikalarına baxış
- Hesabat yaratma və redaktə etmə

#### 2. **isci** - İşçi
- Yalnız öz həftəlik hesabatlarına giriş
- Yeni hesabat yaratma
- Mövcud hesabatları redaktə etmə
- Dashboard və digər modullara giriş yoxdur

### Autentifikasiya Mexanizmi

```php
// includes/auth.php
function require_login() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: /sis/login.php');
        exit();
    }
}

function isSuperAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'superadmin';
}
```

### Session Məlumatları

Login zamanı aşağıdakı məlumatlar session-a yazılır:

```php
$_SESSION['user_id']      // İstifadəçi ID
$_SESSION['username']     // İstifadəçi adı
$_SESSION['role']         // İstifadəçi rolu
$_SESSION['fullname']     // Tam ad
$_SESSION['last_login']   // Son giriş vaxtı
$_SESSION['employee_id']  // İşçi ID (yalnız 'isci' rolu üçün)
```

---

## 📊 Dashboard Xüsusiyyətləri

Dashboard yalnız **superadmin** rolu üçün əlçatandır və aşağıdakı statistikaları göstərir:

### Aylıq Statistikalar

- İşlənmiş günlərin cəmi
- Vətəndaş müraciətlərinin sayı
- Təşkilat müraciətlərinin sayı
- Sorğu sayı
- İmtina sayı
- Arayış sayı
- Geri qaytarılan sənəd sayı
- Təşəkkür sayı

### İşçi Reytinqi

Cari ay ərzində ən çox müraciət işləyən 3 işçinin siyahısı:
- Ad və soyad
- Ümumi müraciət sayı
- Ortalama qiymətləndirmə

### Bildirişlər (Notifications)

- Yeni hesabat bildirişləri
- Geri qaytarılan sənəd xəbərdarlıqları
- Yüksək performans bildirişləri

### Gözləyən İşlər

Bu həftə hesabat verməyən işçilərin siyahısı.

---

## 🔧 Konfiqurasiya Faylları

### 1. config/database.php

```php
<?php
$servername = "localhost";
$username = "root";
$password = "";
$database = "sis_db";

$conn = new mysqli($servername, $username, $password, $database);

if ($conn->connect_error) {
    die("Əlaqə xətası: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
```

### 2. config/config.php

Dinamik URL konfiqurasiyası:

```php
define('BASE_URL', $protocol . '://' . $host . $port_suffix . $project_path . '/');
define('SITE_URL', BASE_URL);
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . $project_path . '/assets/uploads/');
```

**Köməkçi funksiyalar:**

- `url($path)` - Dinamik URL yaratma
- `asset($file)` - Asset fayllarına tam yol

---

## 📁 CRUD Modulları

### 1. Employees (İşçilər)

**Fayl:** `crud/employees/index.php`

Funksiyalar:
- İşçi siyahısını göstərmə
- Axtarış və filterləmə (sektor, vəzifə)
- Səhifələmə (pagination)
- Yeni işçi əlavə etmə
- İşçi məlumatlarını redaktə etmə
- İşçi silmə

### 2. Users (İstifadəçilər)

**Fayl:** `crud/users/index.php`

Funksiyalar:
- İstifadəçi siyahısı
- Yeni istifadəçi yaratma
- İstifadəçi məlumatlarını yeniləmə
- Şifrə dəyişdirmə
- Aktiv/Deaktiv etmə

### 3. Sectors & Positions (Sektor və Vəzifələr)

**Fayl:** `crud/sector-vezife/index.php`

Funksiyalar:
- Sektor əlavə etmə, redaktə, silmə
- Vəzifə əlavə etmə, redaktə, silmə
- AJAX əsaslı əməliyyatlar

### 4. Weekly Works (Həftəlik İşlər)

**Fayl:** `crud/weekly_works/index.php`

Funksiyalar:
- Həftəlik hesabat yaratma
- Hesabatları görmə və redaktə etmə
- Qiymətləndirmə əlavə etmə
- Status dəyişdirmə (iş rejimi, məzuniyyət, xəstəlik, ezamiyyət)

**API Endpointləri:**
- `api_create.php` - Hesabat yaratma
- `api_update.php` - Hesabat yeniləmə
- `api_delete.php` - Hesabat silmə
- `api_update_qiymet.php` - Qiymətləndirmə yeniləmə

---

## 🎨 Frontend Texnologiyaları

### CSS Framework

Layihədə Bootstrap 5 və özəl CSS faylları istifadə olunur:

- `header.css` - Başlıq üslubları
- `login.css` - Giriş səhifəsi dizaynı
- `navigation.css` - Menyu naviqasiyası
- `style.css` - Ümumi üslublar
- `weekly_w_in.css` - Həftəlik iş formu üslubları

### JavaScript Kitabxanaları

- **jQuery** - DOM manipulyasiyası
- **Bootstrap JS** - UI komponentləri
- **Font Awesome** - İkonlar
- **applications.js** - Müraciət idarəetməsi
- **main.js** - Ümumi funksiyalar

---

## 🚀 Quraşdırma Addımları

### 1. Tələblər

- PHP 7.4 və ya daha yüksək
- MySQL 5.7 və ya MariaDB 10.4+
- Apache və ya Nginx veb serveri
- phpMyAdmin (isteğe bağlı)

### 2. Quraşdırma

**Addım 1:** Layihəni klonlayın və ya ZIP faylını açın

```bash
unzip sis.zip
mv sis /var/www/html/
```

**Addım 2:** Verilənlər bazasını yaradın

```bash
mysql -u root -p
CREATE DATABASE sis_db CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
exit;
```

**Addım 3:** SQL faylını import edin

```bash
mysql -u root -p sis_db < sis_db-boş.sql
```

**Addım 4:** Verilənlər bazası məlumatlarını düzənləyin

`config/database.php` faylını açın və özünüzə görə düzəldin:

```php
$servername = "localhost";
$username = "root";        // Sizin istifadəçi adınız
$password = "";            // Sizin şifrəniz
$database = "sis_db";
```

**Addım 5:** Qovluq icazələrini təyin edin

```bash
chmod -R 755 /var/www/html/sis
chmod -R 777 /var/www/html/sis/assets/uploads
chmod -R 777 /var/www/html/sis/logs
```

**Addım 6:** Brauzerdə açın

```
http://localhost/sis
```

### 3. Default Giriş Məlumatları

```
İstifadəçi adı: a.hamidova
Şifrə: (SQL faylında bcrypt ilə şifrələnib)
```

💡 **Qeyd:** İlk dəfə giriş etdikdən sonra şifrəni dəyişdirin!

---

## 📝 Log Sistemi

Sistem avtomatik olaraq günlük log faylları yaradır:

**Məkan:** `logs/log_YYYY_MM_DD.txt`

**Nümunə:**
```
logs/log_2026_01_30.txt
```

**Log məzmunu:**
- Giriş/çıxış əməliyyatları
- CRUD əməliyyatları
- Xəta mesajları
- İstifadəçi fəaliyyətləri

---

## 🛡️ Təhlükəsizlik

### 1. SQL Injection Qorunması

Bütün SQL sorğularda **Prepared Statements** istifadə olunur:

```php
$stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
```

### 2. XSS (Cross-Site Scripting) Qorunması

İstifadəçi inputları təmizlənir:

```php
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
```

### 3. Şifrələmə

Bütün şifrələr **bcrypt** alqoritmi ilə şifrələnir:

```php
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Yoxlama
if (password_verify($password, $user['password'])) {
    // Doğru şifrə
}
```

### 4. Session Təhlükəsizliyi

- Session hijacking qarşısı
- Session timeout mexanizmi
- Təhlükəsiz session ID

---

## 🔄 API Endpointləri

### 1. GET /api/get_employee.php

İşçi məlumatlarını əldə etmək.

**Parametrlər:**
- `id` (int) - İşçi ID-si

**Cavab:**
```json
{
  "id": 1,
  "ad": "Aynur",
  "soyad": "Həmidova",
  "sector_name": "İT Departamenti",
  "position_name": "Menecer"
}
```

### 2. GET /api/get_work.php

Həftəlik iş məlumatlarını əldə etmək.

**Parametrlər:**
- `id` (int) - Weekly work ID-si

**Cavab:**
```json
{
  "id": 1,
  "employee_id": 1,
  "start_date": "2026-01-27",
  "end_date": "2026-01-31",
  "worked_days": 5,
  "veten_muraciyet": 10,
  ...
}
```

---

## 📈 Gələcək Təkmilləşdirmələr

- [ ] Excel və PDF export funksiyası
- [ ] E-poçt bildirişləri
- [ ] Dashboard-da qrafiklər və diaqramlar
- [ ] Mobil tətbiq versiyası
- [ ] İki faktorlu autentifikasiya (2FA)
- [ ] API token autentifikasiyası
- [ ] Real-time bildirişlər (WebSocket)
- [ ] Daha təfsilatlı hesabat sistemi
- [ ] Avtomatik backup mexanizmi

---

## 🐛 Məlum Problemlər və Həllər

### Problem 1: Session itirilməsi

**Həll:** `session_start()` funksiyasının hər səhifənin əvvəlində çağırıldığından əmin olun.

### Problem 2: Upload qovluğuna yazıla bilmir

**Həll:** 
```bash
chmod 777 assets/uploads
```

### Problem 3: Verilənlər bazası bağlantı xətası

**Həll:** `config/database.php` faylında məlumatları yoxlayın və MySQL servisinin işlədiyindən əmin olun.

```bash
# Linux
sudo systemctl status mysql

# Windows
net start MySQL
```

---

## 📞 Əlaqə və Dəstək

Hər hansı sual və ya problem olduqda:

- **E-poçt:** support@sis.az (misal)
- **Təşkilat:** [Təşkilatınızın adı]
- **Versiya:** 1.0.0
- **Son yeniləmə:** 30 Yanvar 2026

---

## 📜 Lisenziya

Bu layihə [Təşkilatınızın adı] tərəfindən daxili istifadə üçün hazırlanmışdır.

---

## 🙏 Təşəkkürlər

Bu layihənin hazırlanmasında istifadə olunan texnologiyalar:

- PHP
- MySQL / MariaDB
- Bootstrap 5
- jQuery
- Font Awesome
- Apache / Nginx

---

**Sənədin hazırlanma tarixi:** 31 Yanvar 2026  
**Versiya:** 1.0  
**Hazırlayan:** SİS Development Team
