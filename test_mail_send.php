<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    \Illuminate\Support\Facades\Mail::raw(
        "Test email from Uhdor - verification system check",
        function ($m) {
            $m->to('waseem@uhdor.com')->subject('Uhdor Test Email');
        }
    );
    echo "Mail sent successfully!\n";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
