/**
 * Abre a Política de Privacidade e os Termos de Uso em um modal,
 * carregando o conteúdo das próprias páginas (que continuam acessíveis
 * por link direto e para os buscadores).
 */
(function () {
    'use strict';

    var PAGINAS = ['privacidade.html', 'termos.html'];
    var overlay, painel, titulo, corpo, botaoFechar, ultimoFoco;

    function estilos() {
        var css = ''
            + '.legal-modal{position:fixed;inset:0;z-index:2000;display:none;align-items:center;'
            + 'justify-content:center;padding:24px;background:rgba(15,23,42,.6);backdrop-filter:blur(4px);}'
            + '.legal-modal.is-open{display:flex;}'
            + '.legal-modal__panel{position:relative;display:flex;flex-direction:column;width:100%;'
            + 'max-width:820px;max-height:88vh;background:#fff;color:#0f172a;border-radius:16px;'
            + 'box-shadow:0 25px 50px -12px rgba(0,0,0,.35);overflow:hidden;'
            + 'font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;}'
            + '.legal-modal__head{display:flex;align-items:flex-start;justify-content:space-between;'
            + 'gap:16px;padding:24px 28px;border-bottom:1px solid #e2e8f0;}'
            + '.legal-modal__head h2{margin:0;font-size:1.25rem;font-weight:700;color:#0f172a;}'
            + '.legal-modal__head p{margin:6px 0 0;font-size:.8125rem;color:#64748b;}'
            + '.legal-modal__close{flex-shrink:0;width:36px;height:36px;display:flex;align-items:center;'
            + 'justify-content:center;border:1px solid #e2e8f0;border-radius:10px;background:#f8fafc;'
            + 'color:#475569;cursor:pointer;transition:.2s;}'
            + '.legal-modal__close:hover{background:#6366f1;border-color:#6366f1;color:#fff;}'
            + '.legal-modal__close svg{width:18px;height:18px;}'
            + '.legal-modal__body{padding:8px 28px 28px;overflow-y:auto;}'
            + '.legal-modal__body h2{font-size:1.125rem;font-weight:700;color:#0f172a;margin:32px 0 12px;'
            + 'padding-top:20px;border-top:1px solid #e2e8f0;}'
            + '.legal-modal__body h2:first-child{border-top:none;padding-top:0;margin-top:8px;}'
            + '.legal-modal__body h3{font-size:1rem;font-weight:600;color:#0f172a;margin:22px 0 10px;}'
            + '.legal-modal__body p{font-size:.9375rem;line-height:1.8;color:#475569;margin:0 0 14px;}'
            + '.legal-modal__body ul{list-style:none;margin:0 0 20px;padding:0;}'
            + '.legal-modal__body li{position:relative;padding-left:22px;margin-bottom:10px;'
            + 'font-size:.9375rem;line-height:1.7;color:#475569;}'
            + '.legal-modal__body li::before{content:"";position:absolute;left:4px;top:10px;width:6px;'
            + 'height:6px;border-radius:50%;background:#6366f1;}'
            + '.legal-modal__body strong{color:#0f172a;font-weight:600;}'
            + '.legal-modal__body a{color:#6366f1;font-weight:500;}'
            + '.legal-modal__body .legal-box{background:#f8fafc;border:1px solid #e2e8f0;'
            + 'border-radius:12px;padding:20px;margin:20px 0;}'
            + '.legal-modal__body .legal-box p{margin-bottom:6px;}'
            + '.legal-modal__foot{padding:16px 28px;border-top:1px solid #e2e8f0;background:#f8fafc;'
            + 'font-size:.8125rem;}'
            + '.legal-modal__foot a{color:#6366f1;font-weight:500;}'
            + '.legal-modal__carregando{padding:48px 0;text-align:center;color:#64748b;font-size:.9375rem;}'
            + 'body.legal-modal-aberto{overflow:hidden;}'
            + '@media(max-width:640px){.legal-modal{padding:0;}'
            + '.legal-modal__panel{max-width:none;max-height:100vh;height:100%;border-radius:0;}'
            + '.legal-modal__head,.legal-modal__body,.legal-modal__foot{padding-left:20px;padding-right:20px;}}';

        var tag = document.createElement('style');
        tag.textContent = css;
        document.head.appendChild(tag);
    }

    function montar() {
        overlay = document.createElement('div');
        overlay.className = 'legal-modal';
        overlay.setAttribute('role', 'dialog');
        overlay.setAttribute('aria-modal', 'true');
        overlay.setAttribute('aria-labelledby', 'legal-modal-titulo');
        overlay.innerHTML = ''
            + '<div class="legal-modal__panel">'
            + '  <div class="legal-modal__head">'
            + '    <div>'
            + '      <h2 id="legal-modal-titulo"></h2>'
            + '      <p class="legal-modal__meta"></p>'
            + '    </div>'
            + '    <button type="button" class="legal-modal__close" aria-label="Fechar">'
            + '      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">'
            + '        <path d="M18 6L6 18M6 6l12 12"/></svg>'
            + '    </button>'
            + '  </div>'
            + '  <div class="legal-modal__body"></div>'
            + '  <div class="legal-modal__foot">'
            + '    <a class="legal-modal__link" href="#" target="_blank" rel="noopener">Abrir a página completa</a>'
            + '  </div>'
            + '</div>';
        document.body.appendChild(overlay);

        painel = overlay.querySelector('.legal-modal__panel');
        titulo = overlay.querySelector('#legal-modal-titulo');
        corpo = overlay.querySelector('.legal-modal__body');
        botaoFechar = overlay.querySelector('.legal-modal__close');

        botaoFechar.addEventListener('click', fechar);
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay) fechar();
        });
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && overlay.classList.contains('is-open')) fechar();
        });
    }

    function abrir(url, rotulo) {
        ultimoFoco = document.activeElement;
        titulo.textContent = rotulo;
        overlay.querySelector('.legal-modal__meta').textContent = '';
        corpo.innerHTML = '<p class="legal-modal__carregando">Carregando...</p>';
        overlay.querySelector('.legal-modal__link').href = url;
        overlay.classList.add('is-open');
        document.body.classList.add('legal-modal-aberto');
        botaoFechar.focus();

        fetch(url)
            .then(function (r) {
                if (!r.ok) throw new Error(r.status);
                return r.text();
            })
            .then(function (html) {
                var doc = new DOMParser().parseFromString(html, 'text/html');
                var conteudo = doc.querySelector('.legal-content');
                var h1 = doc.querySelector('.legal-hero h1');
                var meta = doc.querySelector('.legal-meta');
                if (!conteudo) throw new Error('conteudo');
                if (h1) titulo.textContent = h1.textContent.trim();
                if (meta) overlay.querySelector('.legal-modal__meta').textContent = meta.textContent.trim();
                corpo.innerHTML = conteudo.innerHTML;
                corpo.scrollTop = 0;
            })
            .catch(function () {
                window.location.href = url;
            });
    }

    function fechar() {
        overlay.classList.remove('is-open');
        document.body.classList.remove('legal-modal-aberto');
        corpo.innerHTML = '';
        if (ultimoFoco && ultimoFoco.focus) ultimoFoco.focus();
    }

    function alvoLegal(link) {
        var href = link.getAttribute('href') || '';
        for (var i = 0; i < PAGINAS.length; i++) {
            if (href === PAGINAS[i] || href.indexOf('/' + PAGINAS[i]) !== -1) {
                // na própria página, o link continua navegando normalmente
                if (window.location.pathname.indexOf(PAGINAS[i]) !== -1) return null;
                return PAGINAS[i];
            }
        }
        return null;
    }

    function iniciar() {
        if (!window.fetch || !window.DOMParser) return;   // navegador antigo: links normais
        estilos();
        montar();

        document.addEventListener('click', function (e) {
            var link = e.target.closest ? e.target.closest('a') : null;
            if (!link) return;
            if (e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) return;
            if (link.target === '_blank') return;
            var pagina = alvoLegal(link);
            if (!pagina) return;
            e.preventDefault();
            abrir(pagina, link.textContent.trim());
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', iniciar);
    } else {
        iniciar();
    }
})();
