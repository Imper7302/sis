<?php
// api_update_qiymet.php
require_once '../../config/database.php';
require_once '../../includes/auth.php';
require_login();

header('Content-Type: application/json');

// Yalnız superadmin qiymətləndirmə dəyişə bilər
if (!isset($_SESSION['user']['role']) || $_SESSION['user']['role'] !== 'superadmin') {
    echo json_encode(['success' => false, 'message' => 'Yetkiniz yoxdur']);
    exit;
}

$id = $_POST['id'] ?? 0;
$qiymetlendirme_id = $_POST['qiymetlendirme_id'] ?? null;

if ($id > 0) {
    // Əgər boş göndərilibsə NULL olaraq təyin et
    if ($qiymetlendirme_id === '' || $qiymetlendirme_id === '0') {
        $qiymetlendirme_id = null;
    }
    
    $stmt = $conn->prepare("UPDATE weekly_works SET qiymetlendirme_id = ? WHERE id = ?");
    
    if ($qiymetlendirme_id === null) {
        $stmt->bind_param("ii", $qiymetlendirme_id, $id);
    } else {
        $stmt->bind_param("ii", $qiymetlendirme_id, $id);
    }
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Baza xətası']);
    }
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Yanlış məlumat']);
}
?>