<?php
// config/email.php - E-poçt konfiqurasiyası

// SMTP konfiqurasiyası (Gmail nümunəsi)
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'your-email@gmail.com'); // Buraya öz emailinizi yazın
define('SMTP_PASSWORD', 'your-app-password');     // Buraya Gmail App Password yazın
define('SMTP_ENCRYPTION', 'tls');

// Göndərən məlumatları
define('EMAIL_FROM', 'your-email@gmail.com');
define('EMAIL_FROM_NAME', 'SİS - Sistemli İdarəetmə Sistemi');

// E-poçt şablonları
define('EMAIL_TEMPLATES_PATH', __DIR__ . '/../email_templates/');

/**
 * E-poçt göndərmə funksiyası
 * 
 * @param string $to Alıcının email ünvanı
 * @param string $subject Başlıq
 * @param string $body HTML məzmun
 * @param string $alt_body Text məzmun (HTML dəstəkləməyənlər üçün)
 * @return bool
 */
function send_email($to, $subject, $body, $alt_body = '') {
    // PHPMailer kitabxanasından istifadə edəcəyik
    // Əgər PHPMailer yüklü deyilsə, PHP mail() funksiyasından istifadə edirik
    
    if (class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        return send_email_smtp($to, $subject, $body, $alt_body);
    } else {
        return send_email_basic($to, $subject, $body);
    }
}

/**
 * SMTP ilə e-poçt göndərmə (PHPMailer)
 */
function send_email_smtp($to, $subject, $body, $alt_body = '') {
    require_once __DIR__ . '/../vendor/autoload.php';
    
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
    
    try {
        // SMTP konfiqurasiyası
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = 'UTF-8';
        
        // Göndərən və alıcı
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to);
        
        // Məzmun
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = $alt_body ?: strip_tags($body);
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("E-poçt göndərmə xətası: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * Sadə PHP mail() funksiyası ilə göndərmə
 */
function send_email_basic($to, $subject, $body) {
    $headers = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: " . EMAIL_FROM_NAME . " <" . EMAIL_FROM . ">\r\n";
    
    return mail($to, $subject, $body, $headers);
}

/**
 * E-poçt şablonu yüklə
 */
function load_email_template($template_name, $variables = []) {
    $template_file = EMAIL_TEMPLATES_PATH . $template_name . '.php';
    
    if (!file_exists($template_file)) {
        return false;
    }
    
    // Dəyişənləri extract et
    extract($variables);
    
    // Template-i yüklə
    ob_start();
    include $template_file;
    $content = ob_get_clean();
    
    return $content;
}

/**
 * Həftəlik xatırlatma göndər
 */
function send_weekly_reminder($employee) {
    global $conn;
    
    // İşçinin email ünvanını al
    $sql = "SELECT u.email, u.fullname 
            FROM users u 
            JOIN employees e ON e.user_id = u.id 
            WHERE e.id = ? AND u.is_active = 1 AND u.email IS NOT NULL";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $employee['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 0) {
        return false;
    }
    
    $user = $result->fetch_assoc();
    
    // Email şablonunu hazırla
    $variables = [
        'fullname' => $user['fullname'] ?? $employee['ad'] . ' ' . $employee['soyad'],
        'week_start' => date('d.m.Y', strtotime('monday this week')),
        'week_end' => date('d.m.Y', strtotime('friday this week')),
        'login_url' => BASE_URL . 'login.php'
    ];
    
    $body = load_email_template('weekly_reminder', $variables);
    
    if (!$body) {
        // Əgər şablon yoxdursa, sadə mətn göndər
        $body = "
        <html>
        <body style='font-family: Arial, sans-serif;'>
            <h2>Hörmətli {$variables['fullname']},</h2>
            <p>Bu həftə ({$variables['week_start']} - {$variables['week_end']}) üçün işlədiviniz işlər haqqında məlumat daxil etməyi unutmayın.</p>
            <p><a href='{$variables['login_url']}' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Sistemə daxil ol</a></p>
            <p>Hörmətlə,<br>SİS Komandası</p>
        </body>
        </html>
        ";
    }
    
    return send_email($user['email'], 'Həftəlik Hesabat Xatırlatması', $body);
}

/**
 * Tapşırıq bildirişi göndər
 */
function send_task_notification($task_id, $employee_ids) {
    global $conn;
    
    // Tapşırıq məlumatlarını al
    $sql = "SELECT * FROM tapsiriqlar WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $task_id);
    $stmt->execute();
    $task = $stmt->get_result()->fetch_assoc();
    
    if (!$task) {
        return false;
    }
    
    $success_count = 0;
    
    foreach ($employee_ids as $emp_id) {
        // İşçinin email ünvanını al
        $sql = "SELECT u.email, u.fullname, e.ad, e.soyad
                FROM users u 
                JOIN employees e ON e.user_id = u.id 
                WHERE e.id = ? AND u.is_active = 1 AND u.email IS NOT NULL";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $emp_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            continue;
        }
        
        $user = $result->fetch_assoc();
        
        // Email şablonunu hazırla
        $variables = [
            'fullname' => $user['fullname'] ?? $user['ad'] . ' ' . $user['soyad'],
            'task_title' => $task['baslik'],
            'task_description' => $task['mezmun'],
            'deadline' => date('d.m.Y', strtotime($task['termin_tarixi'])),
            'priority' => $task['prioritet'],
            'login_url' => BASE_URL . 'crud/tapsiriqlar/index.php'
        ];
        
        $body = load_email_template('task_notification', $variables);
        
        if (!$body) {
            // Sadə şablon
            $priority_text = [
                'alci' => 'Alçaq',
                'orta' => 'Orta',
                'yuksek' => 'Yüksək'
            ];
            
            $body = "
            <html>
            <body style='font-family: Arial, sans-serif;'>
                <h2>Yeni Tapşırıq - {$task['baslik']}</h2>
                <p><strong>Hörmətli {$variables['fullname']},</strong></p>
                <p>Sizə yeni tapşırıq verilmişdir:</p>
                <div style='background: #f8f9fa; padding: 15px; border-left: 4px solid #007bff; margin: 20px 0;'>
                    <p><strong>Tapşırıq:</strong> {$task['baslik']}</p>
                    <p><strong>Təsvir:</strong><br>{$task['mezmun']}</p>
                    <p><strong>Termin:</strong> {$variables['deadline']}</p>
                    <p><strong>Prioritet:</strong> {$priority_text[$task['prioritet']]}</p>
                </div>
                <p><a href='{$variables['login_url']}' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Tapşırığa bax</a></p>
                <p>Hörmətlə,<br>SİS Komandası</p>
            </body>
            </html>
            ";
        }
        
        if (send_email($user['email'], 'Yeni Tapşırıq: ' . $task['baslik'], $body)) {
            $success_count++;
        }
    }
    
    return $success_count;
}
?>
