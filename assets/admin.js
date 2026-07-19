/* OXIT yönetici paneli etkileşimleri */
document.addEventListener('DOMContentLoaded', function () {
    /* Silme onayları */
    document.querySelectorAll('form[data-confirm]').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            if (!window.confirm(form.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });

    /* Marka seçimine bağlı model listesi (ürün formu) */
    var brandSelect = document.querySelector('select[data-model-target]');
    if (brandSelect && window.BRAND_DATA) {
        var modelSelect = document.getElementById(brandSelect.getAttribute('data-model-target'));
        var preselect = modelSelect ? modelSelect.getAttribute('data-selected') || '' : '';

        var fillModels = function (keepSelection) {
            if (!modelSelect) return;
            var brandId = brandSelect.value;
            modelSelect.innerHTML = '';
            var optNone = document.createElement('option');
            optNone.value = '';
            optNone.textContent = 'Seçiniz';
            modelSelect.appendChild(optNone);

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

    /* Yeni yüklenecek görsellerin önizlemesi */
    var fileInput = document.getElementById('pf-images');
    var preview = document.getElementById('image-preview');
    if (fileInput && preview) {
        fileInput.addEventListener('change', function () {
            preview.innerHTML = '';
            Array.prototype.forEach.call(fileInput.files, function (file) {
                if (!file.type.match(/^image\//)) return;
                var img = document.createElement('img');
                img.alt = file.name;
                img.src = URL.createObjectURL(file);
                img.onload = function () { URL.revokeObjectURL(img.src); };
                preview.appendChild(img);
            });
        });
    }
});
