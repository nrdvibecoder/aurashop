<?php

require_once 'config.php';

$adminFullname = 'Aura Administrator';
$adminEmail = 'admin@aurashop.com';
$adminPassword = 'AuraAdmin2026!'; 

if (!$pdo) {
    die("Database connection failed. Check your environment settings.");
}

try {
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$adminEmail]);
    $existing = $stmt->fetch();

    if ($existing) {
        echo "Admin user already exists with email: " . sanitize($adminEmail) . "\n";
    } else {
        $hash = password_hash($adminPassword, PASSWORD_BCRYPT);
        $stmt = $pdo->prepare("INSERT INTO users (fullname, email, password_hash, role) VALUES (?, ?, ?, 'admin')");
        $stmt->execute([$adminFullname, $adminEmail, $hash]);
        echo "Admin user created successfully!\n";
        echo "Email: " . sanitize($adminEmail) . "\n";
        echo "Password: " . sanitize($adminPassword) . "\n";
    }
} catch (PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage() . "\n";
}
