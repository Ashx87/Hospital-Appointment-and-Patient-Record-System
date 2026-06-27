<?php
// Temporary helper — delete this file after copying the hash.
// Access at: http://localhost/gen_hash.php
$hash = password_hash('Password123!', PASSWORD_DEFAULT);
echo '<pre>';
echo "Password : Password123!\n";
echo "Hash     : " . $hash . "\n\n";
echo "Paste the Hash value into sql/seed.sql,\n";
echo "replacing every occurrence of \$2y\$12\$PLACEHOLDER_HASH_*.";
echo '</pre>';
