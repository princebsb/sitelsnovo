/**
 * Google Analytics 4 com consentimento (LGPD).
 *
 * Nada é carregado antes do visitante aceitar: o script do Google só entra na
 * página depois do clique em "Aceitar". A escolha fica guardada no navegador e
 * pode ser revista pelo link "Preferências de cookies", no rodapé.
 */
(function () {
    'use strict';

    var MEDIDA = 'G-PRYMMXYSHY';
    var CHAVE = 'lsg-consentimento-analytics';
    var POLITICA = 'privacidade.html';

    var overlay;

    // ---------------------------------------------------------------- memória
    function lerEscolha() {
        try {
            return localStorage.getItem(CHAVE);
        } catch (e) {
            return null;                        // navegador bloqueia armazenamento
        }
    }

    function gravarEscolha(valor) {
        try {
            localStorage.setItem(CHAVE, valor);
            localStorage.setItem(CHAVE + '-data', new Date().toISOString());
        } catch (e) { /* sem armazenamento: vale só para esta visita */ }
    }

    // ---------------------------------------------------------------- Google
    function carregarAnalytics() {
        if (window.gtag || document.getElementById('ga-script')) return;

        window.dataLayer = window.dataLayer || [];
        window.gtag = function () { window.dataLayer.push(arguments); };

        gtag('consent', 'default', {
            ad_storage: 'denied',
            ad_user_data: 'denied',
            ad_personalization: 'denied',
            analytics_storage: 'granted'
        });

        var tag = document.createElement('script');
        tag.id = 'ga-script';
        tag.async = true;
        tag.src = 'https://www.googletagmanager.com/gtag/js?id=' + MEDIDA;
        document.head.appendChild(tag);

        gtag('js', new Date());
        gtag('config', MEDIDA, { anonymize_ip: true });
    }

    function apagarCookiesDoGoogle() {
        var dominio = location.hostname.replace(/^www\./, '');
        document.cookie.split(';').forEach(function (c) {
            var nome = c.split('=')[0].trim();
            if (nome.indexOf('_ga') !== 0) return;
            ['/', ''].forEach(function (caminho) {
                document.cookie = nome + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=' + (caminho || '/');
                document.cookie = nome + '=; expires=Thu, 01 Jan 1970 00:00:00 GMT; path=/; domain=.' + dominio;
            });
        });
    }

    // ---------------------------------------------------------------- banner
    function estilos() {
        if (document.getElementById('lsg-cookies-css')) return;
        var css = ''
            + '.lsg-cookies{position:fixed;left:0;right:0;bottom:0;z-index:1900;display:none;'
            + 'padding:16px;background:rgba(15,23,42,.55);backdrop-filter:blur(3px);}'
            + '.lsg-cookies.is-open{display:block;}'
            + '.lsg-cookies__caixa{max-width:1100px;margin:0 auto;display:flex;flex-wrap:wrap;'
            + 'align-items:center;justify-content:space-between;gap:20px;padding:22px 26px;'
            + 'background:#fff;border-radius:14px;box-shadow:0 20px 40px -12px rgba(0,0,0,.35);'
            + 'font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}'
            + '.lsg-cookies__texto{flex:1 1 420px;font-size:.875rem;line-height:1.7;color:#475569;margin:0;}'
            + '.lsg-cookies__texto strong{display:block;margin-bottom:4px;font-size:.9375rem;color:#0f172a;}'
            + '.lsg-cookies__texto a{color:#6366f1;font-weight:500;}'
            + '.lsg-cookies__acoes{display:flex;gap:10px;flex-shrink:0;}'
            + '.lsg-cookies__btn{padding:11px 20px;border:1px solid transparent;border-radius:10px;'
            + 'font-size:.875rem;font-weight:600;cursor:pointer;transition:.2s;font-family:inherit;}'
            + '.lsg-cookies__btn--sim{background:linear-gradient(135deg,#6366f1,#06b6d4);color:#fff;}'
            + '.lsg-cookies__btn--sim:hover{opacity:.9;}'
            + '.lsg-cookies__btn--nao{background:#f8fafc;border-color:#e2e8f0;color:#475569;}'
            + '.lsg-cookies__btn--nao:hover{background:#e2e8f0;}'
            + '@media(max-width:640px){.lsg-cookies__caixa{padding:20px;}'
            + '.lsg-cookies__acoes{width:100%;}.lsg-cookies__btn{flex:1;}}';

        var tag = document.createElement('style');
        tag.id = 'lsg-cookies-css';
        tag.textContent = css;
        document.head.appendChild(tag);
    }

    function montar() {
        estilos();
        overlay = document.createElement('div');
        overlay.className = 'lsg-cookies';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-label', 'Preferências de cookies');
        overlay.innerHTML = ''
            + '<div class="lsg-cookies__caixa">'
            + '  <p class="lsg-cookies__texto">'
            + '    <strong>Podemos usar cookies de análise?</strong>'
            + '    Eles nos ajudam a entender quais páginas são mais úteis para quem visita o site. '
            + '    Nenhum dado é usado para publicidade. Você pode recusar sem perder nenhuma funcionalidade — '
            + '    veja a <a href="' + POLITICA + '">Política de Privacidade</a>.'
            + '  </p>'
            + '  <div class="lsg-cookies__acoes">'
            + '    <button type="button" class="lsg-cookies__btn lsg-cookies__btn--nao">Recusar</button>'
            + '    <button type="button" class="lsg-cookies__btn lsg-cookies__btn--sim">Aceitar</button>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(overlay);

        overlay.querySelector('.lsg-cookies__btn--sim').addEventListener('click', function () {
            gravarEscolha('sim');
            fechar();
            carregarAnalytics();
        });

        overlay.querySelector('.lsg-cookies__btn--nao').addEventListener('click', function () {
            gravarEscolha('nao');
            fechar();
            apagarCookiesDoGoogle();
        });
    }

    function abrir() {
        if (!overlay) montar();
        overlay.classList.add('is-open');
    }

    function fechar() {
        if (overlay) overlay.classList.remove('is-open');
    }

    // ------------------------------------------- link no rodapé para rever
    function linkPreferencias() {
        var legal = document.querySelector('.footer-legal');
        if (!legal || legal.querySelector('.lsg-preferencias')) return;

        var a = document.createElement('a');
        a.href = '#';
        a.className = 'lsg-preferencias';
        a.textContent = 'Preferências de cookies';
        a.addEventListener('click', function (e) {
            e.preventDefault();
            abrir();
        });
        legal.appendChild(a);
    }

    function iniciar() {
        linkPreferencias();

        var escolha = lerEscolha();
        if (escolha === 'sim') {
            carregarAnalytics();
        } else if (escolha !== 'nao') {
            abrir();
        }
    }

    window.lsgPreferenciasCookies = abrir;

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
