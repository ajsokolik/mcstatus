<?php
// Clear cache files
$cache_dir = __DIR__ . '/cache';
if (is_dir($cache_dir)) {
    foreach (glob($cache_dir.'/*.json') as $file) {
        @unlink($file);
    }
}
echo "Cache cleared";
?>
