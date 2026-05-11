CREATE DATABASE IF NOT EXISTS clinic_booking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE clinic_booking;

DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS availability;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    password VARCHAR(255) NOT NULL,
    role ENUM('doctor','patient') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE availability (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    day_name VARCHAR(30) NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    slot_duration INT NOT NULL DEFAULT 30,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE appointments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    doctor_id INT NOT NULL,
    patient_id INT NOT NULL,
    appointment_date DATE NOT NULL,
    appointment_time TIME NOT NULL,
    reason VARCHAR(255),
    status ENUM('pending','confirmed','completed','cancelled','no_show') DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_doctor_time (doctor_id, appointment_date, appointment_time),
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- password: 123456
INSERT INTO users (name, email, phone, password, role) VALUES
('د. أحمد سالم', 'doctor@clinic.com', '0500000000', '$2y$12$WEpoxRyrtvkioyR4ufacbuAeyzwUBnfWb4cWUGrwziSJMNrKlJ11K', 'doctor'),
('محمد علي', 'patient@clinic.com', '0555555555', '$2y$12$WEpoxRyrtvkioyR4ufacbuAeyzwUBnfWb4cWUGrwziSJMNrKlJ11K', 'patient');

INSERT INTO availability (doctor_id, day_name, start_time, end_time, slot_duration) VALUES
(1, 'الأحد', '09:00:00', '13:00:00', 30),
(1, 'الإثنين', '09:00:00', '13:00:00', 30),
(1, 'الثلاثاء', '16:00:00', '20:00:00', 30),
(1, 'الأربعاء', '09:00:00', '13:00:00', 30),
(1, 'الخميس', '16:00:00', '20:00:00', 30);

INSERT INTO appointments (doctor_id, patient_id, appointment_date, appointment_time, reason, status) VALUES
(1, 2, CURDATE(), '09:00:00', 'كشف عام', 'confirmed');
