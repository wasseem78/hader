<?php
require __DIR__ . "/vendor/autoload.php";
$app = require_once __DIR__ . "/bootstrap/app.php";
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
Illuminate\Support\Facades\Cache::put("zk_push_cmd:CKQX214860360", "C:1:INFO", 300);
echo "Command queued for CKQX214860360\n";
