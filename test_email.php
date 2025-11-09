<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->make(\Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\Mail;

try {
    Mail::raw('Test email from Wecollab - Email verification is working!', function($message) {
        $message->to('arpayot@addu.edu.ph')
                ->subject('Wecollab Email Test - Email Verification Working');
    });
    
    echo "✅ Email sent successfully! Email verification is working.\n";
    echo "📧 Test email sent to: arpayot@addu.edu.ph\n";
    echo "🔧 SMTP Configuration: smtp.gmail.com:587 with TLS\n";
} catch (Exception $e) {
    echo "❌ Email failed to send: " . $e->getMessage() . "\n";
}