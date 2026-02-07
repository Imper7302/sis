<?php
// sis/crud/weekly_works/api_create_absence.php
// Qeyri-iş günləri üçün xüsusi API

require_once '../../config/database.php';
require_once '../../includes/auth.php';

// Login yoxlaması
require_login();

// Yalnız superadmin üçün icazə
if (!isSuperAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Bu əməliyyat üçün icazəniz yoxdur!']);
    exit();
}

// JSON cavabı üçün header
header('Content-Type: application/json');

// Yalnız POST sorğuları
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Yalnız POST sorğuları qəbul edilir']);
    exit();
}

// POST məlumatlarını al
$employee_id = isset($_POST['employee_id']) ? (int)$_POST['employee_id'] : 0;
$start_date = isset($_POST['start_date']) ? trim($_POST['start_date']) : '';
$end_date = isset($_POST['end_date']) ? trim($_POST['end_date']) : '';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'mezuniyyet';
$reason = isset($_POST['reason']) ? trim($_POST['reason']) : '';
$worked_days = 0; // Həmişə 0 olacaq - qeyri-iş günü

// Status dəyərlərinin validasiyası - cədvəldəki ENUM dəyərlərinə uyğun
$allowed_statuses = ['is_rejim', 'mezuniyyet', 'xestelik', 'ezamiyyet'];
if (!in_array($status, $allowed_statuses)) {
    $status = 'is_rejim'; // Default olaraq iş rejimi xaric
}

// Validasiya
$errors = [];

if (empty($employee_id)) {
    $errors[] = 'İşçi seçilməyib!';
}

if (empty($start_date)) {
    $errors[] = 'Başlanğıc tarixi boş ola bilməz!';
}

if (empty($end_date)) {
    $errors[] = 'Bitmə tarixi boş ola bilməz!';
}

if (strtotime($start_date) > strtotime($end_date)) {
    $errors[] = 'Başlanğıc tarixi bitmə tarixindən sonra ola bilməz!';
}

if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Validasiya xətası',
        'errors' => $errors
    ]);
    exit();
}

// Bu həftə üçün artıq qeyd olub-olmadığını yoxla
$checkQuery = "SELECT id FROM weekly_works WHERE employee_id = ? AND start_date = ? AND end_date = ?";
$checkStmt = $conn->prepare($checkQuery);
$checkStmt->bind_param("iss", $employee_id, $start_date, $end_date);
$checkStmt->execute();
$checkResult = $checkStmt->get_result();

if ($checkResult->num_rows > 0) {
    http_response_code(409);
    echo json_encode([
        'success' => false,
        'message' => 'Bu həftə üçün işçinin qeydi artıq mövcuddur!'
    ]);
    exit();
}

// SQL sorğusu - cədvəl strukturuna görə (düzgün sütun adları ilə)
$query = "INSERT INTO weekly_works (
            employee_id, 
            start_date, 
            end_date, 
            worked_days, 
            veten_muraciyet, 
            teskilat_muraciyet, 
            sorqu, 
            imtina, 
            arayish, 
            geri_qaytarilan, 
            tesekkur, 
            imtina_gulhuseyn, 
            imtina_aynur, 
            imtina_adil, 
            qiymetlendirme_id,
            status,
            note,
            created_at
          ) VALUES (?, ?, ?, ?, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, NULL, ?, ?, NOW())";
          
$stmt = $conn->prepare($query);

if (!$stmt) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'SQL hazırlıq xətası: ' . $conn->error
    ]);
    exit();
}

// Note sahəsini "reason" kimi istifadə edirik (dashboard-dan gəlir)
$stmt->bind_param("ississ", 
    $employee_id, 
    $start_date, 
    $end_date, 
    $worked_days, // Həmişə 0 olacaq
    $status,
    $reason
);

if ($stmt->execute()) {
    $newId = $stmt->insert_id;
    
    // Əlavə edilən qeydi gətir
    $selectQuery = "SELECT ww.*, e.ad as employee_ad, e.soyad as employee_soyad, 
                    CONCAT(e.ad, ' ', e.soyad) as employee_fullname
                    FROM weekly_works ww 
                    INNER JOIN employees e ON ww.employee_id = e.id
                    WHERE ww.id = ?";
    $selectStmt = $conn->prepare($selectQuery);
    $selectStmt->bind_param("i", $newId);
    $selectStmt->execute();
    $result = $selectStmt->get_result();
    $newRecord = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'message' => 'Qeyri-iş günü uğurla qeydə alındı!',
        'id' => $newId,
        'record' => $newRecord,
        'status' => $status,
        'worked_days' => 0 // Qayıdışda da 0 olaraq göstəririk
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Qeyd əlavə edilərkən xəta baş verdi!',
        'error' => $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>