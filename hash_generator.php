<?php
// hash_generator.php
$admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
$user_pass = password_hash('user123', PASSWORD_DEFAULT);

echo "Admin password hash: " . $admin_pass . "<br>";
echo "User password hash: " . $user_pass . "<br>";
echo "<hr>";
echo "Copy hash ini dan ganti di file database.sql";
?>