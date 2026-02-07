<?php
// API for getting employee data by ID
require_once '../includes/auth.php';
require_once '../config/database.php';

checkAuth();

// Yalnız superadmin giriş edə bilər
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'İcazə yoxdur!']);
    exit();
}

header('Content-Type: application/json');

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid ID']);
    exit;
}

$id = intval($_GET['id']);

try {
    $sql = "SELECT * FROM employees WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $employee = $result->fetch_assoc();
        
        echo json_encode([
            'success' => true,
            'data' => $employee
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