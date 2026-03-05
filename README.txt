================================================================
  FLEXION INDUSTRIAL - Web Sitesi Kurulum Kılavuzu
================================================================

GEREKSINIMLER
-------------
- PHP 8.1 veya üzeri
- MySQL 5.7+ veya MariaDB 10.4+
- Apache (mod_rewrite aktif) veya Nginx
- phpMyAdmin (opsiyonel, veritabanı yönetimi için)

ADIM 1 — Veritabanını Oluştur
------------------------------
1. phpMyAdmin'i aç (genellikle http://localhost/phpmyadmin)
2. Sol üstteki "Yeni" linkine tıkla
3. Yeni veritabanı adını "flexion_website" olarak gir, karakter setini
   "utf8mb4_unicode_ci" seç ve "Oluştur"a tıkla
4. Oluşturulan veritabanını seç
5. Üstteki "İçe Aktar" (Import) sekmesine geç
6. "Dosya Seç" butonuyla projenin kökündeki "database.sql" dosyasını seç
7. "Git" / "Çalıştır" butonuna tıkla
   → Tablolar ve örnek veriler otomatik olarak yüklenecektir.

ADIM 2 — Veritabanı Bağlantısını Ayarla
-----------------------------------------
"website/includes/config.php" dosyasını bir metin editörüyle aç ve şu
satırları kendi sunucu bilgilerinle değiştir:

    define('DB_HOST', 'localhost');   // genellikle değişmez
    define('DB_NAME', 'flexion_website');
    define('DB_USER', 'root');        // MySQL kullanıcı adın
    define('DB_PASS', '');            // MySQL şifren (XAMPP'ta genelde boş)

ADIM 3 — Dosyaları Web Sunucusuna Koy
---------------------------------------
Yerel geliştirme (XAMPP / Laragon):
    "website/" klasörünü kopyala:
    → C:/xampp/htdocs/flexion/website/

Tarayıcıda aç:
    http://localhost/flexion/website/           → Ana sayfa
    http://localhost/flexion/website/admin/     → Admin girişi

Canlı hosting:
    "website/" içindeki TÜM dosya ve klasörleri hosting'in
    public_html/ klasörüne FTP ile yükle.
    Ardından config.php'yi hosting MySQL bilgileriyle güncelle.

ADIM 4 — Admin Paneline Giriş
-------------------------------
URL  : http://localhost/flexion/website/admin/login.php
       (canlıda: https://siteniz.com/admin/login.php)

Varsayılan Kullanıcı Adı : admin
Varsayılan Şifre         : admin123

>> ÖNEMLİ: İlk girişte şifren otomatik olarak güvenli hash'e çevrilir.
   Lütfen giriş yaptıktan sonra "Profil / Şifre" menüsünden şifreni değiştir!

ADIM 5 — Temel Ayarları Yapılandır
------------------------------------
Admin paneline girdikten sonra:
1. "Header / Footer" → Logo yükle, site adı, telefon, e-posta gir
2. "Genel Ayarlar" → Meta açıklaması, favicon, Google Analytics kodu
3. "Menü" → Navigasyon linklerini sitene göre güncelle
4. "Kategoriler" → Kablo kategorilerini ekle / düzenle / fotoğraf yükle
5. "Ürünler" → Ürünleri ekle; teknik özellik tabloları ve regülasyonları gir
6. "Ana Sayfa Blokları" → Sürükle-bırak ile bölümleri sırala / düzenle
7. "Haberler" → İlk haber ve insights içeriklerini gir

KLASÖR YAPISI
-------------
website/
├── admin/                ← Admin paneli (login korumalı)
│   ├── login.php
│   ├── index.php         ← Dashboard
│   ├── homepage.php      ← Ana sayfa blok yönetimi
│   ├── menu.php          ← Menü yönetimi
│   ├── header-footer.php ← Header/Footer ayarları
│   ├── categories.php    ← Kategori CRUD
│   ├── products.php      ← Ürün CRUD + specs + regülasyonlar
│   ├── news.php          ← Haberler yönetimi
│   ├── settings.php      ← Genel site ayarları
│   └── profile.php       ← Şifre değiştirme
├── assets/
│   ├── css/main.css
│   ├── js/
│   └── uploads/          ← Yüklenen görseller (yazma izni gerekli)
├── includes/
│   ├── config.php        ← Veritabanı & uygulama ayarları (buradan başla)
│   ├── db.php
│   ├── auth.php
│   ├── functions.php
│   ├── header.php
│   ├── footer.php
│   └── upload_helper.php
├── index.php             ← Ana sayfa
├── sectors.php           ← Kategori / Sektör listesi
├── category.php          ← Ürün listesi (kategori bazlı)
├── product.php           ← Ürün detay
└── news.php              ← Haberler & Insights

GÜVENLİK NOTLARI
-----------------
- Şifreni ilk girişten sonra MUTLAKA değiştir (admin/admin123 zayıf şifre).
- Production ortamında config.php'de APP_ENV = 'production' bırak.
- uploads/ klasörünün PHP çalıştırmaya izin vermediğinden emin ol
  (.htaccess veya nginx config ile).
- HTTPS kullanmak için hosting/sunucunda SSL sertifikası etkinleştir.

SORUN GİDERME
--------------
"DB connection failed" hatası:
  → config.php'deki DB_USER ve DB_PASS değerlerini kontrol et.

Beyaz sayfa / 500 hatası:
  → config.php'de APP_ENV = 'development' yap, hata mesajını oku.

Görsel yüklenmiyor:
  → assets/uploads/ klasörünün yazma iznini kontrol et.
     Linux: chmod -R 775 assets/uploads/

================================================================
  Destek için: info@flexionindustrial.com
================================================================
