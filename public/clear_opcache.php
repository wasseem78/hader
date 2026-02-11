<?php
// Clear PHP opcache
if (function_exists('opcache_reset')) {
    opcache_reset();
    echo "OPcache cleared successfully!";
} else {
    echo "OPcache is not enabled.";
}

// Also clear APCu if available
if (function_exists('apcu_clear_cache')) {
    apcu_clear_cache();
    echo "<br>APCu cache cleared successfully!";
}
