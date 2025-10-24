
INSERT INTO coupons (code, discount_type, discount_value, description, status, expiry_date, usage_limit, usage_count, created_at) VALUES
('HOSGELDIN20', 'percentage', 20, 'Hoş geldin indirimi - %20 indirim', 'active', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 0, NOW()),
('YILBASI25', 'percentage', 25, 'Yılbaşı özel - %25 indirim', 'active', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 50, 0, NOW()),
('ERKEN15', 'percentage', 15, 'Erken rezervasyon - %15 indirim', 'active', DATE_ADD(CURDATE(), INTERVAL 90 DAY), 200, 0, NOW()),
('VIP30', 'percentage', 30, 'VIP müşteriler için - %30 indirim', 'active', DATE_ADD(CURDATE(), INTERVAL 45 DAY), 20, 0, NOW());

INSERT INTO coupons (code, discount_type, discount_value, description, status, expiry_date, usage_limit, usage_count, created_at) VALUES
('INDIRIM50', 'fixed', 50, '50 TL indirim kuponu', 'active', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 150, 0, NOW()),
('KAMPANYA100', 'fixed', 100, '100 TL özel kampanya', 'active', DATE_ADD(CURDATE(), INTERVAL 60 DAY), 75, 0, NOW()),
('YENI25', 'fixed', 25, 'Yeni üyeler için 25 TL', 'active', DATE_ADD(CURDATE(), INTERVAL 90 DAY), 300, 0, NOW()),
('SUPER200', 'fixed', 200, 'Süper indirim - 200 TL', 'active', DATE_ADD(CURDATE(), INTERVAL 15 DAY), 10, 0, NOW());

INSERT INTO coupons (code, discount_type, discount_value, description, status, expiry_date, usage_limit, usage_count, created_at) VALUES
('DAIMI10', 'percentage', 10, 'Daimi %10 indirim', 'active', NULL, NULL, 0, NOW()),
('OGRENCI', 'percentage', 20, 'Öğrenci indirimi - %20', 'active', NULL, NULL, 0, NOW());

INSERT INTO coupons (code, discount_type, discount_value, description, status, expiry_date, usage_limit, usage_count, created_at) VALUES
('GECMIS50', 'fixed', 50, 'Süresi dolmuş kupon', 'active', DATE_SUB(CURDATE(), INTERVAL 1 DAY), 100, 0, NOW());

INSERT INTO coupons (code, discount_type, discount_value, description, status, expiry_date, usage_limit, usage_count, created_at) VALUES
('PASIF100', 'fixed', 100, 'Pasif kupon', 'inactive', DATE_ADD(CURDATE(), INTERVAL 30 DAY), 100, 0, NOW());
