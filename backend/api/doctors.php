<?php
require_once '../config/db.php';
require_once 'helpers.php';

try {
    $stmt = $pdo->query('SELECT id, name, email, phone FROM users WHERE role = \'doctor\' ORDER BY name');
    $doctors = $stmt->fetchAll(PDO::FETCH_ASSOC);
    response_json(true, 'قائمة الدكاترة', $doctors);
} catch (Exception $e) {
    response_json(false, 'خطأ في قاعدة البيانات', null, 500);
}
