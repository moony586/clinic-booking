<?php
require_once '../config/db.php';
require_once 'helpers.php';

$input = get_json_input();
$email = trim($input['email'] ?? '');
$password = trim($input['password'] ?? '');
$role = trim($input['role'] ?? '');

if ($role == 'مريض') $role = 'patient';
elseif ($role == 'دكتور') $role = 'doctor';

if (!$email || !$password || !$role) {
    response_json(false, 'الرجاء إدخال البريد وكلمة المرور ونوع المستخدم', null, 400);
}

if (!in_array($role, ['patient', 'doctor'])) {
    response_json(false, 'نوع المستخدم غير صحيح', null, 400);
}

$stmt = $pdo->prepare('SELECT id, name, email, phone, password, role FROM users WHERE email = ? AND role = ? LIMIT 1');
$stmt->execute([$email, $role]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user || !password_verify($password, $user['password'])) {
    response_json(false, 'بيانات الدخول غير صحيحة', null, 401);
}

unset($user['password']);
response_json(true, 'تم تسجيل الدخول بنجاح', $user);
