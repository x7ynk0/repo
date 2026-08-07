/* =====================================================================
   OXIT — Yönetici paneli etkileşimleri
   ===================================================================== */
(function () {
    'use strict';

    const doc = document;
    const $ = (s, c) => (c || doc).querySelector(s);
    const $$ = (s, c) => Array.from((c || doc).querySelectorAll(s));

    function safe(name, fn) {
        try { fn(); } catch (e) { if (window.console) console.warn('[oxit-admin] ' + name, e); }
    }

    /* ---------------------------------------------------------------
       Kenar çubuğu (mobil)
       --------------------------------------------------------------- */
    safe('sidebar', function () {
        const burger = $('#adminBurger');
        const sidebar = $('#adminSidebar');
        const overlay = $('#adminOverlay');
        if (!burger || !sidebar) return;

        function toggle(open) {
            const isOpen = open === undefined ? !sidebar.classList.contains('is-open') : open;
            sidebar.classList.toggle('is-open', isOpen);
            if (overlay) overlay.classList.toggle('is-visible', isOpen);
            doc.body.classList.toggle('is-locked', isOpen);
        }

        burger.addEventListener('click', () => toggle());
        if (overlay) overlay.addEventListener('click', () => toggle(false));
        doc.addEventListener('keydown', (ev) => { if (ev.key === 'Escape') toggle(false); });
    });

    /* ---------------------------------------------------------------
       Tema değiştirici (panel için ayrı tercih)
       --------------------------------------------------------------- */
    safe('theme', function () {
        const btn = $('#adminThemeToggle');
        if (!btn) return;

        btn.addEventListener('click', () => {
            const html = doc.documentElement;
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            try { localStorage.setItem('oxit-admin-theme', next); } catch (e) {}
        });
    });

    /* ---------------------------------------------------------------
       Silme / tehlikeli işlem onayı
       --------------------------------------------------------------- */
    safe('confirm', function () {
        doc.addEventListener('click', (ev) => {
            const el = ev.target.closest('[data-confirm]');
            if (!el) return;
            if (!window.confirm(el.dataset.confirm)) {
                ev.preventDefault();
                ev.stopPropagation();
            }
        }, true);
    });

    /* ---------------------------------------------------------------
       Parola göster / gizle
       --------------------------------------------------------------- */
    safe('password-toggle', function () {
        $$('[data-toggle-password]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const input = doc.getElementById(btn.dataset.togglePassword);
                if (!input) return;
                const show = input.type === 'password';
                input.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Parolayı gizle' : 'Parolayı göster');
                btn.classList.toggle('is-on', show);
            });
        });
    });

    /* ---------------------------------------------------------------
       Parola gücü göstergesi
       --------------------------------------------------------------- */
    safe('password-meter', function () {
        const meter = $('#pwMeter');
        if (!meter) return;

        const input = $('#s-password') || $('#p-new');
        if (!input) return;

        const bar = meter.querySelector('span');

        input.addEventListener('input', () => {
            const v = input.value;
            let score = 0;
            if (v.length >= 8) score++;
            if (v.length >= 12) score++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) score++;
            if (/[0-9]/.test(v)) score++;
            if (/[^A-Za-z0-9]/.test(v)) score++;
            score = Math.min(4, score);

            meter.setAttribute('data-level', String(score));
            bar.style.width = (score / 4 * 100) + '%';
        });
    });

    /* ---------------------------------------------------------------
       İkon seçici
       --------------------------------------------------------------- */
    safe('icon-picker', function () {
        $$('.icon-picker').forEach((picker) => {
            const hidden = $('input[type="hidden"]', picker);
            if (!hidden) return;

            $$('.icon-picker__item', picker).forEach((btn) => {
                btn.addEventListener('click', () => {
                    hidden.value = btn.dataset.icon;
                    $$('.icon-picker__item', picker).forEach((b) => b.classList.toggle('is-selected', b === btn));
                });
            });
        });
    });

    /* ---------------------------------------------------------------
       Görsel önizleme (dosya seçilince)
       --------------------------------------------------------------- */
    safe('image-preview', function () {
        $$('.image-field input[type="file"]').forEach((input) => {
            input.addEventListener('change', () => {
                const file = input.files && input.files[0];
                if (!file) return;

                const wrap = input.closest('.image-field');
                const preview = wrap && $('.image-field__preview', wrap);
                if (!preview) return;

                const reader = new FileReader();
                reader.onload = (e) => {
                    preview.classList.remove('is-empty');
                    preview.innerHTML = '<img alt="">';
                    preview.firstChild.src = e.target.result;
                };
                reader.readAsDataURL(file);
            });
        });
    });

    /* ---------------------------------------------------------------
       Renk seçici — metin kutusunu senkronla
       --------------------------------------------------------------- */
    safe('color-sync', function () {
        $$('.color-field').forEach((wrap) => {
            const picker = $('input[type="color"]', wrap);
            const text = $('input[type="text"]', wrap);
            if (!picker || !text) return;
            picker.addEventListener('input', () => { text.value = picker.value; });
        });
    });

    /* ---------------------------------------------------------------
       Çift alan satırları (metrikler)
       --------------------------------------------------------------- */
    safe('pairs', function () {
        $$('[data-pairs]').forEach((wrap) => {
            const rows = $('.pairs-field__rows', wrap);
            const addBtn = $('[data-pairs-add]', wrap);
            if (!rows) return;

            function bindDelete(row) {
                const btn = $('.pairs-field__del', row);
                if (!btn) return;
                btn.addEventListener('click', () => {
                    if ($$('.pairs-field__row', rows).length > 1) {
                        row.remove();
                    } else {
                        $$('input', row).forEach((i) => { i.value = ''; });
                    }
                });
            }

            $$('.pairs-field__row', rows).forEach(bindDelete);

            if (addBtn) {
                addBtn.addEventListener('click', () => {
                    const first = $('.pairs-field__row', rows);
                    if (!first) return;
                    const clone = first.cloneNode(true);
                    $$('input', clone).forEach((i) => { i.value = ''; });
                    rows.appendChild(clone);
                    bindDelete(clone);
                    const firstInput = $('input', clone);
                    if (firstInput) firstInput.focus();
                });
            }
        });
    });

    /* ---------------------------------------------------------------
       Sekmeler (ayarlar sayfası)
       --------------------------------------------------------------- */
    safe('tabs', function () {
        const wrap = $('[data-tabs]');
        if (!wrap) return;

        const tabs = $$('.admin-tab', wrap);
        const panels = $$('[data-tab-panel]');

        function activate(key, push) {
            tabs.forEach((t) => t.classList.toggle('is-active', t.dataset.tab === key));
            panels.forEach((p) => p.classList.toggle('is-active', p.dataset.tabPanel === key));
            try { localStorage.setItem('oxit-admin-tab', key); } catch (e) {}
            if (push) history.replaceState(null, '', '#' + key);
        }

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => activate(tab.dataset.tab, true));
        });

        // Adres çubuğundaki #bölüm veya son seçilen sekmeyi geri yükle
        let initial = (location.hash || '').replace('#', '');
        if (!initial) {
            try { initial = localStorage.getItem('oxit-admin-tab') || ''; } catch (e) {}
        }
        if (initial && tabs.some((t) => t.dataset.tab === initial)) {
            activate(initial, false);
        }
    });

    /* ---------------------------------------------------------------
       Markdown araç çubuğu
       --------------------------------------------------------------- */
    safe('editor-toolbar', function () {
        $$('[data-editor-for]').forEach((bar) => {
            const field = doc.getElementById(bar.dataset.editorFor);
            if (!field) return;

            $$('button', bar).forEach((btn) => {
                btn.addEventListener('click', () => {
                    const start = field.selectionStart;
                    const end = field.selectionEnd;
                    const selected = field.value.slice(start, end);
                    const prefix = btn.dataset.md || '';
                    const suffix = btn.dataset.mdWrap || '';

                    const replacement = prefix + (selected || 'metin') + suffix;
                    field.value = field.value.slice(0, start) + replacement + field.value.slice(end);
                    field.focus();
                    field.selectionStart = start + prefix.length;
                    field.selectionEnd = start + prefix.length + (selected || 'metin').length;
                });
            });
        });
    });

    /* ---------------------------------------------------------------
       Form gönderiminde çift tıklamayı engelle
       --------------------------------------------------------------- */
    safe('submit-guard', function () {
        $$('form').forEach((form) => {
            form.addEventListener('submit', () => {
                const btn = $('[type="submit"]', form);
                if (!btn || btn.disabled) return;
                setTimeout(() => {
                    btn.classList.add('is-loading');
                    btn.disabled = true;
                }, 10);
            });
        });
    });

    /* ---------------------------------------------------------------
       Kaydedilmemiş değişiklik uyarısı
       --------------------------------------------------------------- */
    safe('dirty-guard', function () {
        const form = $('.admin-content form');
        if (!form) return;

        let dirty = false;
        form.addEventListener('input', () => { dirty = true; });
        form.addEventListener('submit', () => { dirty = false; });

        window.addEventListener('beforeunload', (ev) => {
            if (!dirty) return;
            ev.preventDefault();
            ev.returnValue = '';
        });
    });

    /* ---------------------------------------------------------------
       Klavye kısayolları
       --------------------------------------------------------------- */
    safe('shortcuts', function () {
        doc.addEventListener('keydown', (ev) => {
            // Ctrl/Cmd + S → formu kaydet
            if ((ev.ctrlKey || ev.metaKey) && ev.key.toLowerCase() === 's') {
                const form = $('.admin-content form');
                if (form) {
                    ev.preventDefault();
                    form.requestSubmit ? form.requestSubmit() : form.submit();
                }
            }

            // "/" → arama kutusuna odaklan
            const tag = (ev.target.tagName || '').toLowerCase();
            if (ev.key === '/' && tag !== 'input' && tag !== 'textarea') {
                const search = $('.admin-search input');
                if (search) { ev.preventDefault(); search.focus(); }
            }
        });
    });

    /* ---------------------------------------------------------------
       Flash mesajları — otomatik kapan
       --------------------------------------------------------------- */
    safe('flash', function () {
        $$('.flash').forEach((el) => {
            const btn = $('.flash__close', el);
            const close = () => { el.classList.add('is-hiding'); setTimeout(() => el.remove(), 320); };
            if (btn) btn.addEventListener('click', close);
            if (el.parentElement && el.parentElement.classList.contains('flash-stack')) {
                setTimeout(close, 6000);
            }
        });
    });

    /* ---------------------------------------------------------------
       Tablo satırına tıklayınca düzenlemeye git
       --------------------------------------------------------------- */
    safe('row-click', function () {
        $$('.admin-table tbody tr').forEach((row) => {
            const link = $('.admin-table__title', row);
            if (!link) return;

            row.style.cursor = 'pointer';
            row.addEventListener('click', (ev) => {
                if (ev.target.closest('a, button, input, label')) return;
                link.click();
            });
        });
    });

})();
