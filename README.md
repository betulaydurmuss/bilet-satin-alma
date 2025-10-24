# 🎫 Bilet-Satın-Alma (PHP Otobüs Bileti Rezervasyon Sistemi)

Bu proje, PHP ile geliştirilmiş bir **otobüs bileti satın alma ve rezervasyon sistemidir.**  
Kullanıcılar kalkış ve varış şehirlerini seçerek uygun seferleri görüntüleyebilir, koltuk seçimi yapabilir ve satın aldıkları biletlerin PDF çıktısını alabilirler.

---

## 🚀 Özellikler

- 🌍 **81 İlden Her Yöne Sefer**
  - Her şehirden diğer tüm şehirlere her gün üç farklı saatte otomatik sefer oluşturulur.

- 💺 **Koltuk Seçimi**
  - Kullanıcılar interaktif koltuk düzeninden istedikleri koltuğu seçebilir.

- 🎫 **Bilet PDF İndirme**
  - Kullanıcı biletini satın aldıktan sonra otomatik olarak PDF olarak indirebilir.

- 💳 **Kupon ve Kampanya Sistemi**
  - Yönetici paneli üzerinden indirim kuponları oluşturulabilir, kampanyalar düzenlenebilir.

- 🧠 **Yönetici Paneli**
  - Firmalar, seferler, kullanıcılar ve kuponlar yönetilebilir.

- 💾 **SQLite Veritabanı Desteği**
  - Veriler `data/bilet_satin_alma.db` dosyasında saklanır.

---

## ⚙️ Kurulum


1. **XAMPP** veya benzeri bir PHP sunucusu yükle.  
2. Projeyi şu dizine kopyala: C:\xampp\htdocs\Bilet-satın-alma
3. Tarayıcıdan şu adrese git:  
👉 [http://localhost/Bilet-satın-alma/public/](http://localhost/Bilet-satın-alma/public/)

4. Sistem otomatik olarak şehir ve sefer tablolarını oluşturacaktır.

---

## 🧱 Kullanılan Teknolojiler

| Katman | Teknoloji |
|--------|------------|
| Backend | PHP 8 |
| Frontend | HTML, CSS, JavaScript |
| Veritabanı | SQLite |
| PDF | MiniPDF & SimplePDF |

---
