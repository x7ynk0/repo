/* =====================================================================
   OXIT STUDIO — Ön yüz etkileşim motoru
   ---------------------------------------------------------------------
   Bağımlılık yok. Tüm modüller kendi kendini korumalı biçimde başlar;
   biri hata verse bile diğerleri çalışmaya devam eder.
   ===================================================================== */
(function () {
    'use strict';

    const doc = document;
    const html = doc.documentElement;
    const body = doc.body;

    const prefersReduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const isTouch = window.matchMedia('(hover: none)').matches;

    const $ = (sel, ctx) => (ctx || doc).querySelector(sel);
    const $$ = (sel, ctx) => Array.from((ctx || doc).querySelectorAll(sel));

    /** Modülleri izole çalıştır: biri patlarsa sayfa çökmesin. */
    function safe(name, fn) {
        try { fn(); } catch (err) {
            if (window.console && console.warn) console.warn('[oxit] ' + name + ':', err);
        }
    }

    /** requestAnimationFrame tabanlı throttle */
    function rafThrottle(fn) {
        let ticking = false;
        return function () {
            if (ticking) return;
            ticking = true;
            requestAnimationFrame(() => { fn.apply(this, arguments); ticking = false; });
        };
    }

    function debounce(fn, wait) {
        let t;
        return function () {
            clearTimeout(t);
            t = setTimeout(() => fn.apply(this, arguments), wait || 150);
        };
    }

    const lerp = (a, b, n) => (1 - n) * a + n * b;
    const clamp = (v, min, max) => Math.min(Math.max(v, min), max);

    /* =================================================================
       1. PRELOADER
       ================================================================= */
    safe('preloader', function () {
        const el = $('#preloader');
        if (!el) return;

        const bar = $('#preloaderBar');
        const pct = $('#preloaderPct');
        let progress = 0;
        let done = false;

        const tick = setInterval(() => {
            if (done) return;
            progress += Math.random() * 16 + 4;
            if (progress > 92) progress = 92;
            render(progress);
        }, 130);

        function render(v) {
            if (bar) bar.style.width = v + '%';
            if (pct) pct.textContent = Math.round(v) + '%';
        }

        function finish() {
            if (done) return;
            done = true;
            clearInterval(tick);
            render(100);
            setTimeout(() => {
                el.classList.add('is-done');
                body.classList.add('is-loaded');
                doc.dispatchEvent(new CustomEvent('oxit:loaded'));
                setTimeout(() => el.remove(), 700);
            }, 320);
        }

        window.addEventListener('load', finish);
        // Ağ yavaşsa kilitlenmesin
        setTimeout(finish, 4200);
    });

    /* =================================================================
       2. TEMA DEĞİŞTİRİCİ
       ================================================================= */
    safe('theme', function () {
        const btn = $('#themeToggle');
        if (!btn) return;

        btn.addEventListener('click', () => {
            const next = html.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            try { localStorage.setItem('oxit-theme', next); } catch (e) {}

            // Kısa bir geçiş bastırması ile titremeyi engelle
            html.style.transition = 'background .4s ease, color .4s ease';
            setTimeout(() => { html.style.transition = ''; }, 420);
        });
    });

    /* =================================================================
       3. HEADER — yapışkan + gizlenen davranış + kaydırma çubuğu
       ================================================================= */
    safe('header', function () {
        const header = $('#siteHeader');
        const progress = $('#scrollProgress');
        const toTop = $('#toTop');
        let lastY = window.scrollY;

        const onScroll = rafThrottle(() => {
            const y = window.scrollY;

            if (header) {
                header.classList.toggle('is-stuck', y > 40);
                // Aşağı kaydırırken gizle, yukarı kaydırırken göster
                if (y > 400 && y > lastY + 6) header.classList.add('is-hidden');
                else if (y < lastY - 6 || y < 200) header.classList.remove('is-hidden');
            }

            if (progress) {
                const max = doc.documentElement.scrollHeight - window.innerHeight;
                progress.style.width = (max > 0 ? (y / max) * 100 : 0) + '%';
            }

            if (toTop) toTop.classList.toggle('is-visible', y > 600);

            lastY = y;
        });

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();

        if (toTop) {
            toTop.addEventListener('click', () => {
                window.scrollTo({ top: 0, behavior: prefersReduced ? 'auto' : 'smooth' });
            });
        }
    });

    /* =================================================================
       4. MOBİL MENÜ
       ================================================================= */
    safe('mobile-menu', function () {
        const burger = $('#burger');
        const menu = $('#mobileMenu');
        if (!burger || !menu) return;

        function toggle(open) {
            const isOpen = open === undefined ? !menu.classList.contains('is-open') : open;
            menu.classList.toggle('is-open', isOpen);
            burger.classList.toggle('is-open', isOpen);
            burger.setAttribute('aria-expanded', String(isOpen));
            menu.setAttribute('aria-hidden', String(!isOpen));
            body.classList.toggle('is-locked', isOpen);
        }

        burger.addEventListener('click', () => toggle());

        menu.addEventListener('click', (ev) => {
            if (ev.target === menu) toggle(false);           // dış tıklama
            if (ev.target.closest('a')) toggle(false);        // bağlantıya tıklama
        });

        doc.addEventListener('keydown', (ev) => {
            if (ev.key === 'Escape' && menu.classList.contains('is-open')) toggle(false);
        });
    });

    /* =================================================================
       5. ÖZEL İMLEÇ (masaüstü)
       ================================================================= */
    safe('cursor', function () {
        if (isTouch || prefersReduced) return;
        if (body.dataset.cursor === '0') return;

        const dot = $('#cursor');
        const ring = $('#cursorRing');
        if (!dot || !ring) return;

        let mx = window.innerWidth / 2, my = window.innerHeight / 2;
        let rx = mx, ry = my;
        let active = false;

        window.addEventListener('mousemove', (ev) => {
            mx = ev.clientX;
            my = ev.clientY;
            if (!active) { active = true; body.classList.add('cursor-ready'); }
        }, { passive: true });

        window.addEventListener('mouseout', (ev) => {
            if (!ev.relatedTarget) body.classList.remove('cursor-ready');
        });
        window.addEventListener('mouseover', () => {
            if (active) body.classList.add('cursor-ready');
        });

        (function loop() {
            rx = lerp(rx, mx, 0.16);
            ry = lerp(ry, my, 0.16);
            dot.style.transform = `translate3d(${mx - 4}px, ${my - 4}px, 0)`;
            ring.style.transform = `translate3d(${rx - 19}px, ${ry - 19}px, 0)`;
            requestAnimationFrame(loop);
        })();

        const hoverSel = 'a, button, [data-magnetic], .card, .project-card, .accordion__head, input, textarea, select, .choice, .filter-btn';
        doc.addEventListener('mouseover', (ev) => {
            if (ev.target.closest && ev.target.closest(hoverSel)) body.classList.add('cursor-hover');
        });
        doc.addEventListener('mouseout', (ev) => {
            if (ev.target.closest && ev.target.closest(hoverSel)) body.classList.remove('cursor-hover');
        });
    });

    /* =================================================================
       6. MIKNATIS BUTONLAR
       ================================================================= */
    safe('magnetic', function () {
        if (isTouch || prefersReduced) return;

        $$('[data-magnetic]').forEach((el) => {
            const strength = parseFloat(el.dataset.magnetic) || 0.32;

            el.addEventListener('mousemove', (ev) => {
                const r = el.getBoundingClientRect();
                const x = ev.clientX - r.left - r.width / 2;
                const y = ev.clientY - r.top - r.height / 2;
                el.style.transform = `translate(${x * strength}px, ${y * strength}px)`;
            });

            el.addEventListener('mouseleave', () => {
                el.style.transform = '';
            });
        });
    });

    /* =================================================================
       7. GÖRÜNÜRLÜK ANİMASYONLARI (reveal)
       ================================================================= */
    safe('reveal', function () {
        const items = $$('.reveal');
        if (!items.length) return;

        if (prefersReduced || !('IntersectionObserver' in window)) {
            items.forEach((el) => el.classList.add('is-visible'));
            return;
        }

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                entry.target.classList.add('is-visible');
                io.unobserve(entry.target);
            });
        }, { threshold: 0.12, rootMargin: '0px 0px -60px 0px' });

        items.forEach((el) => io.observe(el));
    });

    /* =================================================================
       8. SAYAÇLAR
       ================================================================= */
    safe('counters', function () {
        const items = $$('[data-count]');
        if (!items.length) return;

        function run(el) {
            const target = parseFloat(el.dataset.count) || 0;
            const suffix = el.dataset.suffix || '';
            const dur = parseInt(el.dataset.duration, 10) || 1800;
            const decimals = (String(target).split('.')[1] || '').length;

            if (prefersReduced) {
                el.textContent = target.toFixed(decimals) + suffix;
                return;
            }

            const start = performance.now();
            (function step(now) {
                const p = clamp((now - start) / dur, 0, 1);
                const eased = 1 - Math.pow(1 - p, 3);      // easeOutCubic
                const val = target * eased;
                el.textContent = (decimals ? val.toFixed(decimals) : Math.round(val).toLocaleString('tr-TR')) + suffix;
                if (p < 1) requestAnimationFrame(step);
            })(start);
        }

        if (!('IntersectionObserver' in window)) { items.forEach(run); return; }

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                run(entry.target);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.4 });

        items.forEach((el) => io.observe(el));
    });

    /* =================================================================
       9. YETENEK ÇUBUKLARI
       ================================================================= */
    safe('skills', function () {
        const bars = $$('.skill__fill');
        if (!bars.length) return;

        const fill = (el) => { el.style.width = (el.dataset.level || 0) + '%'; };

        if (!('IntersectionObserver' in window)) { bars.forEach(fill); return; }

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                setTimeout(() => fill(entry.target), (parseInt(entry.target.dataset.delay, 10) || 0) * 90);
                io.unobserve(entry.target);
            });
        }, { threshold: 0.3 });

        bars.forEach((el) => io.observe(el));
    });

    /* =================================================================
       10. HERO — dönen yazı (typewriter)
       ================================================================= */
    safe('rotator', function () {
        const el = $('#heroRotator');
        if (!el) return;

        let words = [];
        try { words = JSON.parse(el.dataset.words || '[]'); } catch (e) { words = []; }
        if (!words.length) return;

        const out = doc.createElement('span');
        const caret = doc.createElement('span');
        caret.className = 'caret';
        el.textContent = '';
        el.append(out, caret);

        if (prefersReduced) { out.textContent = words[0]; return; }

        let wi = 0, ci = 0, deleting = false;

        (function type() {
            const word = words[wi];
            out.textContent = word.slice(0, ci);

            let delay = deleting ? 42 : 78;

            if (!deleting && ci === word.length) {
                delay = 1900;
                deleting = true;
            } else if (deleting && ci === 0) {
                deleting = false;
                wi = (wi + 1) % words.length;
                delay = 320;
            } else {
                ci += deleting ? -1 : 1;
            }

            setTimeout(type, delay);
        })();
    });

    /* =================================================================
       11. KART ÜZERİNDE IŞIK TAKİBİ (spotlight)
       ================================================================= */
    safe('spotlight', function () {
        if (isTouch) return;

        doc.addEventListener('mousemove', rafThrottle((ev) => {
            const card = ev.target.closest && ev.target.closest('.card, .price-card, .timeline-item__body, .process-step');
            if (!card) return;
            const r = card.getBoundingClientRect();
            card.style.setProperty('--mx', (ev.clientX - r.left) + 'px');
            card.style.setProperty('--my', (ev.clientY - r.top) + 'px');
        }), { passive: true });
    });

    /* =================================================================
       12. 3D TILT
       ================================================================= */
    safe('tilt', function () {
        if (isTouch || prefersReduced) return;

        $$('[data-tilt]').forEach((el) => {
            const max = parseFloat(el.dataset.tilt) || 8;

            el.addEventListener('mousemove', (ev) => {
                const r = el.getBoundingClientRect();
                const px = (ev.clientX - r.left) / r.width;
                const py = (ev.clientY - r.top) / r.height;
                const rx = (0.5 - py) * max * 2;
                const ry = (px - 0.5) * max * 2;
                el.style.transform = `perspective(900px) rotateX(${rx}deg) rotateY(${ry}deg) translateZ(0)`;
            });

            el.addEventListener('mouseleave', () => {
                el.style.transform = 'perspective(900px) rotateX(0) rotateY(0)';
            });
        });
    });

    /* =================================================================
       13. PARALLAX
       ================================================================= */
    safe('parallax', function () {
        const items = $$('[data-parallax]');
        if (!items.length || prefersReduced) return;

        const onScroll = rafThrottle(() => {
            const vh = window.innerHeight;
            items.forEach((el) => {
                const r = el.getBoundingClientRect();
                if (r.bottom < -200 || r.top > vh + 200) return;
                const speed = parseFloat(el.dataset.parallax) || 0.12;
                const offset = (r.top + r.height / 2 - vh / 2) * -speed;
                el.style.transform = `translate3d(0, ${offset.toFixed(2)}px, 0)`;
            });
        });

        window.addEventListener('scroll', onScroll, { passive: true });
        onScroll();
    });

    /* =================================================================
       14. AKORDİYON (SSS)
       ================================================================= */
    safe('accordion', function () {
        $$('.accordion').forEach((acc) => {
            const single = acc.dataset.single !== '0';
            const items = $$('.accordion__item', acc);

            items.forEach((item) => {
                const head = $('.accordion__head', item);
                const panel = $('.accordion__panel', item);
                if (!head || !panel) return;

                head.setAttribute('aria-expanded', item.classList.contains('is-open') ? 'true' : 'false');
                if (item.classList.contains('is-open')) panel.style.maxHeight = panel.scrollHeight + 'px';

                head.addEventListener('click', () => {
                    const open = !item.classList.contains('is-open');

                    if (single) {
                        items.forEach((other) => {
                            if (other === item) return;
                            other.classList.remove('is-open');
                            const op = $('.accordion__panel', other);
                            const oh = $('.accordion__head', other);
                            if (op) op.style.maxHeight = null;
                            if (oh) oh.setAttribute('aria-expanded', 'false');
                        });
                    }

                    item.classList.toggle('is-open', open);
                    head.setAttribute('aria-expanded', String(open));
                    panel.style.maxHeight = open ? panel.scrollHeight + 'px' : null;
                });
            });

            // Pencere yeniden boyutlandığında açık panelleri yeniden ölç
            window.addEventListener('resize', debounce(() => {
                items.forEach((item) => {
                    if (!item.classList.contains('is-open')) return;
                    const panel = $('.accordion__panel', item);
                    if (panel) panel.style.maxHeight = panel.scrollHeight + 'px';
                });
            }, 200));
        });
    });

    /* =================================================================
       15. SLIDER (referanslar vb.)
       ================================================================= */
    safe('slider', function () {
        $$('[data-slider]').forEach(initSlider);

        function initSlider(root) {
            const track = $('.slider__track', root);
            const slides = $$('.slider__slide', root);
            if (!track || !slides.length) return;

            const prev = $('[data-slider-prev]', root);
            const next = $('[data-slider-next]', root);
            const dotsWrap = $('.slider__dots', root);
            const autoplay = root.dataset.autoplay !== '0';
            const interval = parseInt(root.dataset.interval, 10) || 5200;

            let index = 0;
            let perView = 1;
            let maxIndex = 0;
            let timer = null;

            function measure() {
                const w = root.clientWidth;
                perView = w >= 1024 ? 3 : (w >= 680 ? 2 : 1);
                maxIndex = Math.max(0, slides.length - perView);
                index = clamp(index, 0, maxIndex);
                buildDots();
                update();
            }

            function update() {
                const slideW = slides[0].getBoundingClientRect().width;
                const gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 24;
                track.style.transform = `translate3d(-${index * (slideW + gap)}px, 0, 0)`;

                if (prev) prev.disabled = index <= 0;
                if (next) next.disabled = index >= maxIndex;

                if (dotsWrap) {
                    $$('.slider__dot', dotsWrap).forEach((d, i) => {
                        d.classList.toggle('is-active', i === index);
                    });
                }
            }

            function buildDots() {
                if (!dotsWrap) return;
                dotsWrap.innerHTML = '';
                for (let i = 0; i <= maxIndex; i++) {
                    const dot = doc.createElement('button');
                    dot.type = 'button';
                    dot.className = 'slider__dot' + (i === index ? ' is-active' : '');
                    dot.setAttribute('aria-label', (i + 1) + '. gruba git');
                    dot.addEventListener('click', () => { index = i; update(); restart(); });
                    dotsWrap.appendChild(dot);
                }
            }

            function go(dir) {
                index = clamp(index + dir, 0, maxIndex);
                update();
                restart();
            }

            function restart() {
                if (!autoplay || prefersReduced) return;
                clearInterval(timer);
                timer = setInterval(() => {
                    index = index >= maxIndex ? 0 : index + 1;
                    update();
                }, interval);
            }

            if (prev) prev.addEventListener('click', () => go(-1));
            if (next) next.addEventListener('click', () => go(1));

            root.addEventListener('mouseenter', () => clearInterval(timer));
            root.addEventListener('mouseleave', restart);

            // Dokunmatik kaydırma
            let startX = 0, delta = 0, dragging = false;
            track.addEventListener('touchstart', (ev) => {
                dragging = true; startX = ev.touches[0].clientX; delta = 0;
                clearInterval(timer);
            }, { passive: true });
            track.addEventListener('touchmove', (ev) => {
                if (!dragging) return;
                delta = ev.touches[0].clientX - startX;
            }, { passive: true });
            track.addEventListener('touchend', () => {
                dragging = false;
                if (Math.abs(delta) > 55) go(delta < 0 ? 1 : -1);
                else restart();
            });

            // Klavye
            root.addEventListener('keydown', (ev) => {
                if (ev.key === 'ArrowLeft') go(-1);
                if (ev.key === 'ArrowRight') go(1);
            });

            window.addEventListener('resize', debounce(measure, 180));
            measure();
            restart();
        }
    });

    /* =================================================================
       16. FİLTRELEME (projeler / hizmetler)
       ================================================================= */
    safe('filter', function () {
        $$('[data-filter-group]').forEach((group) => {
            const targetSel = group.dataset.filterTarget;
            const items = $$(targetSel);
            const buttons = $$('.filter-btn', group);
            if (!items.length) return;

            buttons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    const val = btn.dataset.filter || '*';
                    buttons.forEach((b) => b.classList.toggle('is-active', b === btn));

                    let shown = 0;
                    items.forEach((item) => {
                        const cats = (item.dataset.category || '').toLowerCase();
                        const match = val === '*' || cats.split('|').indexOf(val.toLowerCase()) !== -1;
                        item.classList.toggle('is-filtered-out', !match);
                        if (match) {
                            shown++;
                            item.style.animation = 'none';
                            void item.offsetWidth;                 // reflow → animasyonu yeniden tetikle
                            item.style.animation = 'fadeUp .5s var(--ease-out) both';
                            item.style.animationDelay = (shown * 0.045) + 's';
                        }
                    });

                    const empty = $(group.dataset.filterEmpty || '#filterEmpty');
                    if (empty) empty.hidden = shown !== 0;
                });
            });
        });
    });

    /* =================================================================
       17. MARQUEE — içeriği ikiye katlayarak kesintisiz döngü
       ================================================================= */
    safe('marquee', function () {
        $$('.marquee__track').forEach((track) => {
            if (track.dataset.cloned === '1') return;
            track.innerHTML += track.innerHTML;
            track.dataset.cloned = '1';
        });
    });

    /* =================================================================
       18. PARTİKÜL ARKA PLANI
       ================================================================= */
    safe('particles', function () {
        if (prefersReduced || isTouch) return;
        if (body.dataset.particles === '0') return;
        if (!$('.hero')) return;

        const canvas = doc.createElement('canvas');
        canvas.id = 'particles';
        body.insertBefore(canvas, body.firstChild);

        const ctx = canvas.getContext('2d');
        if (!ctx) return;

        let w = 0, h = 0, dots = [], raf = null;
        const accent = getComputedStyle(html).getPropertyValue('--accent').trim() || '#7c5cff';

        function resize() {
            w = canvas.width = window.innerWidth;
            h = canvas.height = window.innerHeight;
            const count = clamp(Math.round((w * h) / 26000), 26, 78);
            dots = Array.from({ length: count }, () => ({
                x: Math.random() * w,
                y: Math.random() * h,
                vx: (Math.random() - 0.5) * 0.28,
                vy: (Math.random() - 0.5) * 0.28,
                r: Math.random() * 1.7 + 0.5
            }));
        }

        function draw() {
            ctx.clearRect(0, 0, w, h);

            for (let i = 0; i < dots.length; i++) {
                const d = dots[i];
                d.x += d.vx; d.y += d.vy;
                if (d.x < 0 || d.x > w) d.vx *= -1;
                if (d.y < 0 || d.y > h) d.vy *= -1;

                ctx.beginPath();
                ctx.arc(d.x, d.y, d.r, 0, Math.PI * 2);
                ctx.fillStyle = accent;
                ctx.globalAlpha = 0.42;
                ctx.fill();

                for (let j = i + 1; j < dots.length; j++) {
                    const o = dots[j];
                    const dx = d.x - o.x, dy = d.y - o.y;
                    const dist = Math.sqrt(dx * dx + dy * dy);
                    if (dist > 132) continue;
                    ctx.beginPath();
                    ctx.moveTo(d.x, d.y);
                    ctx.lineTo(o.x, o.y);
                    ctx.strokeStyle = accent;
                    ctx.globalAlpha = (1 - dist / 132) * 0.16;
                    ctx.lineWidth = 1;
                    ctx.stroke();
                }
            }

            ctx.globalAlpha = 1;
            raf = requestAnimationFrame(draw);
        }

        // Sekme arka plandayken CPU harcama
        doc.addEventListener('visibilitychange', () => {
            if (doc.hidden) { cancelAnimationFrame(raf); }
            else { raf = requestAnimationFrame(draw); }
        });

        window.addEventListener('resize', debounce(resize, 220));
        resize();
        draw();
    });

    /* =================================================================
       19. FORM — doğrulama, gönderim durumu, karakter sayacı
       ================================================================= */
    safe('forms', function () {
        $$('form[data-validate]').forEach((form) => {
            form.setAttribute('novalidate', 'novalidate');

            form.addEventListener('submit', (ev) => {
                let ok = true;
                let firstBad = null;

                $$('[required]', form).forEach((field) => {
                    const wrap = field.closest('.field') || field.parentElement;
                    const errEl = wrap ? $('.field__error', wrap) : null;
                    let msg = '';

                    if (field.type === 'checkbox' && !field.checked) {
                        msg = 'Bu alanı işaretlemelisiniz.';
                    } else if (!field.value.trim()) {
                        msg = 'Bu alan zorunludur.';
                    } else if (field.type === 'email' && !/^[^\s@]+@[^\s@]+\.[a-z]{2,}$/i.test(field.value.trim())) {
                        msg = 'Geçerli bir e-posta adresi girin.';
                    } else if (field.dataset.minlength && field.value.trim().length < parseInt(field.dataset.minlength, 10)) {
                        msg = 'En az ' + field.dataset.minlength + ' karakter girin.';
                    }

                    if (errEl) errEl.textContent = msg;
                    field.style.borderColor = msg ? '#ef4444' : '';
                    if (msg) { ok = false; if (!firstBad) firstBad = field; }
                });

                if (!ok) {
                    ev.preventDefault();
                    if (firstBad) {
                        firstBad.scrollIntoView({ block: 'center', behavior: prefersReduced ? 'auto' : 'smooth' });
                        firstBad.focus({ preventScroll: true });
                    }
                    return;
                }

                const btn = $('[type="submit"]', form);
                if (btn) { btn.classList.add('is-loading'); btn.disabled = true; }
            });

            // Yazarken hatayı temizle
            form.addEventListener('input', (ev) => {
                const field = ev.target;
                if (!field.hasAttribute || !field.hasAttribute('required')) return;
                const wrap = field.closest('.field');
                const errEl = wrap ? $('.field__error', wrap) : null;
                if (errEl && errEl.textContent) { errEl.textContent = ''; field.style.borderColor = ''; }
            });
        });

        // Karakter sayacı
        $$('[data-counter-for]').forEach((counter) => {
            const field = $('#' + counter.dataset.counterFor);
            if (!field) return;
            const max = parseInt(field.getAttribute('maxlength'), 10) || 2000;
            const render = () => { counter.textContent = field.value.length + ' / ' + max; };
            field.addEventListener('input', render);
            render();
        });
    });

    /* =================================================================
       20. FLASH MESAJLARI
       ================================================================= */
    safe('flash', function () {
        $$('.flash').forEach((el) => {
            const close = () => {
                el.classList.add('is-hiding');
                setTimeout(() => el.remove(), 320);
            };
            const btn = $('.flash__close', el);
            if (btn) btn.addEventListener('click', close);
            setTimeout(close, 6500);
        });
    });

    /* =================================================================
       21. YUMUŞAK İÇ BAĞLANTI KAYDIRMA
       ================================================================= */
    safe('anchors', function () {
        doc.addEventListener('click', (ev) => {
            const link = ev.target.closest && ev.target.closest('a[href^="#"]');
            if (!link) return;

            const id = link.getAttribute('href');
            if (!id || id === '#' || id.length < 2) return;

            const target = doc.getElementById(id.slice(1));
            if (!target) return;

            ev.preventDefault();
            const top = target.getBoundingClientRect().top + window.scrollY
                - (parseInt(getComputedStyle(html).getPropertyValue('--header-h'), 10) || 78) - 20;
            window.scrollTo({ top, behavior: prefersReduced ? 'auto' : 'smooth' });
            history.replaceState(null, '', id);
        });
    });

    /* =================================================================
       22. KOPYALAMA DÜĞMELERİ
       ================================================================= */
    safe('copy', function () {
        $$('[data-copy]').forEach((btn) => {
            btn.addEventListener('click', async () => {
                const text = btn.dataset.copy;
                try {
                    await navigator.clipboard.writeText(text);
                    const old = btn.getAttribute('title') || '';
                    btn.setAttribute('title', 'Kopyalandı!');
                    btn.classList.add('is-copied');
                    setTimeout(() => { btn.setAttribute('title', old); btn.classList.remove('is-copied'); }, 1800);
                } catch (e) { /* pano izni yok */ }
            });
        });
    });

    /* =================================================================
       23. TEMBEL GÖRSEL YÜKLEME (yedek)
       ================================================================= */
    safe('lazy', function () {
        if ('loading' in HTMLImageElement.prototype) return;
        $$('img[loading="lazy"][data-src]').forEach((img) => {
            img.src = img.dataset.src;
        });
    });

    /* =================================================================
       24. KLAVYE KISAYOLLARI (erişilebilirlik + hız)
       ================================================================= */
    safe('shortcuts', function () {
        doc.addEventListener('keydown', (ev) => {
            const tag = (ev.target.tagName || '').toLowerCase();
            if (tag === 'input' || tag === 'textarea' || ev.target.isContentEditable) return;

            // Shift + T → tema değiştir
            if (ev.shiftKey && ev.key.toLowerCase() === 't') {
                const btn = $('#themeToggle');
                if (btn) btn.click();
            }
        });
    });

    /* =================================================================
       25. AKTİF BÖLÜM İZLEME (tek sayfa gezinme vurgusu)
       ================================================================= */
    safe('scrollspy', function () {
        const sections = $$('section[id]');
        const links = $$('a[data-spy]');
        if (!sections.length || !links.length || !('IntersectionObserver' in window)) return;

        const io = new IntersectionObserver((entries) => {
            entries.forEach((entry) => {
                if (!entry.isIntersecting) return;
                links.forEach((l) => {
                    l.classList.toggle('is-active', l.getAttribute('href') === '#' + entry.target.id);
                });
            });
        }, { rootMargin: '-45% 0px -50% 0px' });

        sections.forEach((s) => io.observe(s));
    });

    /* =================================================================
       26. KOD PENCERESİ — sözdizimi renklendirme (basit)
       ================================================================= */
    safe('highlight', function () {
        const pre = $('[data-highlight]');
        if (!pre) return;

        const raw = pre.textContent;
        const esc = (s) => s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');

        // Tek geçişli tokenizasyon — üretilen HTML'in yeniden işlenmesini önler
        const rx = /(\/\/[^\n]*)|('[^'\n]*'|"[^"\n]*")|\b(class|constructor|return|const|let|var|function|new|this|if|else|await|async|export|import|from|extends|typeof)\b|\b(\d+(?:\.\d+)?)\b|([A-Za-z_$][\w$]*)(?=\s*\()/g;

        const html2 = esc(raw).replace(rx, function (match, com, str, kw, num, fn) {
            if (com) return '<span class="tok-com">' + com + '</span>';
            if (str) return '<span class="tok-str">' + str + '</span>';
            if (kw)  return '<span class="tok-key">' + kw + '</span>';
            if (num) return '<span class="tok-num">' + num + '</span>';
            if (fn)  return '<span class="tok-fn">' + fn + '</span>';
            return match;
        });

        pre.innerHTML = html2
            .split('\n')
            .map((line) => '<span class="code-line">' + (line || ' ') + '</span>')
            .join('');
    });

    /* =================================================================
       27. TEKLİF FORMU — bütçe göstergesi
       ================================================================= */
    safe('budget-range', function () {
        const range = $('#budgetRange');
        const out = $('#budgetOut');
        if (!range || !out) return;

        const labels = [
            'Henüz netleşmedi', '15.000 – 30.000 ₺', '30.000 – 60.000 ₺',
            '60.000 – 120.000 ₺', '120.000 – 250.000 ₺', '250.000 ₺ ve üzeri'
        ];

        const render = () => {
            const i = clamp(parseInt(range.value, 10) || 0, 0, labels.length - 1);
            out.textContent = labels[i];
            range.setAttribute('aria-valuetext', labels[i]);
        };

        range.addEventListener('input', render);
        render();
    });

    /* =================================================================
       28. ÇOK ADIMLI FORM
       ================================================================= */
    safe('steps', function () {
        const form = $('[data-steps]');
        if (!form) return;

        const steps = $$('[data-step]', form);
        const dots = $$('[data-step-dot]', form);
        const nextBtn = $('[data-step-next]', form);
        const prevBtn = $('[data-step-prev]', form);
        const submitBtn = $('[data-step-submit]', form);
        if (!steps.length) return;

        let current = 0;

        function render() {
            steps.forEach((s, i) => { s.hidden = i !== current; });
            dots.forEach((d, i) => {
                d.classList.toggle('is-active', i === current);
                d.classList.toggle('is-done', i < current);
            });
            if (prevBtn) prevBtn.hidden = current === 0;
            if (nextBtn) nextBtn.hidden = current === steps.length - 1;
            if (submitBtn) submitBtn.hidden = current !== steps.length - 1;
        }

        function validateStep() {
            let ok = true;
            $$('[required]', steps[current]).forEach((f) => {
                if ((f.type === 'checkbox' && !f.checked) || (f.type !== 'checkbox' && !f.value.trim())) {
                    f.style.borderColor = '#ef4444';
                    ok = false;
                } else {
                    f.style.borderColor = '';
                }
            });
            return ok;
        }

        if (nextBtn) nextBtn.addEventListener('click', () => {
            if (!validateStep()) return;
            current = Math.min(current + 1, steps.length - 1);
            render();
            form.scrollIntoView({ block: 'start', behavior: prefersReduced ? 'auto' : 'smooth' });
        });

        if (prevBtn) prevBtn.addEventListener('click', () => {
            current = Math.max(current - 1, 0);
            render();
        });

        render();
    });

    /* =================================================================
       29. YIL BİLGİSİ / DİNAMİK METİNLER
       ================================================================= */
    safe('dynamic-text', function () {
        $$('[data-year]').forEach((el) => { el.textContent = new Date().getFullYear(); });
    });

    /* =================================================================
       30. KONSOL İMZASI
       ================================================================= */
    safe('signature', function () {
        if (!window.console || !console.log) return;
        console.log(
            '%c OXIT %c Bu siteyi mi beğendiniz? Benzerini sizin için de yapalım. ',
            'background:linear-gradient(120deg,#7c5cff,#22d3ee);color:#fff;padding:6px 10px;border-radius:6px 0 0 6px;font-weight:700',
            'background:#11111d;color:#b3b3c4;padding:6px 10px;border-radius:0 6px 6px 0'
        );
    });

})();
