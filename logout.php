<?php
// Çıxış səhifəsi
session_start();

// Log yaz
if (isset($_SESSION['user_id'])) {
    $logMessage = date('Y-m-d H:i:s') . " - " . $_SESSION['user_id'] . " - LOGOUT - " . $_SESSION['username'] . " çıxış etdi" . PHP_EOL;
    $logFile = __DIR__ . '/logs/log_' . date('Y_m_d') . '.txt';
    
    if (!is_dir(__DIR__ . '/logs')) {
        mkdir(__DIR__ . '/logs', 0755, true);
    }
    
    file_put_contents($logFile, $logMessage, FILE_APPEND);
}

// Session-u təmizlə
session_unset();
session_destroy();

// Giriş səhifəsinə yönləndir
header("Location: login.php");
exit();
?>