<?php
// Bu skripti bir dəfə işlədin ki, bütün fayllarda URL-ləri avtomatik düzəltsin

function updateFiles($dir) {
    $files = scandir($dir);
    
    foreach ($files as $file) {
        if ($file == '.' || $file == '..') continue;
        
        $path = $dir . '/' . $file;
        
        if (is_dir($path)) {
            updateFiles($path);
        } elseif (pathinfo($path, PATHINFO_EXTENSION) == 'php') {
            $content = file_get_contents($path);
            
            // Header include-larını düzəlt
            $content = str_replace(
                ["include 'includes/", "include('../includes/", "include('../../includes/"],
                ["include '" . dirname(__DIR__) . "/includes/", 
                 "include('../includes/", 
                 "include('../../includes/"],
                $content
            );
            
            // Form action-larını düzəlt
            $content = preg_replace(
                '/action="([^"]*)"/',
                'action="<?php echo url(\'$1\'); ?>"',
                $content
            );
            
            file_put_contents($path, $content);
            echo "Updated: $path<br>";
        }
    }
}

echo "Starting URL update...<br>";
updateFiles(__DIR__);
echo "Update completed!";
?>