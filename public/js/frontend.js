/* Agint Pricing Cards — dependency-free carousel.
 *
 * Page-based slider: advances one "view" (slides-per-view) at a time, with
 * responsive slides-per-view, arrows, dots, loop, autoplay and drag/swipe.
 * Supports multiple carousels on a page.
 */
(function () {
    'use strict';

    function clamp(n, min, max) {
        return Math.max(min, Math.min(max, n));
    }

    function Carousel(root) {
        this.root     = root;
        this.viewport = root.querySelector('.apc-carousel__viewport');
        this.track    = root.querySelector('.apc-carousel__track');
        this.slides   = Array.prototype.slice.call(root.querySelectorAll('.apc-slide'));
        this.prevBtn  = root.querySelector('.apc-carousel__prev');
        this.nextBtn  = root.querySelector('.apc-carousel__next');
        this.dotsWrap = root.querySelector('.apc-carousel__dots');

        this.columns  = parseInt(root.getAttribute('data-columns'), 10) || 3;
        this.loop     = root.getAttribute('data-loop') === '1';
        this.autoplay = root.getAttribute('data-autoplay') === '1';
        this.speed    = parseInt(root.getAttribute('data-speed'), 10) || 4000;

        this.page      = 0;
        this.perView   = this.columns;
        this.pages     = 1;
        this.autoTimer = null;

        if (!this.track || !this.slides.length) { return; }

        this.bind();
        this.layout();
        this.startAuto();
    }

    Carousel.prototype.computePerView = function () {
        var w = window.innerWidth;
        if (w <= 700) { return 1; }
        if (w <= 1024) { return Math.min(2, this.columns); }
        return this.columns;
    };

    Carousel.prototype.layout = function () {
        this.perView = this.computePerView();
        this.pages   = Math.max(1, Math.ceil(this.slides.length / this.perView));
        this.page    = clamp(this.page, 0, this.pages - 1);

        var pct = (100 / this.perView) + '%';
        this.slides.forEach(function (s) { s.style.width = pct; });

        this.buildDots();
        this.update(false);
    };

    Carousel.prototype.maxScroll = function () {
        return Math.max(0, this.track.scrollWidth - this.viewport.clientWidth);
    };

    Carousel.prototype.offsetForPage = function (page) {
        var vw = this.viewport.clientWidth;
        return Math.min(page * vw, this.maxScroll());
    };

    Carousel.prototype.update = function (animate) {
        if (animate === false) { this.track.style.transition = 'none'; }
        var x = this.offsetForPage(this.page);
        this.track.style.transform = 'translateX(' + (-x) + 'px)';
        if (animate === false) {
            // force reflow then restore transition
            void this.track.offsetWidth;
            this.track.style.transition = '';
        }

        // Dots
        if (this.dotsWrap) {
            var dots = this.dotsWrap.children;
            for (var i = 0; i < dots.length; i++) {
                dots[i].classList.toggle('is-active', i === this.page);
            }
        }

        // Arrows (only disable when not looping)
        var single = this.pages <= 1;
        if (this.prevBtn) {
            this.prevBtn.disabled = single || (!this.loop && this.page === 0);
        }
        if (this.nextBtn) {
            this.nextBtn.disabled = single || (!this.loop && this.page === this.pages - 1);
        }

        // Hide the whole nav row if there's only one page
        var nav = this.root.querySelector('.apc-carousel__nav');
        if (nav) { nav.style.display = single ? 'none' : ''; }
    };

    Carousel.prototype.goTo = function (page) {
        if (this.pages <= 1) { this.page = 0; this.update(true); return; }
        if (this.loop) {
            page = (page + this.pages) % this.pages;
        } else {
            page = clamp(page, 0, this.pages - 1);
        }
        this.page = page;
        this.update(true);
    };

    Carousel.prototype.next = function () { this.goTo(this.page + 1); };
    Carousel.prototype.prev = function () { this.goTo(this.page - 1); };

    Carousel.prototype.buildDots = function () {
        if (!this.dotsWrap) { return; }
        this.dotsWrap.innerHTML = '';
        var self = this;
        for (var i = 0; i < this.pages; i++) {
            (function (idx) {
                var b = document.createElement('button');
                b.type = 'button';
                b.className = 'apc-carousel__dot' + (idx === self.page ? ' is-active' : '');
                b.setAttribute('aria-label', 'Go to slide ' + (idx + 1));
                b.addEventListener('click', function () { self.stopAuto(); self.goTo(idx); });
                self.dotsWrap.appendChild(b);
            })(i);
        }
    };

    Carousel.prototype.startAuto = function () {
        if (!this.autoplay || this.pages <= 1) { return; }
        this.stopAuto();
        var self = this;
        this.autoTimer = window.setInterval(function () { self.next(); }, this.speed);
    };
    Carousel.prototype.stopAuto = function () {
        if (this.autoTimer) { window.clearInterval(this.autoTimer); this.autoTimer = null; }
    };

    Carousel.prototype.bind = function () {
        var self = this;

        if (this.prevBtn) {
            this.prevBtn.addEventListener('click', function () { self.stopAuto(); self.prev(); });
        }
        if (this.nextBtn) {
            this.nextBtn.addEventListener('click', function () { self.stopAuto(); self.next(); });
        }

        // Pause autoplay while hovering
        this.root.addEventListener('mouseenter', function () { self.stopAuto(); });
        this.root.addEventListener('mouseleave', function () { self.startAuto(); });

        // Resize (debounced)
        var rt;
        window.addEventListener('resize', function () {
            window.clearTimeout(rt);
            rt = window.setTimeout(function () { self.layout(); }, 150);
        });

        // Drag / swipe via pointer events
        var startX = 0, baseX = 0, dragging = false, moved = 0;

        var onDown = function (e) {
            dragging = true; moved = 0;
            startX = (e.touches ? e.touches[0].clientX : e.clientX);
            baseX  = self.offsetForPage(self.page);
            self.track.classList.add('is-dragging');
            self.stopAuto();
        };
        var onMove = function (e) {
            if (!dragging) { return; }
            var x = (e.touches ? e.touches[0].clientX : e.clientX);
            moved = x - startX;
            self.track.style.transform = 'translateX(' + (-(baseX - moved)) + 'px)';
            if (e.cancelable && Math.abs(moved) > 8) { e.preventDefault(); }
        };
        var onUp = function () {
            if (!dragging) { return; }
            dragging = false;
            self.track.classList.remove('is-dragging');
            var threshold = Math.min(120, self.viewport.clientWidth * 0.15);
            if (moved <= -threshold) { self.next(); }
            else if (moved >= threshold) { self.prev(); }
            else { self.update(true); }
        };

        this.track.addEventListener('mousedown', onDown);
        window.addEventListener('mousemove', onMove);
        window.addEventListener('mouseup', onUp);
        this.track.addEventListener('touchstart', onDown, { passive: true });
        this.track.addEventListener('touchmove', onMove, { passive: false });
        this.track.addEventListener('touchend', onUp);

        // Prevent accidental link clicks after a drag
        this.track.addEventListener('click', function (e) {
            if (Math.abs(moved) > 8) { e.preventDefault(); }
        }, true);
    };

    function init() {
        var nodes = document.querySelectorAll('.apc-carousel');
        Array.prototype.forEach.call(nodes, function (n) {
            if (!n.__apcInit) { n.__apcInit = true; new Carousel(n); }
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
