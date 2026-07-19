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

    /* Bildirimler: kapatma düğmesi + otomatik gizleme */
    document.querySelectorAll('.toast').forEach(function (toast) {
        var close = toast.querySelector('.toast-close');
        var hide = function () {
            toast.classList.add('toast-hide');
            setTimeout(function () { toast.remove(); }, 300);
        };
        if (close) close.addEventListener('click', hide);
        setTimeout(hide, 5000);
    });

    /* T.C. Kimlik No doğrulaması (resmi kontrol algoritması) */
    function validTckn(value) {
        if (!/^[1-9][0-9]{10}$/.test(value)) return false;
        var d = value.split('').map(Number);
        var odd = d[0] + d[2] + d[4] + d[6] + d[8];
        var even = d[1] + d[3] + d[5] + d[7];
        var d10 = ((odd * 7) - even) % 10;
        if (d10 < 0) d10 += 10;
        if (d10 !== d[9]) return false;
        var sum10 = d.slice(0, 10).reduce(function (a, b) { return a + b; }, 0);
        return sum10 % 10 === d[10];
    }

    /* Türkiye telefon numarası doğrulaması */
    function validPhoneTr(value) {
        var digits = value.replace(/\D+/g, '');
        if (digits.indexOf('0090') === 0) digits = digits.slice(4);
        else if (digits.indexOf('90') === 0 && digits.length === 12) digits = digits.slice(2);
        if (digits.indexOf('0') === 0) digits = digits.slice(1);
        return /^(5[0-9]{9}|[234][0-9]{9}|850[0-9]{7})$/.test(digits);
    }

    document.querySelectorAll('input[data-validate]').forEach(function (input) {
        var kind = input.getAttribute('data-validate');
        var hint = document.querySelector('.field-hint[data-hint-for="' + input.id + '"]');
        var check = function () {
            var value = input.value.trim();
            if (value === '') {
                input.setCustomValidity('');
                if (hint) { hint.textContent = ''; hint.className = 'field-hint'; }
                return;
            }
            var ok = kind === 'tckn' ? validTckn(value.replace(/\D+/g, '')) : validPhoneTr(value);
            if (ok) {
                input.setCustomValidity('');
                if (hint) { hint.textContent = '✓ Geçerli'; hint.className = 'field-hint hint-ok'; }
            } else {
                input.setCustomValidity(kind === 'tckn' ? 'Geçerli bir T.C. Kimlik Numarası girin.' : 'Geçerli bir Türkiye telefon numarası girin.');
                if (hint) {
                    hint.textContent = kind === 'tckn' ? 'Geçersiz T.C. Kimlik Numarası' : 'Geçersiz telefon numarası (ör. 05xx xxx xx xx)';
                    hint.className = 'field-hint hint-error';
                }
            }
        };
        input.addEventListener('input', check);
        input.addEventListener('blur', check);
        check();
    });

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
