# Araç Yedek Parça Sitesi

Veritabanı gerektirmeyen (JSON dosya tabanlı), yönetici panelli, profesyonel araç yedek parça satış/vitrin sitesi. PHP 8+ ile çalışır; MySQL veya başka bir veritabanı **kullanılmaz** — tüm veriler `data/` klasöründeki JSON dosyalarında saklanır.

## Özellikler

### Vitrin (Ziyaretçi Tarafı)
- Modern, mobil uyumlu tasarım
- Parça adı / OEM kodu / açıklama içinde arama
- Marka → model bağımlı filtreleme, kategori filtresi, fiyat/ad sıralama, sayfalama
- Ürün detay sayfası: çoklu görsel galerisi, teknik bilgiler, stok durumu
- Telefon ve WhatsApp ile hızlı iletişim butonları (ürün bilgisiyle hazır mesaj)
- İletişim sayfası (bilgiler panelden yönetilir)
- Örnek/sahte ürün verisi içermez — ürünler yalnızca panelden eklenir

### Yönetici Paneli (OXIT)
- **Adres:** `https://siteniz.com/oxit` (Apache'de `.htaccess`, yerelde `router.php` yönlendirir)
- **İlk kurulum:** Panel ilk açıldığında ana yönetici hesabı oluşturulur. Şifre bcrypt ile şifrelenerek saklanır. Kurulum tamamlandıktan sonra bu ekran bir daha açılmaz, dışarıdan kayıt/profil oluşturulamaz.
- Ürün yönetimi: ekleme, düzenleme, silme, yayından kaldırma (pasif), öne çıkarma
- Ürün başına 8 adede kadar görsel yükleme (jpg/png/webp/gif, 5 MB sınırı), ana görsel seçimi, görsel silme
- Kategori yönetimi (ekle / yeniden adlandır / sil) — hazır 24 parça kategorisi ile gelir
- Marka & model yönetimi — 40'tan fazla marka ve yüzlerce modelle hazır gelir; dilediğinizi ekleyip silebilirsiniz
- Site ayarları: başlık, slogan, telefon, WhatsApp, e-posta, adres, hakkımızda, alt bilgi, para birimi
- Genel bakış ekranı: ürün/stok/kategori/marka istatistikleri, son eklenen ürünler

### Kullanıcı ve Yetki Sistemi
| Yetki | Ana Yönetici | Çalışan |
|---|---|---|
| Ürün / kategori / marka-model / ayar yönetimi | ✅ | ✅ |
| Çalışan listesini görme | ✅ | ❌ |
| Çalışan ekleme / silme | ✅ | ❌ |
| Kendi şifresini değiştirme | ✅ | ✅ |

- Tüm şifreler `password_hash()` (bcrypt) ile şifrelenir; düz metin saklanmaz.
- 5 hatalı giriş denemesinde 5 dakikalık geçici kilit uygulanır.
- Tüm formlar CSRF koruması altındadır.

## Veri Yapısı

Tüm veriler ilk çalıştırmada otomatik oluşturulur:

```
data/
├── users.json       → kullanıcılar (şifreler bcrypt ile)
├── products.json    → ürünler
├── categories.json  → parça kategorileri
├── brands.json      → markalar ve modelleri
├── settings.json    → site ayarları
└── .htaccess        → dışarıdan erişim engeli
uploads/             → ürün görselleri (PHP çalıştırma kapalı)
```

`data/` klasörü hem `.htaccess` (kök + klasör içi) hem de `router.php` tarafından dışarıya kapatılmıştır.

## Kurulum

### Normal sunucu (Apache + PHP 8+)
1. Dosyaları sunucunuza yükleyin (`.htaccess` dahil).
2. `data/` ve `uploads/` klasörlerinin PHP tarafından yazılabilir olduğundan emin olun (`chmod 775` genellikle yeterlidir).
3. Tarayıcıdan `https://siteniz.com/oxit` adresine gidin ve ana yönetici hesabınızı oluşturun.

### Yerel geliştirme
```bash
php -S localhost:8000 router.php
```
Ardından `http://localhost:8000` (vitrin) ve `http://localhost:8000/oxit` (panel) adreslerini açın.

> Not: `data/` ve `uploads/` klasörlerinin içeriği çalışma verisidir; `.gitignore` ile depo dışında tutulur.
