/*!
 * 默认主题交互组件
 *
 * 覆盖新版业务模板需要的全部前端行为，纯原生实现，不依赖 jQuery：
 *   1. 图集灯箱      [data-mx-lightbox]        支持分组、左右切换、ESC 关闭
 *   2. 商品图切换    [data-mx-gallery]         缩略图点击切换主图
 *   3. 视频弹窗      [data-mx-video]           mp4 直链与 iframe 平台
 *   4. 问答折叠      [data-mx-accordion]       同组单开
 *   5. 图片轮播      [data-mx-slider]          自动切换 + 箭头 + 圆点
 *   6. 咨询按钮      [data-mx-inquiry]         滚动到页脚联系方式并提示
 *
 * 全部用事件委托绑定，短标签 / 异步插入的内容无需重新初始化。
 */
(function () {
    'use strict';

    var overlay = null;
    var body = null;
    var state = { mode: '', items: [], index: 0 };

    function ensureOverlay() {
        if (overlay) {
            return overlay;
        }

        overlay = document.createElement('div');
        overlay.className = 'mx-modal';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.innerHTML =
            '<button type="button" class="mx-modal__close" data-mx-close aria-label="关闭">&times;</button>' +
            '<button type="button" class="mx-modal__nav mx-modal__nav--prev" data-mx-prev aria-label="上一项">&#10094;</button>' +
            '<button type="button" class="mx-modal__nav mx-modal__nav--next" data-mx-next aria-label="下一项">&#10095;</button>' +
            '<div class="mx-modal__body"></div>';

        document.body.appendChild(overlay);
        body = overlay.querySelector('.mx-modal__body');

        overlay.addEventListener('click', function (event) {
            if (event.target === overlay || event.target.closest('[data-mx-close]')) {
                close();
                return;
            }
            if (event.target.closest('[data-mx-prev]')) {
                step(-1);
                return;
            }
            if (event.target.closest('[data-mx-next]')) {
                step(1);
            }
        });

        document.addEventListener('keydown', function (event) {
            if (!overlay.classList.contains('is-open')) {
                return;
            }
            if (event.key === 'Escape') {
                close();
            } else if (event.key === 'ArrowLeft') {
                step(-1);
            } else if (event.key === 'ArrowRight') {
                step(1);
            }
        });

        return overlay;
    }

    function open(mode, items, index) {
        if (!items.length) {
            return;
        }

        state.mode = mode;
        state.items = items;
        state.index = Math.max(0, Math.min(index || 0, items.length - 1));

        ensureOverlay();
        overlay.classList.add('is-open');
        overlay.classList.toggle('mx-video-modal', mode === 'video');
        overlay.classList.toggle('mx-video-modal--fluid', mode === 'video' && items[state.index].type === 'iframe');
        document.body.style.overflow = 'hidden';
        render();
    }

    function close() {
        if (!overlay) {
            return;
        }
        overlay.classList.remove('is-open');
        document.body.style.overflow = '';
        body.innerHTML = '';
        state = { mode: '', items: [], index: 0 };
    }

    function step(delta) {
        if (state.items.length < 2) {
            return;
        }
        state.index = (state.index + delta + state.items.length) % state.items.length;
        render();
    }

    function render() {
        var item = state.items[state.index];
        if (!item) {
            return;
        }

        if (state.mode === 'video') {
            body.innerHTML = item.type === 'iframe'
                ? '<iframe class="mx-video-modal__frame" src="' + encodeURI(item.src) + '" allowfullscreen frameborder="0"></iframe>'
                : '<video class="mx-video-modal__frame" controls autoplay playsinline src="' + encodeURI(item.src) + '"></video>';
        } else {
            body.innerHTML =
                '<figure class="mx-lightbox__figure">' +
                '<img class="mx-lightbox__image" src="' + encodeURI(item.src) + '" alt="">' +
                (item.caption ? '<figcaption class="mx-lightbox__caption">' + escapeHtml(item.caption) + '</figcaption>' : '') +
                '</figure>';
        }

        toggleNav(state.items.length > 1);
    }

    function toggleNav(visible) {
        var prev = overlay.querySelector('[data-mx-prev]');
        var next = overlay.querySelector('[data-mx-next]');
        if (prev) {
            prev.style.display = visible ? '' : 'none';
        }
        if (next) {
            next.style.display = visible ? '' : 'none';
        }
    }

    function escapeHtml(value) {
        return String(value || '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;');
    }

    /* ---- 1. 图集灯箱 ---------------------------------------- */
    function lightboxItems(trigger) {
        var scope = trigger.closest('[data-mx-lightbox-group]') || document;
        return Array.prototype.map.call(
            scope.querySelectorAll('[data-mx-lightbox]'),
            function (node) {
                return { src: node.getAttribute('href') || node.getAttribute('data-src') || '', caption: node.getAttribute('data-caption') || '' };
            }
        ).filter(function (item) {
            return item.src !== '';
        });
    }

    /* ---- 2. 商品图切换 -------------------------------------- */
    function bindGallery(root) {
        var main = root.querySelector('[data-mx-gallery-main]');
        var thumbs = root.querySelectorAll('[data-mx-gallery-thumb]');
        var stage = root.querySelector('[data-mx-lightbox]');

        Array.prototype.forEach.call(thumbs, function (thumb) {
            thumb.addEventListener('click', function () {
                var src = thumb.getAttribute('data-mx-gallery-thumb');
                if (!src) {
                    return;
                }
                if (main) {
                    main.setAttribute('src', src);
                }
                if (stage) {
                    stage.setAttribute('href', src);
                }
                Array.prototype.forEach.call(thumbs, function (node) {
                    node.classList.remove('is-active');
                });
                thumb.classList.add('is-active');
            });
        });
    }

    /* ---- 4. 问答折叠 ---------------------------------------- */
    function bindAccordion(root) {
        Array.prototype.forEach.call(root.querySelectorAll('[data-mx-accordion-trigger]'), function (trigger) {
            trigger.addEventListener('click', function () {
                var item = trigger.closest('.mx-faq__item') || trigger.parentElement;
                if (!item) {
                    return;
                }

                var panel = item.querySelector('.mx-faq__answer');
                var willOpen = !item.classList.contains('is-open');

                Array.prototype.forEach.call(root.querySelectorAll('.mx-faq__item.is-open'), function (opened) {
                    if (opened === item) {
                        return;
                    }
                    opened.classList.remove('is-open');
                    var openedPanel = opened.querySelector('.mx-faq__answer');
                    if (openedPanel) {
                        openedPanel.hidden = true;
                    }
                });

                item.classList.toggle('is-open', willOpen);
                if (panel) {
                    panel.hidden = !willOpen;
                }
            });
        });
    }

    /* ---- 5. 图片轮播 {mx:slider} -------------------------------- */
    function bindSlider(root) {
        var slides = root.querySelectorAll('.mx-slider__slide');
        if (slides.length < 2) {
            return;
        }

        var dots = root.querySelectorAll('[data-mx-slider-dot]');
        var index = 0;
        var timer = null;
        var interval = parseInt(root.getAttribute('data-interval'), 10) || 0;

        function show(next) {
            index = (next + slides.length) % slides.length;
            Array.prototype.forEach.call(slides, function (slide, i) {
                slide.classList.toggle('is-active', i === index);
            });
            Array.prototype.forEach.call(dots, function (dot, i) {
                dot.classList.toggle('is-active', i === index);
            });
        }

        function start() {
            if (interval > 0 && !timer) {
                timer = window.setInterval(function () {
                    show(index + 1);
                }, interval);
            }
        }

        function stop() {
            if (timer) {
                window.clearInterval(timer);
                timer = null;
            }
        }

        var prev = root.querySelector('[data-mx-slider-prev]');
        var next = root.querySelector('[data-mx-slider-next]');
        if (prev) {
            prev.addEventListener('click', function () {
                show(index - 1);
                stop();
                start();
            });
        }
        if (next) {
            next.addEventListener('click', function () {
                show(index + 1);
                stop();
                start();
            });
        }
        Array.prototype.forEach.call(dots, function (dot, i) {
            dot.addEventListener('click', function () {
                show(i);
                stop();
                start();
            });
        });

        root.addEventListener('mouseenter', stop);
        root.addEventListener('mouseleave', start);
        start();
    }

    /* ---- 6. 咨询按钮 ---------------------------------------- */
    function handleInquiry() {
        var target = document.querySelector('.mx-home-contact-section, .footer, footer');
        if (target && typeof target.scrollIntoView === 'function') {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (window.layer && typeof window.layer.msg === 'function') {
            window.layer.msg('已定位到联系方式，您也可以直接拨打页面上的联系电话。');
        }
    }

    /* ---- 统一入口：事件委托 ---------------------------------- */
    document.addEventListener('click', function (event) {
        var videoTrigger = event.target.closest('[data-mx-video]');
        if (videoTrigger && !videoTrigger.disabled) {
            event.preventDefault();
            var src = videoTrigger.getAttribute('data-video-src') || '';
            if (src !== '') {
                open('video', [{
                    src: src,
                    type: videoTrigger.getAttribute('data-video-type') || 'mp4',
                    title: videoTrigger.getAttribute('data-video-title') || ''
                }], 0);
            }
            return;
        }

        if (event.target.closest('[data-mx-inquiry]')) {
            event.preventDefault();
            handleInquiry();
            return;
        }

        var lightboxTrigger = event.target.closest('[data-mx-lightbox]');
        if (lightboxTrigger) {
            event.preventDefault();
            var items = lightboxItems(lightboxTrigger);
            var current = lightboxTrigger.getAttribute('href') || '';
            var index = 0;
            for (var i = 0; i < items.length; i += 1) {
                if (items[i].src === current) {
                    index = i;
                    break;
                }
            }
            open('lightbox', items, index);
        }
    });

    function init() {
        Array.prototype.forEach.call(document.querySelectorAll('[data-mx-gallery]'), bindGallery);
        Array.prototype.forEach.call(document.querySelectorAll('[data-mx-accordion]'), bindAccordion);
        Array.prototype.forEach.call(document.querySelectorAll('[data-mx-slider]'), bindSlider);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    window.mxThemeComponents = { open: open, close: close };
}());
