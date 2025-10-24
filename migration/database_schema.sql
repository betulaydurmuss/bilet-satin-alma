CREATE TABLE IF NOT EXISTS companies (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(100),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS trips (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    company_id INTEGER NOT NULL,
    departure_city VARCHAR(100) NOT NULL,
    arrival_city VARCHAR(100) NOT NULL,
    departure_date DATE NOT NULL,
    departure_time TIME NOT NULL,
    arrival_time TIME NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    total_seats INTEGER NOT NULL DEFAULT 39,
    available_seats INTEGER NOT NULL DEFAULT 39,
    bus_plate VARCHAR(20),
    status VARCHAR(20) DEFAULT 'active',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

INSERT INTO companies (name, phone, email) VALUES
('Kamil Koç', '0850 256 00 53', 'info@kamilkoc.com.tr'),
('Metro Turizm', '0850 222 34 55', 'info@metroturizm.com.tr'),
('Pamukkale Turizm', '0850 333 35 25', 'info@pamukkale.com.tr'),
('Kale Seyahat', '0850 444 55 66', 'info@kaleseyahat.com.tr'),
('Metro Otobus', '0850 555 66 77', 'info@metrobus.com.tr');

INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate) VALUES
(1, 'Ankara', 'İstanbul', '2025-10-15', '09:00:00', '14:30:00', 350.00, 39, 39, '06 ABC 123'),
(2, 'İstanbul', 'Ankara', '2025-10-15', '10:00:00', '15:30:00', 350.00, 39, 39, '34 XYZ 789'),
(3, 'İzmir', 'Bursa', '2025-10-16', '08:00:00', '12:00:00', 320.00, 39, 39, '35 QWE 321'),
(4, 'Antalya', 'Ankara', '2025-10-17', '21:00:00', '09:00:00', 450.00, 39, 39, '07 RTY 654'),
(5, 'Bursa', 'İzmir', '2025-10-18', '11:00:00', '15:00:00', 300.00, 39, 39, '16 MTR 888'),
(1, 'Zonguldak', 'Bursa', '2025-10-19', '08:00:00', '13:00:00', 400.00, 39, 39, '67 ZNG 001'),
(2, 'Zonguldak', 'İstanbul', '2025-10-19', '09:30:00', '15:00:00', 420.00, 39, 39, '67 ZNG 002'),
(3, 'Zonguldak', 'Ankara', '2025-10-20', '07:00:00', '12:00:00', 390.00, 39, 39, '67 ZNG 003'),
(4, 'Zonguldak', 'İzmir', '2025-10-21', '06:00:00', '16:00:00', 500.00, 39, 39, '67 ZNG 004'),
(5, 'Zonguldak', 'Antalya', '2025-10-22', '20:00:00', '08:00:00', 550.00, 39, 39, '67 ZNG 005');
