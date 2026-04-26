<?php

require __DIR__.'/../vendor/autoload.php';

$app = require __DIR__.'/../bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

try {
    DB::statement('ALTER TABLE sent_emails ADD CONSTRAINT sent_emails_user_id_fk FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE SET NULL');
    echo "User FK added\n";
} catch (Exception $e) {
    echo "User FK: " . $e->getMessage() . "\n";
}

try {
    DB::statement('ALTER TABLE sent_emails ADD CONSTRAINT sent_emails_profile_id_fk FOREIGN KEY (profile_id) REFERENCES profiles(profile_id) ON DELETE SET NULL');
    echo "Profile FK added\n";
} catch (Exception $e) {
    echo "Profile FK: " . $e->getMessage() . "\n";
}

echo "Done!\n";