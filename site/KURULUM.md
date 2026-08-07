# Oxit Studio — Profesyonel Yazılım Hizmetleri Platformu

Veritabanı gerektirmeyen, tüm verisini JSON dosyalarında saklayan, PHP tabanlı
kurumsal yazılım hizmetleri web sitesi ve yönetim paneli.

---

## 1. Hızlı Kurulum (3 adım)

### 1) Dosyaları yükleyin
ZIP içeriğini sunucunuzun web kök dizinine (`public_html`, `www`, `htdocs`) çıkarın.

### 2) Klasör izinlerini verin
Aşağıdaki iki klasör **yazılabilir** olmalıdır:

```
data/       → 755 (bazı hostinglerde 775 veya 777 gerekebilir)
uploads/    → 755 (bazı hostinglerde 775 veya 777 gerekebilir)
```

FTP programınızda klasöre sağ tıklayıp "İzinler / CHMOD" bölümünden ayarlayabilirsiniz.
SSH erişiminiz varsa:

```bash
chmod -R 755 data uploads
```

### 3) Yönetici hesabınızı oluşturun
Tarayıcınızdan şu adrese gidin:

```
https://siteniz.com/oxit
```

Karşınıza **tek seferlik** yönetici oluşturma ekranı gelecek. Bilgilerinizi girip
kaydedin — hesap oluşturulduktan sonra bu adres kalıcı olarak giriş ekranına dönüşür.

> Kurulum ekranı yalnızca `data/users.json` içinde hiç kullanıcı yokken gösterilir.

**Bu kadar. Site kullanıma hazır.**

---

## 2. Sistem Gereksinimleri

| Gereksinim | Değer |
|---|---|
| PHP | 8.0 veya üzeri (8.4 ile test edildi) |
| Veritabanı | **Gerekmez** |
| PHP eklentileri | `json`, `mbstring`, `fileinfo` (standart olarak bulunur) |
| Web sunucusu | Apache (`.htaccess` dahil), Nginx veya PHP yerleşik sunucusu |

Yerel bilgisayarınızda denemek için:

```bash
cd site
php -S localhost:8000
# → http://localhost:8000
```

---

## 3. Yönetici Paneli

Adres: **`/oxit`**

| Bölüm | Ne yapar? |
|---|---|
| **Kontrol Paneli** | Ziyaret grafiği, okunmamış talepler, son hareketler, hızlı işlemler |
| **Gelen Talepler** | İletişim formundan gelen teklif talepleri; durum takibi, dahili not, CSV dışa aktarma |
| **Hizmetler** | Sunduğunuz hizmetler (15 hazır örnek içerir) |
| **Referanslar** | Vaka çalışmaları; sonuç metrikleri, teknoloji etiketleri, galeri |
| **Müşteri Görüşleri** | Ana sayfadaki kaydırmalı yorum bölümü |
| **Blog Yazıları** | SEO odaklı içerikler; okuma süresi ve görüntülenme sayacı otomatik |
| **Fiyat Paketleri** | Ana sayfadaki fiyatlandırma tablosu |
| **Çalışma Süreci** | "Nasıl çalışıyoruz" adımları |
| **Yetenekler** | Yüzdelik teknoloji çubukları |
| **SSS** | Sıkça sorulan sorular (arama motorları için yapısal veri ile) |
| **Müşteri Logoları** | Ana sayfadaki kayan logo şeridi |
| **Site Ayarları** | Marka, hero metinleri, iletişim, sosyal medya, renkler, efektler, SEO, modüller |
| **Profilim** | Ad, e-posta, fotoğraf ve parola değişimi |
| **Yedekleme** | Tüm veriyi JSON olarak indirme / geri yükleme |
| **Sistem Kaydı** | Panel işlemleri ve giriş denemeleri (son 500 kayıt) |

### Panel kısayolları
- `Ctrl/Cmd + S` → açık formu kaydeder
- `/` → arama kutusuna odaklanır
- `Shift + T` (ön yüzde) → açık/koyu tema değiştirir

---

## 4. Dosya Yapısı

```
/
├── index.php               Ana sayfa
├── hizmetler.php           Hizmet listesi
├── hizmet.php              Hizmet detayı
├── projeler.php            Referanslar (kategori filtreli)
├── proje.php               Vaka çalışması detayı
├── hakkimizda.php          Hakkımızda
├── blog.php / yazi.php     Blog listesi ve yazı detayı
├── iletisim.php            İletişim + teklif formu
├── sss.php                 Sıkça sorulan sorular
├── gizlilik.php            KVKK / gizlilik metni
├── 404.php                 Hata sayfası
├── sitemap.php             Otomatik XML site haritası
├── robots.txt
├── config.php              Tüm yapılandırma
│
├── inc/
│   ├── bootstrap.php       Önyükleyici (oturum, güvenlik başlıkları)
│   ├── Store.php           JSON veri katmanı (dosya kilitli, atomik yazma)
│   ├── helpers.php         Yardımcı fonksiyonlar + SVG ikon kütüphanesi
│   ├── auth.php            Kimlik doğrulama ve kurulum
│   ├── seed.php            İlk çalıştırmada örnek içerik
│   └── layout/             header.php, footer.php, maintenance.php
│
├── oxit/                   YÖNETİCİ PANELİ
│   ├── index.php           Yönlendirici
│   ├── inc/
│   │   ├── resources.php   İçerik türü şemaları
│   │   ├── crud.php        Genel CRUD motoru
│   │   └── admin_layout.php  Panel düzeni + form bileşenleri
│   ├── views/              setup, login, dashboard, messages, settings…
│   └── assets/             admin.css, admin.js
│
├── assets/
│   ├── css/main.css        Tasarım sistemi (~2.200 satır)
│   ├── js/main.js          30 etkileşim modülü, bağımlılık yok
│   └── img/                favicon, yer tutucu görseller
│
├── data/                   TÜM VERİ BURADA (JSON) — yazılabilir olmalı
└── uploads/                Yüklenen görseller — yazılabilir olmalı
```

---

## 5. Öne Çıkan Özellikler

### Görsel ve etkileşim
- Ön yükleme (preloader) ekranı ve yüzde göstergesi
- Özel fare imleci ve mıknatıs (magnetic) butonlar
- Kaydırma ile beliren içerik animasyonları (IntersectionObserver)
- 3D eğilme (tilt) efekti, kart üzerinde ışık takibi (spotlight)
- Canvas partikül ağı arka planı
- Daktilo efektiyle dönen başlık yazıları
- Kaydırma ilerleme çubuğu, gizlenen/yapışkan üst menü
- Sonsuz kayan logo ve teknoloji şeritleri (marquee)
- Dokunmatik destekli referans slider'ı
- Akordiyon SSS, sayaç animasyonları, yetenek çubukları
- Aurora ışık lekeleri, film greni dokusu, degrade metinler
- Açık / koyu tema (tercih tarayıcıda hatırlanır)
- Sözdizimi renklendirmeli animasyonlu kod penceresi

### Teknik
- **Sıfır bağımlılık** — jQuery yok, framework yok, npm yok
- Dosya kilitli, atomik JSON yazma (eşzamanlı istekte veri bozulmaz)
- CSRF koruması, oturum sabitleme koruması, güvenlik başlıkları
- Hatalı girişte hesap kilitleme (5 deneme → 10 dakika)
- Bal küpü (honeypot) + hız sınırlama ile form spam koruması
- Yükleme güvenliği: MIME doğrulama, uzantı beyaz listesi, SVG script taraması,
  `uploads` klasöründe PHP çalıştırma engeli
- Erişilebilirlik: klavye gezinme, `aria` etiketleri, "içeriğe geç" bağlantısı,
  `prefers-reduced-motion` desteği (efektler otomatik kapanır)
- SEO: Open Graph + Twitter kartları, JSON-LD yapısal veri (ProfessionalService,
  FAQPage), otomatik site haritası, kanonik adres, temiz başlık hiyerarşisi
- Tam duyarlı (responsive) — 390 px'ten 4K'ya kadar yatay taşma yok
- Yazdırma (print) stilleri

---

## 6. Sık Karşılaşılan Durumlar

**Kurulum ekranı gelmiyor, doğrudan giriş ekranı açılıyor**
→ Yönetici hesabı zaten oluşturulmuş demektir. Sıfırlamak için `data/users.json`
dosyasını silin; bir sonraki `/oxit` ziyaretinde kurulum ekranı yeniden açılır.
Diğer verileriniz etkilenmez.

**Parolamı unuttum**
→ Yukarıdaki adımın aynısı: `data/users.json` dosyasını silip yeni hesap oluşturun.

**Görsel yükleyemiyorum**
→ `uploads/` klasörünün yazma izni yoktur. CHMOD 755 (gerekirse 775) verin.

**Kaydettiğim içerik görünmüyor**
→ `data/` klasörünün yazma izni yoktur. Aynı şekilde izin verin.

**Yönetici panelinin adresini değiştirmek istiyorum**
→ `oxit` klasörünü yeniden adlandırın, ardından `config.php` içindeki
`define('ADMIN_DIR', 'oxit');` satırında yeni adı yazın.

**Blog / paketler / SSS bölümlerini gizlemek istiyorum**
→ Site Ayarları → **Modüller** sekmesinden ilgili bölümü kapatın.

**Renkleri değiştirmek istiyorum**
→ Site Ayarları → **Tema & Efektler**. Üç renk seçtiğinizde tüm site
(butonlar, degradeler, ışık efektleri, panel) otomatik uyum sağlar.

**Site bakımdayken kendim görebilir miyim?**
→ Evet. Bakım modunda oturum açmış yöneticiler siteyi normal görüntüler.

---

## 7. Yayına Almadan Önce Kontrol Listesi

- [ ] `/oxit` üzerinden yönetici hesabı oluşturuldu
- [ ] Site Ayarları → Genel: marka adı, slogan, açıklama, logo güncellendi
- [ ] Site Ayarları → İletişim: e-posta, telefon, WhatsApp, adres girildi
- [ ] Örnek hizmetler kendi hizmetlerinizle değiştirildi
- [ ] Örnek referanslar gerçek projelerinizle değiştirildi
- [ ] Örnek müşteri görüşleri gerçek yorumlarla değiştirildi
- [ ] Gizlilik metni (`gizlilik.php`) hukuk danışmanınıza inceletildi
- [ ] SSL sertifikası etkin; `.htaccess` içindeki HTTPS yönlendirmesi açıldı
- [ ] Google Analytics kimliği Site Ayarları → SEO bölümüne girildi
- [ ] `sitemap.php` Google Search Console'a bildirildi
- [ ] İlk yedek alındı (Yönetim → Yedekleme)

---

## 8. Güvenlik Notları

- Sunucunuz Apache ise `data/` ve `inc/` klasörlerine doğrudan HTTP erişimi
  `.htaccess` ile engellenmiştir. **Nginx kullanıyorsanız** aynı korumayı
  yapılandırmanıza eklemelisiniz:

```nginx
location ~ ^/(data|inc)/ { deny all; return 404; }
location ~ ^/uploads/.*\.(php|phtml|phar)$ { deny all; return 404; }
```

- Yedek dosyaları müşteri iletişim bilgisi içerir; güvenli saklayın.
- `config.php` içindeki `APP_DEBUG` canlıda mutlaka `false` kalmalıdır.

---

*Bu paket eksiksiz ve çalışır durumdadır; kurulum sonrası ek bir yapılandırma gerektirmez.*
