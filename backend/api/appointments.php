<?php
require_once '../config/db.php';
require_once 'helpers.php';

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    $role = $_GET['role'] ?? '';
    $user_id = intval($_GET['user_id'] ?? 0);

    if (!$role || !$user_id) {
        response_json(false, 'نوع المستخدم ورقمه مطلوبان', null, 400);
    }

    if ($role === 'doctor') {
        $stmt = $pdo->prepare(
            'SELECT a.*, p.name AS patient_name, d.name AS doctor_name
             FROM appointments a
             JOIN users p ON a.patient_id = p.id
             JOIN users d ON a.doctor_id = d.id
             WHERE a.doctor_id = ?
             ORDER BY a.appointment_date DESC, a.appointment_time DESC'
        );
        $stmt->execute([$user_id]);
    } else {
        $stmt = $pdo->prepare(
            'SELECT a.*, p.name AS patient_name, d.name AS doctor_name
             FROM appointments a
             JOIN users p ON a.patient_id = p.id
             JOIN users d ON a.doctor_id = d.id
             WHERE a.patient_id = ?
             ORDER BY a.appointment_date DESC, a.appointment_time DESC'
        );
        $stmt->execute([$user_id]);
    }

    response_json(true, 'قائمة الحجوزات', $stmt->fetchAll(PDO::FETCH_ASSOC));
}

if ($method === 'POST') {
    $input = get_json_input();
    $doctor_id = intval($input['doctor_id'] ?? 0);
    $patient_id = intval($input['patient_id'] ?? 0);
    $appointment_date = trim($input['appointment_date'] ?? '');
    $appointment_time = trim($input['appointment_time'] ?? '');
    $reason = trim($input['reason'] ?? '');

    if (!$doctor_id || !$patient_id || !$appointment_date || !$appointment_time) {
        response_json(false, 'بيانات الحجز غير مكتملة', null, 400);
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, reason, status)
             VALUES (?, ?, ?, ?, ?, "pending")'
        );
        $stmt->execute([$doctor_id, $patient_id, $appointment_date, $appointment_time, $reason]);
        response_json(true, 'تم إرسال الحجز بنجاح');
    } catch (PDOException $e) {
        response_json(false, 'هذا الموعد محجوز مسبقًا', null, 400);
    }
}

if ($method === 'PUT') {
    $input = get_json_input();
    $id = intval($input['id'] ?? 0);
    $status = trim($input['status'] ?? '');
    $allowed = ['pending', 'confirmed', 'completed', 'cancelled', 'no_show'];

    if (!$id || !in_array($status, $allowed)) {
        response_json(false, 'حالة الحجز غير صحيحة', null, 400);
    }

    $stmt = $pdo->prepare('UPDATE appointments SET status = ? WHERE id = ?');
    $stmt->execute([$status, $id]);
    response_json(true, 'تم تحديث حالة الحجز');
}

response_json(false, 'العملية غير مدعومة', null, 405);
