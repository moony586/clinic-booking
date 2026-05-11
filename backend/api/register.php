<?php
require_once '../config/db.php';
require_once 'helpers.php';

$input = get_json_input();
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$phone = trim($input['phone'] ?? '');
$password = trim($input['password'] ?? '');

if (!$name || !$email || !$password) {
    response_json(false, 'الاسم والبريد وكلمة المرور مطلوبة', null, 400);
}

try {
    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare('INSERT INTO users (name, email, phone, password, role) VALUES (?, ?, ?, ?, "patient")');
    $stmt->execute([$name, $email, $phone, $hashed]);
    response_json(true, 'تم إنشاء حساب المريض بنجاح', ['id' => $pdo->lastInsertId()]);
} catch (PDOException $e) {
    response_json(false, 'البريد مستخدم مسبقًا أو حدث خطأ', null, 400);
}
