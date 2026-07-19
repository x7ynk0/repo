/* Vitrin etkileşimleri */
document.addEventListener('DOMContentLoaded', function () {
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
