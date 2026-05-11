<?php
require_once '../config/db.php';
require_once 'helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $doctor_id = intval($_GET['doctor_id'] ?? 1);
    $stmt = $pdo->prepare('SELECT * FROM availability WHERE doctor_id = ? ORDER BY id DESC');
    $stmt->execute([$doctor_id]);
    response_json(true, 'الأوقات المتاحة', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
    $input = get_json_input();
    $doctor_id = intval($input['doctor_id'] ?? 0);
    $day_name = trim($input['day_name'] ?? '');
    $start_time = trim($input['start_time'] ?? '');
    $end_time = trim($input['end_time'] ?? '');
    $slot_duration = intval($input['slot_duration'] ?? 30);

    if (!$doctor_id || !$day_name || !$start_time || !$end_time) {
        response_json(false, 'الرجاء إدخال جميع بيانات الوقت المتاح', null, 400);
    }

    $stmt = $pdo->prepare('INSERT INTO availability (doctor_id, day_name, start_time, end_time, slot_duration) VALUES (?, ?, ?, ?, ?)');
    $stmt->execute([$doctor_id, $day_name, $start_time, $end_time, $slot_duration]);
    response_json(true, 'تم إضافة الوقت المتاح');
}

if ($method === 'DELETE') {
    $id = intval($_GET['id'] ?? 0);
    if (!$id) response_json(false, 'رقم الوقت غير صحيح', null, 400);
    $stmt = $pdo->prepare('DELETE FROM availability WHERE id = ?');
    $stmt->execute([$id]);
    response_json(true, 'تم حذف الوقت المتاح');
}

response_json(false, 'العملية غير مدعومة', null, 405);
