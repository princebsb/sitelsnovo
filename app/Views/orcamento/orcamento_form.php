<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?></title>
    <meta name="description" content="<?= esc($description) ?>">

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
            --color-error: #ef4444;
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
            min-height: 100vh;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
            padding: 40px 24px;
        }

        /* Header */
        .page-header {
            text-align: center;
            margin-bottom: 48px;
        }

        .logo {
            margin-bottom: 24px;
        }

        .logo img {
            height: 50px;
        }

        .page-title {
            font-size: 2.5rem;
            font-weight: 800;
            margin-bottom: 16px;
            background: var(--gradient-primary);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .page-subtitle {
            font-size: 1.125rem;
            color: var(--color-text-light);
            max-width: 500px;
            margin: 0 auto;
        }

        /* Steps */
        .steps {
            display: flex;
            justify-content: center;
            gap: 32px;
            margin-bottom: 48px;
        }

        .step {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--color-text-light);
        }

        .step.active {
            color: var(--color-primary);
        }

        .step-number {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--color-border);
            border-radius: 50%;
            font-weight: 600;
            font-size: 0.875rem;
        }

        .step.active .step-number {
            background: var(--gradient-primary);
            color: white;
        }

        /* Form Card */
        .form-card {
            background: var(--color-white);
            border-radius: 16px;
            padding: 40px;
            box-shadow: var(--shadow-lg);
        }

        .form-section {
            margin-bottom: 32px;
        }

        .form-section-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--color-border);
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .form-group.full {
            grid-column: span 2;
        }

        label {
            font-size: 0.875rem;
            font-weight: 500;
            color: var(--color-text);
        }

        label .required {
            color: var(--color-error);
        }

        input, select, textarea {
            padding: 14px 16px;
            font-size: 1rem;
            font-family: inherit;
            background: var(--color-bg);
            border: 1px solid var(--color-border);
            border-radius: 8px;
            color: var(--color-text);
            transition: all 0.3s ease;
        }

        input:focus, select:focus, textarea:focus {
            outline: none;
            border-color: var(--color-primary);
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.1);
        }

        input.error, select.error, textarea.error {
            border-color: var(--color-error);
        }

        .error-message {
            font-size: 0.8125rem;
            color: var(--color-error);
        }

        textarea {
            resize: vertical;
            min-height: 150px;
        }

        .char-count {
            text-align: right;
            font-size: 0.75rem;
            color: var(--color-text-light);
            margin-top: 4px;
        }

        /* Tips */
        .tips {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 32px;
        }

        .tips h4 {
            font-size: 0.9375rem;
            font-weight: 600;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tips ul {
            list-style: none;
            font-size: 0.875rem;
            color: var(--color-text-light);
        }

        .tips li {
            padding: 4px 0;
            padding-left: 20px;
            position: relative;
        }

        .tips li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--color-success);
        }

        /* Button */
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            padding: 16px 32px;
            font-size: 1rem;
            font-weight: 600;
            font-family: inherit;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: var(--gradient-primary);
            color: white;
            width: 100%;
            box-shadow: 0 4px 14px rgba(99, 102, 241, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(99, 102, 241, 0.5);
        }

        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }

        .btn svg {
            width: 20px;
            height: 20px;
        }

        /* Spinner */
        .spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Loading Overlay */
        .loading-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(255, 255, 255, 0.95);
            z-index: 1000;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 24px;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-animation {
            width: 80px;
            height: 80px;
            border: 4px solid var(--color-border);
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        .loading-text {
            font-size: 1.25rem;
            font-weight: 600;
            color: var(--color-text);
        }

        .loading-steps {
            display: flex;
            flex-direction: column;
            gap: 12px;
            text-align: left;
        }

        .loading-step {
            display: flex;
            align-items: center;
            gap: 12px;
            color: var(--color-text-light);
            opacity: 0.5;
            transition: all 0.3s ease;
        }

        .loading-step.active {
            opacity: 1;
            color: var(--color-primary);
        }

        .loading-step.done {
            opacity: 1;
            color: var(--color-success);
        }

        .loading-step-icon {
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid currentColor;
            border-radius: 50%;
            font-size: 0.75rem;
        }

        .loading-step.done .loading-step-icon {
            background: var(--color-success);
            border-color: var(--color-success);
            color: white;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            margin-top: 48px;
            padding-top: 24px;
            border-top: 1px solid var(--color-border);
        }

        .page-footer a {
            color: var(--color-primary);
            text-decoration: none;
        }

        .page-footer a:hover {
            text-decoration: underline;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .container {
                padding: 24px 16px;
            }

            .page-title {
                font-size: 1.75rem;
            }

            .steps {
                flex-direction: column;
                align-items: center;
                gap: 16px;
            }

            .form-card {
                padding: 24px;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .form-group.full {
                grid-column: span 1;
            }
        }
    </style>
</head>
<body>
    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-animation"></div>
        <div class="loading-text">Analisando seu projeto...</div>
        <div class="loading-steps">
            <div class="loading-step" id="step1">
                <span class="loading-step-icon">1</span>
                <span>Enviando dados...</span>
            </div>
            <div class="loading-step" id="step2">
                <span class="loading-step-icon">2</span>
                <span>Analisando com IA...</span>
            </div>
            <div class="loading-step" id="step3">
                <span class="loading-step-icon">3</span>
                <span>Calculando estimativas...</span>
            </div>
            <div class="loading-step" id="step4">
                <span class="loading-step-icon">4</span>
                <span>Gerando orçamento...</span>
            </div>
        </div>
    </div>

    <div class="container">
        <!-- Header -->
        <header class="page-header">
            <div class="logo">
                <a href="<?= site_url() ?>">
                    <img src="<?= base_url('logo.svg') ?>" alt="LS Global Tecnologia">
                </a>
            </div>
            <h1 class="page-title">Simule seu Orçamento</h1>
            <p class="page-subtitle">
                Descreva sua ideia e receba uma estimativa automática de custo e prazo em segundos.
            </p>
        </header>

        <!-- Steps -->
        <div class="steps">
            <div class="step active">
                <span class="step-number">1</span>
                <span>Descreva seu projeto</span>
            </div>
            <div class="step">
                <span class="step-number">2</span>
                <span>Análise com IA</span>
            </div>
            <div class="step">
                <span class="step-number">3</span>
                <span>Receba o orçamento</span>
            </div>
        </div>

        <!-- Form -->
        <div class="form-card">
            <form id="orcamentoForm" method="POST" action="<?= site_url('orcamento/processar') ?>">
                <?= csrf_field() ?>

                <!-- Dados Pessoais -->
                <div class="form-section">
                    <h3 class="form-section-title">Seus Dados</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome completo <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" required placeholder="Seu nome">
                            <span class="error-message" id="nomeError"></span>
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="seu@email.com">
                            <span class="error-message" id="emailError"></span>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="whatsapp">WhatsApp <span class="required">*</span></label>
                            <input type="tel" id="whatsapp" name="whatsapp" required placeholder="(11) 99999-9999">
                            <span class="error-message" id="whatsappError"></span>
                        </div>
                        <div class="form-group">
                            <label for="tipo_projeto">Tipo de projeto <span class="required">*</span></label>
                            <select id="tipo_projeto" name="tipo_projeto" required>
                                <option value="">Selecione...</option>
                                <?php foreach ($tipos_projeto as $value => $label): ?>
                                    <option value="<?= esc($value) ?>"><?= esc($label) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <span class="error-message" id="tipo_projetoError"></span>
                        </div>
                    </div>
                </div>

                <!-- Descrição do Projeto -->
                <div class="form-section">
                    <h3 class="form-section-title">Descreva seu Projeto</h3>
                    <div class="form-group full">
                        <label for="descricao">Descrição detalhada <span class="required">*</span></label>
                        <textarea
                            id="descricao"
                            name="descricao"
                            required
                            placeholder="Descreva sua ideia de sistema ou aplicativo. Quanto mais detalhes você fornecer, mais precisa será a estimativa.

Exemplo: Preciso de um sistema para gerenciar minha clínica médica. Deve ter agendamento de consultas, cadastro de pacientes, prontuário eletrônico, controle financeiro e relatórios. Os médicos precisam acessar pelo celular também."
                            maxlength="2000"
                        ></textarea>
                        <div class="char-count"><span id="charCount">0</span>/2000 caracteres</div>
                        <span class="error-message" id="descricaoError"></span>
                    </div>
                </div>

                <!-- Tips -->
                <div class="tips">
                    <h4>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <path d="M12 16v-4M12 8h.01"/>
                        </svg>
                        Dicas para uma boa descrição
                    </h4>
                    <ul>
                        <li>Descreva o objetivo principal do sistema</li>
                        <li>Liste as funcionalidades que você precisa</li>
                        <li>Mencione integrações desejadas (pagamentos, WhatsApp, etc)</li>
                        <li>Indique se precisa de aplicativo mobile</li>
                        <li>Informe o público-alvo do sistema</li>
                    </ul>
                </div>

                <!-- Submit -->
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <span>Gerar Orçamento Automático</span>
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M5 12h14M12 5l7 7-7 7"/>
                    </svg>
                </button>
            </form>
        </div>

        <!-- Footer -->
        <footer class="page-footer">
            <p>
                <a href="<?= site_url() ?>">← Voltar ao site</a>
            </p>
            <p style="margin-top: 12px; color: var(--color-text-light); font-size: 0.875rem;">
                LS Global Tecnologia - Desenvolvimento de Sistemas e Aplicativos
            </p>
        </footer>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('orcamentoForm');
            const submitBtn = document.getElementById('submitBtn');
            const loadingOverlay = document.getElementById('loadingOverlay');
            const descricao = document.getElementById('descricao');
            const charCount = document.getElementById('charCount');
            const whatsapp = document.getElementById('whatsapp');

            // Character count
            descricao.addEventListener('input', function() {
                charCount.textContent = this.value.length;
            });

            // WhatsApp mask
            whatsapp.addEventListener('input', function(e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length <= 11) {
                    if (value.length > 6) {
                        value = `(${value.slice(0, 2)}) ${value.slice(2, 7)}-${value.slice(7)}`;
                    } else if (value.length > 2) {
                        value = `(${value.slice(0, 2)}) ${value.slice(2)}`;
                    } else if (value.length > 0) {
                        value = `(${value}`;
                    }
                }
                e.target.value = value;
            });

            // Form submission
            form.addEventListener('submit', async function(e) {
                e.preventDefault();

                // Clear errors
                document.querySelectorAll('.error-message').forEach(el => el.textContent = '');
                document.querySelectorAll('.error').forEach(el => el.classList.remove('error'));

                // Validate
                let isValid = true;
                const fields = ['nome', 'email', 'whatsapp', 'tipo_projeto', 'descricao'];

                fields.forEach(field => {
                    const input = document.getElementById(field);
                    if (!input.value.trim()) {
                        document.getElementById(field + 'Error').textContent = 'Este campo é obrigatório';
                        input.classList.add('error');
                        isValid = false;
                    }
                });

                if (descricao.value.length < 20) {
                    document.getElementById('descricaoError').textContent = 'Descreva seu projeto com pelo menos 20 caracteres';
                    descricao.classList.add('error');
                    isValid = false;
                }

                if (!isValid) return;

                // Show loading
                loadingOverlay.classList.add('active');
                submitBtn.disabled = true;

                // Animate steps
                const steps = ['step1', 'step2', 'step3', 'step4'];
                let currentStep = 0;

                const stepInterval = setInterval(() => {
                    if (currentStep > 0) {
                        document.getElementById(steps[currentStep - 1]).classList.remove('active');
                        document.getElementById(steps[currentStep - 1]).classList.add('done');
                        document.getElementById(steps[currentStep - 1]).querySelector('.loading-step-icon').textContent = '✓';
                    }
                    if (currentStep < steps.length) {
                        document.getElementById(steps[currentStep]).classList.add('active');
                        currentStep++;
                    }
                }, 1500);

                try {
                    const formData = new FormData(form);

                    const response = await fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    const result = await response.json();

                    clearInterval(stepInterval);

                    if (result.success) {
                        // Mark all steps as done
                        steps.forEach(step => {
                            const el = document.getElementById(step);
                            el.classList.remove('active');
                            el.classList.add('done');
                            el.querySelector('.loading-step-icon').textContent = '✓';
                        });

                        // Redirect after a moment
                        setTimeout(() => {
                            window.location.href = result.redirect;
                        }, 1000);
                    } else {
                        loadingOverlay.classList.remove('active');
                        submitBtn.disabled = false;

                        if (result.errors) {
                            Object.keys(result.errors).forEach(field => {
                                const errorEl = document.getElementById(field + 'Error');
                                if (errorEl) {
                                    errorEl.textContent = result.errors[field];
                                    document.getElementById(field)?.classList.add('error');
                                }
                            });
                        } else {
                            alert(result.message || 'Ocorreu um erro. Tente novamente.');
                        }
                    }
                } catch (error) {
                    console.error('Error:', error);
                    clearInterval(stepInterval);
                    loadingOverlay.classList.remove('active');
                    submitBtn.disabled = false;
                    alert('Ocorreu um erro de conexão. Tente novamente.');
                }
            });
        });
    </script>
</body>
</html>
