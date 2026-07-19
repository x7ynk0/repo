/* Vitrin etkileşimleri */
document.addEventListener('DOMContentLoaded', function () {
    /* Mobil menü */
    var navToggle = document.querySelector('.nav-toggle');
    var siteNav = document.getElementById('site-nav');
    if (navToggle && siteNav) {
        navToggle.addEventListener('click', function () {
            var open = siteNav.classList.toggle('open');
            navToggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        });
        document.addEventListener('click', function (e) {
            if (siteNav.classList.contains('open') && !siteNav.contains(e.target) && !navToggle.contains(e.target)) {
                siteNav.classList.remove('open');
                navToggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    /* IBAN kopyalama */
    document.querySelectorAll('.btn-copy[data-copy]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var text = btn.getAttribute('data-copy');
            var done = function () {
                var old = btn.textContent;
                btn.textContent = 'Kopyalandı ✓';
                btn.classList.add('copied');
                setTimeout(function () {
                    btn.textContent = old;
                    btn.classList.remove('copied');
                }, 2000);
            };
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text).then(done).catch(function () { fallbackCopy(text, done); });
            } else {
                fallbackCopy(text, done);
            }
        });
    });

    function fallbackCopy(text, done) {
        var ta = document.createElement('textarea');
        ta.value = text;
        ta.style.position = 'fixed';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.select();
        try { document.execCommand('copy'); done(); } catch (err) { /* kopyalama desteklenmiyor */ }
        document.body.removeChild(ta);
    }

    /* Marka seçimine bağlı model listesi */
    var brandSelect = document.querySelector('select[data-model-target]');
    if (brandSelect && window.BRAND_DATA) {
        var modelSelect = document.getElementById(brandSelect.getAttribute('data-model-target'));
        var preselect = modelSelect ? modelSelect.getAttribute('data-selected') || '' : '';

        var fillModels = function (keepSelection) {
            if (!modelSelect) return;
            var brandId = brandSelect.value;
            modelSelect.innerHTML = '';
            var optAll = document.createElement('option');
            optAll.value = '';
            optAll.textContent = 'Tüm Modeller';
            modelSelect.appendChild(optAll);

            if (!brandId) {
                modelSelect.disabled = true;
                return;
            }
            var brand = window.BRAND_DATA.find(function (b) { return b.id === brandId; });
            if (!brand) { modelSelect.disabled = true; return; }
            modelSelect.disabled = false;
            brand.models.forEach(function (m) {
                var opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name;
                if (keepSelection && m.id === preselect) opt.selected = true;
                modelSelect.appendChild(opt);
            });
        };

        fillModels(true);
        brandSelect.addEventListener('change', function () { fillModels(false); });
    }

    /* Ürün detay galerisi */
    var mainImg = document.getElementById('gallery-main-img');
    if (mainImg) {
        document.querySelectorAll('.gallery-thumb').forEach(function (thumb) {
            thumb.addEventListener('click', function () {
                mainImg.src = thumb.getAttribute('data-src');
                document.querySelectorAll('.gallery-thumb').forEach(function (t) { t.classList.remove('active'); });
                thumb.classList.add('active');
            });
        });
    }
});
