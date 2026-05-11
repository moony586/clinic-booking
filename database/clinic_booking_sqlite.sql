DROP TABLE IF EXISTS appointments;
DROP TABLE IF EXISTS availability;
DROP TABLE IF EXISTS users;

CREATE TABLE users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    phone TEXT,
    password TEXT NOT NULL,
    role TEXT NOT NULL CHECK (role IN ('doctor','patient')),
    created_at TEXT DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE availability (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    doctor_id INTEGER NOT NULL,
    day_name TEXT NOT NULL,
    start_time TEXT NOT NULL,
    end_time TEXT NOT NULL,
    slot_duration INTEGER NOT NULL DEFAULT 30,
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE appointments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    doctor_id INTEGER NOT NULL,
    patient_id INTEGER NOT NULL,
    appointment_date TEXT NOT NULL,
    appointment_time TEXT NOT NULL,
    reason TEXT,
    status TEXT DEFAULT 'pending' CHECK (status IN ('pending','confirmed','completed','cancelled','no_show')),
    created_at TEXT DEFAULT CURRENT_TIMESTAMP,
    UNIQUE (doctor_id, appointment_date, appointment_time),
    FOREIGN KEY (doctor_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (patient_id) REFERENCES users(id) ON DELETE CASCADE
);

-- password: 123456
INSERT INTO users (name, email, phone, password, role) VALUES
('د. أحمد سالم', 'doctor@clinic.com', '0500000000', '$2y$12$WEpoxRyrtvkioyR4ufacbuAeyzwUBnfWb4cWUGrwziSJMNrKlJ11K', 'doctor'),
('محمد علي', 'patient@clinic.com', '0555555555', '$2y$12$WEpoxRyrtvkioyR4ufacbuAeyzwUBnfWb4cWUGrwziSJMNrKlJ11K', 'patient');

INSERT INTO availability (doctor_id, day_name, start_time, end_time, slot_duration) VALUES
(1, 'الأحد', '09:00:00', '13:00:00', 30),
(1, 'الإثنين', '09:00:00', '13:00:00', 30);