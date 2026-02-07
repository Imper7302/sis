<?php
// Auto-detect base URL
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$script_name = $_SERVER['SCRIPT_NAME'];
$path_info = pathinfo($script_name);

// Təyin edilmiş port
$port = $_SERVER['SERVER_PORT'];
$port_suffix = ($port != 80 && $port != 443) ? ":$port" : "";

// Proyekt yolunu tap
$project_path = str_replace('/index.php', '', $script_name);
$project_path = str_replace('/dashboard.php', '', $project_path);

// Base URL təyin et
define('BASE_URL', $protocol . '://' . $host . $port_suffix . $project_path . '/');
define('SITE_URL', BASE_URL);
define('ASSETS_URL', BASE_URL . 'assets/');
define('UPLOAD_PATH', $_SERVER['DOCUMENT_ROOT'] . $project_path . '/assets/uploads/');

// Dinamik link yaratma funksiyası
function url($path = '') {
    // Əgər tam URL-dirsə, onu qaytar
    if (filter_var($path, FILTER_VALIDATE_URL)) {
        return $path;
    }
    
    // Base URL-ə əlavə et
    $full_url = BASE_URL . ltrim($path, '/');
    
    // Dublikat slash-ları sil
    return preg_replace('/([^:])(\/{2,})/', '$1/', $full_url);
}

function asset($file) {
    return ASSETS_URL . ltrim($file, '/');
}



// Debug üçün
if (isset($_GET['debug'])) {
    echo "Protocol: $protocol<br>";
    echo "Host: $host<br>";
    echo "Port: $port<br>";
    echo "Script Name: $script_name<br>";
    echo "Project Path: $project_path<br>";
    echo "Base URL: " . BASE_URL . "<br>";
    die();
}
?>