<?php
// API for getting work data by ID
require_once '../includes/auth.php';
require_once '../config/database.php';

checkAuth();

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM weekly_works WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $work = $result->fetch_assoc();
        
        // İşçi yalnız öz məlumatını götürə bilər
        if (isEmployee() && isset($_SESSION['employee_id'])) {
            if ($work['employee_id'] != $_SESSION['employee_id']) {
                echo json_encode(['success' => false, 'message' => 'İcazə yoxdur!']);
                exit;
            }
        }
        
        echo json_encode([
            'success' => true,
            'data' => $work
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Məlumat tapılmadı']);
    }
    
    $stmt->close();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>