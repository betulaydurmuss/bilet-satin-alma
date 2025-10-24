-- Add cities table to store all 81 Turkish provinces
CREATE TABLE IF NOT EXISTS cities (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

INSERT OR IGNORE INTO cities (name) VALUES
('Adana'), ('Adıyaman'), ('Afyonkarahisar'), ('Ağrı'), ('Amasya'), ('Ankara'), ('Antalya'), ('Artvin'), ('Aydın'), ('Balıkesir'),
('Bilecik'), ('Bingöl'), ('Bitlis'), ('Bolu'), ('Burdur'), ('Bursa'), ('Çanakkale'), ('Çankırı'), ('Çorum'), ('Denizli'),
('Diyarbakır'), ('Edirne'), ('Elazığ'), ('Erzincan'), ('Erzurum'), ('Eskişehir'), ('Gaziantep'), ('Giresun'), ('Gümüşhane'), ('Hakkari'),
('Hatay'), ('Isparta'), ('Mersin'), ('İstanbul'), ('İzmir'), ('Kars'), ('Kastamonu'), ('Kayseri'), ('Kırklareli'), ('Kırşehir'),
('Kocaeli'), ('Konya'), ('Kütahya'), ('Malatya'), ('Manisa'), ('Kahramanmaraş'), ('Mardin'), ('Muğla'), ('Muş'), ('Nevşehir'),
('Niğde'), ('Ordu'), ('Rize'), ('Sakarya'), ('Samsun'), ('Siirt'), ('Sinop'), ('Sivas'), ('Tekirdağ'), ('Tokat'),
('Trabzon'), ('Tunceli'), ('Şanlıurfa'), ('Uşak'), ('Van'), ('Yozgat'), ('Zonguldak'), ('Aksaray'), ('Bayburt'), ('Karaman'),
('Kırıkkale'), ('Batman'), ('Şırnak'), ('Bartın'), ('Ardahan'), ('Iğdır'), ('Yalova'), ('Karabük'), ('Kilis'), ('Osmaniye'),
('Düzce');