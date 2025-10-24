CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    role VARCHAR(20) NOT NULL DEFAULT 'user', -- 'user', 'firma_admin', 'admin'
    credit DECIMAL(10,2) DEFAULT 1000.00,
    company_id INTEGER,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE SET NULL
);
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
    status VARCHAR(20) DEFAULT 'active', -- 'active', 'cancelled', 'completed'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (company_id) REFERENCES companies(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tickets (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    trip_id INTEGER NOT NULL,
    seat_number INTEGER NOT NULL,
    passenger_name VARCHAR(100) NOT NULL,
    passenger_tc VARCHAR(11) NOT NULL,
    passenger_phone VARCHAR(20),
    passenger_email VARCHAR(100),
    price DECIMAL(10,2) NOT NULL,
    discount_amount DECIMAL(10,2) DEFAULT 0.00,
    final_price DECIMAL(10,2) NOT NULL,
    coupon_code VARCHAR(50),
    status VARCHAR(20) DEFAULT 'active', -- 'active', 'cancelled'
    purchase_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    cancellation_date DATETIME,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (trip_id) REFERENCES trips(id) ON DELETE CASCADE,
    UNIQUE(trip_id, seat_number)
);

CREATE TABLE IF NOT EXISTS coupons (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    code VARCHAR(50) UNIQUE NOT NULL,
    discount_rate INTEGER NOT NULL, 
    usage_limit INTEGER DEFAULT 100,
    used_count INTEGER DEFAULT 0,
    expiry_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'active', -- 'active', 'inactive'
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS coupon_usage (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    coupon_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    ticket_id INTEGER NOT NULL,
    used_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (coupon_id) REFERENCES coupons(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE
);

CREATE INDEX IF NOT EXISTS idx_users_email ON users(email);
CREATE INDEX IF NOT EXISTS idx_users_role ON users(role);
CREATE INDEX IF NOT EXISTS idx_trips_company ON trips(company_id);
CREATE INDEX IF NOT EXISTS idx_trips_date ON trips(departure_date);
CREATE INDEX IF NOT EXISTS idx_trips_cities ON trips(departure_city, arrival_city);
CREATE INDEX IF NOT EXISTS idx_tickets_user ON tickets(user_id);
CREATE INDEX IF NOT EXISTS idx_tickets_trip ON tickets(trip_id);
CREATE INDEX IF NOT EXISTS idx_tickets_status ON tickets(status);
CREATE INDEX IF NOT EXISTS idx_coupons_code ON coupons(code);

INSERT INTO users (username, email, password, full_name, role, credit) 
VALUES ('admin', 'admin@bilet.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sistem Yöneticisi', 'admin', 10000.00);

INSERT INTO companies (name, phone, email) VALUES 
('Metro Turizm', '0850 222 34 55', 'info@metroturizm.com.tr'),
('Pamukkale Turizm', '0850 333 35 25', 'info@pamukkale.com.tr'),
('Kamil Koç', '0850 256 00 53', 'info@kamilkoc.com.tr');

INSERT INTO users (username, email, password, full_name, role, credit, company_id) 
VALUES ('metro_admin', 'metro@admin.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Metro Admin', 'firma_admin', 5000.00, 1);

INSERT INTO users (username, email, password, full_name, phone, role, credit) 
VALUES ('ahmet_yilmaz', 'ahmet@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Ahmet Yılmaz', '0532 123 45 67', 'user', 2000.00);

INSERT INTO trips (company_id, departure_city, arrival_city, departure_date, departure_time, arrival_time, price, total_seats, available_seats, bus_plate) VALUES
(1, 'Ankara', 'İstanbul', '2025-10-15', '09:00:00', '14:30:00', 350.00, 39, 39, '06 ABC 123'),
(1, 'İstanbul', 'Ankara', '2025-10-15', '10:00:00', '15:30:00', 350.00, 39, 39, '34 XYZ 789'),
(2, 'Ankara', 'İzmir', '2025-10-16', '08:00:00', '16:00:00', 400.00, 45, 45, '06 DEF 456'),
(2, 'İzmir', 'Ankara', '2025-10-16', '09:00:00', '17:00:00', 400.00, 45, 45, '35 QWE 321'),
(3, 'Ankara', 'Antalya', '2025-10-17', '20:00:00', '08:00:00', 450.00, 39, 39, '06 GHI 789'),
(3, 'Antalya', 'Ankara', '2025-10-17', '21:00:00', '09:00:00', 450.00, 39, 39, '07 RTY 654'),
(1, 'Mersin', 'Zonguldak', '2025-10-18', '08:00:00', '18:00:00', 700.00, 27, 27, '33 MRS 001'),
(2, 'Mersin', 'Bursa', '2025-10-19', '09:00:00', '17:00:00', 650.00, 27, 27, '33 MRS 002');

INSERT INTO coupons (code, discount_rate, usage_limit, expiry_date, status) VALUES
('ILKSEFER', 20, 100, '2025-12-31', 'active'),
('SONBAHAR25', 15, 200, '2025-11-30', 'active'),
('ERKENREZERVASYON', 10, 150, '2025-10-31', 'active');