<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        :root {
            --color-bg: #f8fafc;
            --color-white: #ffffff;
            --color-primary: #6366f1;
            --color-primary-dark: #4f46e5;
            --color-secondary: #06b6d4;
            --color-text: #1e293b;
            --color-text-light: #64748b;
            --color-border: #e2e8f0;
            --color-success: #22c55e;
            --color-warning: #f59e0b;
            --color-error: #ef4444;
            --color-whatsapp: #25D366;
            --gradient-primary: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%);
            --shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
            --shadow-lg: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: var(--color-bg);
            color: var(--color-text);
            line-height: 1.6;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        /* Header */
        .page-header {
            text-align: center;
            margin-bottom: 40px;
        }

        .logo {
            margin-bottom: 24px;
        }

        .logo img {
            height: 50px;
        }

        .success-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 20px;
            background: rgba(34, 197, 94, 0.1);
            color: var(--color-success);
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
            margin-bottom: 16px;
        }

        .page-title {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 8px;
        }

        .page-subtitle {
            color: var(--color-text-light);
        }

        /* Result Card */
        .result-card {
            background: var(--color-white);
            border-radius: 16px;
            overflow: hidden;
            box-shadow: var(--shadow-lg);
            margin-bottom: 24px;
        }

        .result-header {
            background: var(--gradient-primary);
            color: white;
            padding: 32px;
            text-align: center;
        }

        .result-header h2 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .project-type {
            opacity: 0.9;
        }

        .result-body {
            padding: 32px;
        }

        /* Price Box */
        .price-box {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
            border-radius: 12px;
            padding: 32px;
            text-align: center;
            margin-bottom: 32px;
        }

        .price-label {
            font-size: 0.875rem;
            color: var(--color-text-light);
            margin-bottom: 8px;
        }

        .price-value {
            font-size: 2.5rem;
            font-weight: 800;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 16px;
        }

        .price-details {
            display: flex;
            justify-content: center;
            gap: 32px;
            flex-wrap: wrap;
        }

        .price-detail {
            text-align: center;
        }

        .price-detail-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-text);
        }

        .price-detail-label {
            font-size: 0.8125rem;
            color: var(--color-text-light);
        }

        /* Sections */
        .section {
            margin-bottom: 32px;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 16px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--color-border);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .section-title svg {
            color: var(--color-primary);
        }

        /* Description */
        .description-box {
            background: var(--color-bg);
            border-radius: 8px;
            padding: 20px;
            color: var(--color-text-light);
        }

        /* Features List */
        .features-list {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
        }

        .feature-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px;
            background: var(--color-bg);
            border-radius: 8px;
        }

        .feature-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(34, 197, 94, 0.1);
            color: var(--color-success);
            border-radius: 50%;
            flex-shrink: 0;
        }

        .feature-name {
            font-weight: 500;
            font-size: 0.9375rem;
        }

        .feature-desc {
            font-size: 0.8125rem;
            color: var(--color-text-light);
        }

        /* Complexity Badge */
        .complexity-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
        }

        .complexity-baixa {
            background: rgba(34, 197, 94, 0.1);
            color: var(--color-success);
        }

        .complexity-media {
            background: rgba(245, 158, 11, 0.1);
            color: var(--color-warning);
        }

        .complexity-alta {
            background: rgba(239, 68, 68, 0.1);
            color: var(--color-error);
        }

        /* Suggestions */
        .suggestions-list {
            list-style: none;
        }

        .suggestion-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
            padding: 12px 0;
            border-bottom: 1px solid var(--color-border);
        }

        .suggestion-item:last-child {
            border-bottom: none;
        }

        .suggestion-icon {
            color: var(--color-secondary);
            flex-shrink: 0;
        }

        /* Technologies */
        .tech-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .tech-tag {
            padding: 6px 14px;
            background: var(--color-bg);
            border-radius: 50px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        /* Disclaimer */
        .disclaimer {
            background: rgba(245, 158, 11, 0.1);
            border-left: 4px solid var(--color-warning);
            padding: 16px 20px;
            border-radius: 0 8px 8px 0;
            font-size: 0.875rem;
            color: var(--color-text-light);
        }

        /* CTA Section */
        .cta-section {
            background: var(--color-white);
            border-radius: 16px;
            padding: 32px;
            text-align: center;
            box-shadow: var(--shadow-lg);
            margin-bottom: 24px;
        }

        .cta-title {
            font-size: 1.25rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .cta-subtitle {
            color: var(--color-text-light);
            margin-bottom: 24px;
        }

        .cta-buttons {
            display: flex;
            justify-content: center;
            gap: 16px;
            flex-wrap: wrap;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 14px 28px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .btn-whatsapp {
            background: var(--color-whatsapp);
            color: white;
        }

        .btn-whatsapp:hover {
            background: #20bd5a;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid var(--color-border);
            color: var(--color-text);
        }

        .btn-outline:hover {
            border-color: var(--color-primary);
            color: var(--color-primary);
        }

        .btn svg {
            width: 20px;
            height: 20px;
        }

        /* Share Section */
        .share-section {
            text-align: center;
            padding: 24px;
            border-top: 1px solid var(--color-border);
        }

        .share-section p {
            font-size: 0.875rem;
            color: var(--color-text-light);
            margin-bottom: 12px;
        }

        .share-buttons {
            display: flex;
            justify-content: center;
            gap: 12px;
        }

        .share-btn {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            color: white;
            text-decoration: none;
            transition: transform 0.3s ease;
        }

        .share-btn:hover {
            transform: scale(1.1);
        }

        .share-btn.whatsapp { background: #25D366; }
        .share-btn.email { background: #64748b; }
        .share-btn.copy { background: var(--color-primary); cursor: pointer; }

        /* Footer */
        .page-footer {
            text-align: center;
            padding-top: 24px;
        }

        .page-footer a {
            color: var(--color-primary);
            text-decoration: none;
        }

        .page-footer a:hover {
            text-decoration: underline;
        }

        /* Print Styles */
        @media print {
            body { background: white; }
            .cta-section, .share-section, .page-footer { display: none; }
            .result-card { box-shadow: none; border: 1px solid var(--color-border); }
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 24px 16px;
            }

            .page-title {
                font-size: 1.5rem;
            }

            .price-value {
                font-size: 2rem;
            }

            .price-details {
                flex-direction: column;
                gap: 16px;
            }

            .features-list {
                grid-template-columns: 1fr;
            }

            .cta-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="page-header">
            <div class="logo">
                <a href="<?= site_url() ?>">
                    <img src="<?= base_url('logo.svg') ?>" alt="LS Global Tecnologia">
                </a>
            </div>
            <div class="success-badge">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/>
                    <polyline points="22 4 12 14.01 9 11.01"/>
                </svg>
                Orçamento gerado com sucesso!
            </div>
            <h1 class="page-title">Olá, <?= esc($orcamento['nome']) ?>!</h1>
            <p class="page-subtitle">Confira abaixo a estimativa do seu projeto</p>
        </header>

        <!-- Result Card -->
        <div class="result-card">
            <div class="result-header">
                <h2>Pré-Orçamento #<?= str_pad($orcamento['id'], 5, '0', STR_PAD_LEFT) ?></h2>
                <p class="project-type"><?= esc($tipo_texto) ?></p>
            </div>

            <div class="result-body">
                <!-- Price Box -->
                <div class="price-box">
                    <p class="price-label">Faixa estimada de investimento</p>
                    <p class="price-value"><?= esc($valor_texto) ?></p>
                    <div class="price-details">
                        <div class="price-detail">
                            <p class="price-detail-value"><?= esc($prazo_texto) ?></p>
                            <p class="price-detail-label">Prazo estimado</p>
                        </div>
                        <div class="price-detail">
                            <p class="price-detail-value"><?= esc($orcamento['horas_estimadas']) ?>h</p>
                            <p class="price-detail-label">Horas de desenvolvimento</p>
                        </div>
                        <div class="price-detail">
                            <p class="price-detail-value complexity-badge complexity-<?= esc($orcamento['complexidade']) ?>">
                                <?= ucfirst(esc($orcamento['complexidade'])) ?>
                            </p>
                            <p class="price-detail-label">Complexidade</p>
                        </div>
                    </div>
                </div>

                <!-- Description -->
                <?php if (!empty($orcamento['descricao_melhorada'])): ?>
                <div class="section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/>
                            <line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        Resumo do Projeto
                    </h3>
                    <div class="description-box">
                        <?= esc($orcamento['descricao_melhorada']) ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Features -->
                <?php if (!empty($orcamento['funcionalidades'])): ?>
                <div class="section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 11 12 14 22 4"/>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/>
                        </svg>
                        Funcionalidades Detectadas
                    </h3>
                    <div class="features-list">
                        <?php foreach ($orcamento['funcionalidades'] as $func): ?>
                        <div class="feature-item">
                            <span class="feature-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <div>
                                <p class="feature-name"><?= esc($func['nome'] ?? '') ?></p>
                                <?php if (!empty($func['descricao'])): ?>
                                <p class="feature-desc"><?= esc($func['descricao']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Integrations -->
                <?php if (!empty($orcamento['integracoes'])): ?>
                <div class="section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="3"/>
                            <path d="M12 1v6m0 6v10M21 12h-6m-6 0H1"/>
                        </svg>
                        Integrações Necessárias
                    </h3>
                    <div class="features-list">
                        <?php foreach ($orcamento['integracoes'] as $integracao): ?>
                        <div class="feature-item">
                            <span class="feature-icon">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </span>
                            <div>
                                <p class="feature-name"><?= esc($integracao['nome'] ?? '') ?></p>
                                <?php if (!empty($integracao['descricao'])): ?>
                                <p class="feature-desc"><?= esc($integracao['descricao']) ?></p>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Suggestions -->
                <?php if (!empty($orcamento['sugestoes'])): ?>
                <div class="section">
                    <h3 class="section-title">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        Sugestões de Melhoria
                    </h3>
                    <ul class="suggestions-list">
                        <?php foreach ($orcamento['sugestoes'] as $sugestao): ?>
                        <li class="suggestion-item">
                            <span class="suggestion-icon">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M9 18l6-6-6-6"/>
                                </svg>
                            </span>
                            <span><?= esc($sugestao) ?></span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <!-- Disclaimer -->
                <div class="disclaimer">
                    <strong>Importante:</strong> Esta é uma estimativa inicial automática baseada na descrição fornecida. O valor final depende de uma análise detalhada do projeto. Entre em contato para uma proposta personalizada.
                </div>
            </div>
        </div>

        <!-- CTA Section -->
        <div class="cta-section">
            <h2 class="cta-title">Gostou da estimativa?</h2>
            <p class="cta-subtitle">Converse com nossa equipe para detalhar seu projeto</p>
            <div class="cta-buttons">
                <a href="https://wa.me/5511999999999?text=Olá! Vi o pré-orçamento #<?= str_pad($orcamento['id'], 5, '0', STR_PAD_LEFT) ?> e gostaria de mais informações." class="btn btn-whatsapp" target="_blank" rel="noopener">
                    <svg viewBox="0 0 24 24" fill="currentColor">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                    </svg>
                    Falar no WhatsApp
                </a>
                <button class="btn btn-primary" onclick="solicitarProposta()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                        <polyline points="22,6 12,13 2,6"/>
                    </svg>
                    Receber Proposta Detalhada
                </button>
                <button class="btn btn-outline" onclick="window.print()">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 6 2 18 2 18 9"/>
                        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Imprimir
                </button>
            </div>

            <!-- Share -->
            <div class="share-section">
                <p>Compartilhar orçamento:</p>
                <div class="share-buttons">
                    <a href="https://wa.me/?text=Confira meu orçamento: <?= urlencode(current_url()) ?>" class="share-btn whatsapp" target="_blank" rel="noopener" title="Compartilhar no WhatsApp">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                        </svg>
                    </a>
                    <a href="mailto:?subject=Meu orçamento LS Global&body=Confira: <?= urlencode(current_url()) ?>" class="share-btn email" title="Enviar por email">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/>
                            <polyline points="22,6 12,13 2,6"/>
                        </svg>
                    </a>
                    <button class="share-btn copy" onclick="copyLink()" title="Copiar link">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="9" y="9" width="13" height="13" rx="2" ry="2"/>
                            <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <footer class="page-footer">
            <p>
                <a href="<?= site_url('orcamento') ?>">← Fazer nova simulação</a>
                &nbsp;|&nbsp;
                <a href="<?= site_url() ?>">Voltar ao site</a>
            </p>
            <p style="margin-top: 12px; color: var(--color-text-light); font-size: 0.875rem;">
                LS Global Tecnologia - Desenvolvimento de Sistemas e Aplicativos
            </p>
        </footer>
    </div>

    <script>
        function solicitarProposta() {
            const mensagem = prompt('Deseja adicionar alguma mensagem? (opcional)');

            fetch('<?= site_url('orcamento/solicitar-proposta') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams({
                    '<?= csrf_token() ?>': '<?= csrf_hash() ?>',
                    'orcamento_id': '<?= $orcamento['id'] ?>',
                    'mensagem': mensagem || ''
                })
            })
            .then(response => response.json())
            .then(data => {
                alert(data.message);
            })
            .catch(error => {
                alert('Erro ao enviar solicitação. Tente novamente.');
            });
        }

        function copyLink() {
            navigator.clipboard.writeText(window.location.href).then(() => {
                alert('Link copiado!');
            });
        }
    </script>
</body>
</html>
