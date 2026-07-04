// Indicador de carregamento global.
// Quando a pessoa submete um formulário ou clica num link interno, mostro
// imediatamente uma barra/spinner — assim, mesmo que o servidor demore
// (ex: primeiro acesso "a frio" no alojamento), nunca fica um ecrã parado
// sem feedback nenhum.
(function () {
    'use strict';

    // Crio o overlay uma única vez.
    var overlay = document.createElement('div');
    overlay.id = 'rb-loading';
    overlay.setAttribute('aria-hidden', 'true');
    overlay.innerHTML =
        '<div class="rb-loading-box">' +
        '<div class="rb-loading-spinner"></div>' +
        '<div class="rb-loading-text">A carregar…</div>' +
        '</div>';

    var style = document.createElement('style');
    style.textContent =
        '#rb-loading{position:fixed;inset:0;background:rgba(10,15,25,.55);backdrop-filter:blur(2px);' +
        'display:none;align-items:center;justify-content:center;z-index:99999;}' +
        '#rb-loading.rb-on{display:flex;}' +
        '.rb-loading-box{background:#fff;border-radius:14px;padding:22px 30px;text-align:center;' +
        'box-shadow:0 10px 40px rgba(0,0,0,.25);font-family:Arial,Helvetica,sans-serif;}' +
        '.rb-loading-spinner{width:34px;height:34px;margin:0 auto 10px;border:4px solid #e3e8ef;' +
        'border-top-color:#1a73e8;border-radius:50%;animation:rbspin .8s linear infinite;}' +
        '.rb-loading-text{font-size:14px;color:#3c4043;}' +
        '@keyframes rbspin{to{transform:rotate(360deg)}}';

    function ready(fn) {
        if (document.readyState !== 'loading') { fn(); }
        else { document.addEventListener('DOMContentLoaded', fn); }
    }

    function mostrar() {
        overlay.classList.add('rb-on');
    }

    ready(function () {
        document.head.appendChild(style);
        document.body.appendChild(overlay);

        // Formulários: mostro o spinner ao submeter (upload de fotos pode demorar).
        document.addEventListener('submit', function (e) {
            var form = e.target;
            if (form && !form.hasAttribute('data-no-loading')) {
                mostrar();
            }
        }, true);

        // Links internos: mostro ao navegar para outra página do site.
        document.addEventListener('click', function (e) {
            var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
            if (!a) return;
            var href = a.getAttribute('href') || '';
            // Ignoro âncoras, novos separadores, downloads, mailto e sites externos.
            if (href.startsWith('#') || href.startsWith('mailto:') || href.startsWith('tel:')) return;
            if (a.target === '_blank' || a.hasAttribute('download') || a.hasAttribute('data-no-loading')) return;
            if (a.origin && a.origin !== window.location.origin) return;
            if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
            mostrar();
        }, true);

        // Se a pessoa voltar atrás no browser, escondo o overlay (páginas em cache).
        window.addEventListener('pageshow', function () {
            overlay.classList.remove('rb-on');
        });
    });
})();
