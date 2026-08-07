<?php
/**
 * Gizlilik politikası / KVKK aydınlatma metni.
 */

declare(strict_types=1);

require_once __DIR__ . '/config.php';

$page = [
    'title' => 'Gizlilik Politikası ve KVKK Aydınlatma Metni',
    'desc'  => 'Kişisel verilerinizin nasıl işlendiği, saklandığı ve korunduğu hakkında bilgilendirme.',
];

$siteName = (string) setting('site.name', APP_NAME);
$email    = (string) setting('contact.email', '');

require INC_PATH . '/layout/header.php';
?>

<section class="page-hero">
    <span class="aurora aurora--1"></span>
    <div class="container">
        <div class="page-hero__inner">
            <nav class="breadcrumb" aria-label="Sayfa yolu">
                <a href="<?= e(base_url()) ?>">Ana Sayfa</a>
                <?= icon('chevron-right', 14) ?>
                <span>Gizlilik</span>
            </nav>
            <span class="eyebrow"><?= icon('lock', 15) ?> Yasal</span>
            <h1>Gizlilik Politikası &amp; KVKK Aydınlatma Metni</h1>
            <p class="lead">Son güncelleme: <?= e(tr_date(date('Y-m-d'))) ?></p>
        </div>
    </div>
</section>

<section class="section">
    <div class="container container--narrow">
        <div class="prose reveal">
            <p><strong><?= e($siteName) ?></strong> olarak kişisel verilerinizin güvenliğine önem veriyoruz.
               Bu metin, 6698 sayılı Kişisel Verilerin Korunması Kanunu (KVKK) kapsamında veri sorumlusu sıfatıyla
               sizi bilgilendirmek amacıyla hazırlanmıştır.</p>

            <h2>1. Hangi verileri topluyoruz?</h2>
            <ul>
                <li><strong>İletişim formu verileri:</strong> ad soyad, e-posta adresi, telefon numarası, şirket adı ve mesaj içeriği.</li>
                <li><strong>Teknik veriler:</strong> tarayıcı türü, işletim sistemi ve ziyaret edilen sayfalar gibi anonim kullanım verileri.</li>
                <li><strong>Çerez verileri:</strong> tema tercihiniz gibi site deneyimini iyileştiren, kişisel kimlik içermeyen tercih kayıtları.</li>
            </ul>
            <p>IP adresiniz doğrudan saklanmaz; yalnızca kötüye kullanımı engellemek amacıyla geri döndürülemez biçimde
               özetlenerek (hash) tutulur.</p>

            <h2>2. Verileri hangi amaçla işliyoruz?</h2>
            <ul>
                <li>Talep ettiğiniz teklif ve bilgilendirmeleri hazırlamak ve size iletmek</li>
                <li>Sözleşme öncesi görüşmeleri yürütmek</li>
                <li>Hizmet kalitemizi ölçmek ve iyileştirmek</li>
                <li>Yasal yükümlülüklerimizi yerine getirmek</li>
            </ul>

            <h2>3. Hukuki dayanak</h2>
            <p>Verileriniz, KVKK m.5/2-c uyarınca sözleşmenin kurulması veya ifasıyla doğrudan doğruya ilgili olması,
               m.5/2-f uyarınca meşru menfaatlerimiz ve açık rızanız kapsamında işlenmektedir.</p>

            <h2>4. Verileri kimlerle paylaşıyoruz?</h2>
            <p>Kişisel verileriniz pazarlama amacıyla üçüncü taraflara satılmaz veya kiralanmaz.
               Yalnızca hizmetin sunulması için zorunlu olan altyapı sağlayıcılarıyla (sunucu barındırma, e-posta gönderimi)
               ve yasal olarak yetkili kamu kurumlarıyla, talep edilmesi hâlinde paylaşılabilir.</p>

            <h2>5. Saklama süresi</h2>
            <p>İletişim formu üzerinden ilettiğiniz veriler, talebin sonuçlanmasından itibaren en fazla 24 ay saklanır.
               Sürenin sonunda veriler silinir veya anonim hâle getirilir. Silinmesini daha erken talep edebilirsiniz.</p>

            <h2>6. Çerezler</h2>
            <p>Sitemizde yalnızca işlevsel çerezler kullanılmaktadır:</p>
            <ul>
                <li><code>oxit_session</code> — oturum yönetimi için zorunlu çerez.</li>
                <li><code>oxit-theme</code> — açık/koyu tema tercihinizi hatırlar (tarayıcı yerel depolaması).</li>
            </ul>
            <p>Analitik araçlar etkinleştirilmişse, ölçüm amaçlı anonim çerezler de kullanılabilir.
               Tarayıcı ayarlarınızdan çerezleri her zaman engelleyebilirsiniz.</p>

            <h2>7. Haklarınız</h2>
            <p>KVKK m.11 kapsamında; verilerinizin işlenip işlenmediğini öğrenme, işlenmişse bilgi talep etme,
               düzeltilmesini veya silinmesini isteme, işlemenin sınırlandırılmasını talep etme ve
               otomatik sistemlerle analiz sonucu aleyhinize doğan sonuçlara itiraz etme haklarına sahipsiniz.</p>
            <?php if ($email !== ''): ?>
                <p>Taleplerinizi <a href="mailto:<?= e($email) ?>"><?= e($email) ?></a> adresine iletebilirsiniz.
                   Başvurunuz en geç 30 gün içinde yanıtlanır.</p>
            <?php endif; ?>

            <h2>8. Güvenlik önlemleri</h2>
            <ul>
                <li>Tüm trafikte HTTPS/TLS şifrelemesi</li>
                <li>Parolaların geri döndürülemez biçimde özetlenerek saklanması</li>
                <li>Yetkisiz erişime karşı oturum ve CSRF koruması</li>
                <li>Düzenli yedekleme ve güvenlik güncellemeleri</li>
                <li>En az yetki (least privilege) prensibi</li>
            </ul>

            <h2>9. Değişiklikler</h2>
            <p>Bu metin, mevzuat değişiklikleri veya hizmet kapsamındaki güncellemeler doğrultusunda revize edilebilir.
               Güncel sürüm her zaman bu sayfada yayımlanır.</p>

            <blockquote>Bu metin genel bilgilendirme amaçlıdır ve hukuki danışmanlık yerine geçmez.
               Kendi işletmeniz için yayımlamadan önce bir hukuk danışmanına inceletmeniz önerilir.</blockquote>
        </div>
    </div>
</section>

<?php require INC_PATH . '/layout/footer.php'; ?>
