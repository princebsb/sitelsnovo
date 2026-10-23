<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f8fafc;">
    <table width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <tr>
            <td>
                <!-- Header -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%); border-radius: 12px 12px 0 0; padding: 30px; text-align: center;">
                    <tr>
                        <td>
                            <h1 style="color: #ffffff; margin: 0 0 10px 0; font-size: 24px;">Seu Pré-Orçamento</h1>
                            <p style="color: rgba(255,255,255,0.9); margin: 0; font-size: 14px;">LS Global Tecnologia</p>
                        </td>
                    </tr>
                </table>

                <!-- Body -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background: #ffffff; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td>
                            <!-- Greeting -->
                            <p style="color: #1e293b; font-size: 16px; margin-bottom: 20px;">
                                Olá, <strong><?= esc($dados['nome']) ?></strong>!
                            </p>
                            <p style="color: #64748b; font-size: 15px; line-height: 1.6; margin-bottom: 25px;">
                                Obrigado por utilizar nosso simulador de orçamento. Abaixo está a estimativa automática para o seu projeto.
                            </p>

                            <!-- Price Box -->
                            <table width="100%" cellpadding="25" cellspacing="0" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%); border-radius: 12px; margin-bottom: 25px; text-align: center;">
                                <tr>
                                    <td>
                                        <p style="color: #64748b; margin: 0 0 8px 0; font-size: 14px;">Faixa Estimada de Investimento</p>
                                        <p style="color: #6366f1; font-size: 32px; font-weight: 700; margin: 0 0 20px 0;"><?= esc($resultado['valor_texto']) ?></p>

                                        <table width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="text-align: center; width: 33%; padding: 10px;">
                                                    <p style="color: #1e293b; font-size: 20px; font-weight: 600; margin: 0;"><?= esc($resultado['prazo_texto']) ?></p>
                                                    <p style="color: #64748b; font-size: 12px; margin: 5px 0 0 0;">Prazo Estimado</p>
                                                </td>
                                                <td style="text-align: center; width: 33%; padding: 10px; border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;">
                                                    <p style="color: #1e293b; font-size: 20px; font-weight: 600; margin: 0;"><?= esc($resultado['horas_estimadas']) ?>h</p>
                                                    <p style="color: #64748b; font-size: 12px; margin: 5px 0 0 0;">Horas de Dev.</p>
                                                </td>
                                                <td style="text-align: center; width: 33%; padding: 10px;">
                                                    <p style="color: #1e293b; font-size: 20px; font-weight: 600; margin: 0; text-transform: capitalize;"><?= esc($resultado['complexidade']) ?></p>
                                                    <p style="color: #64748b; font-size: 12px; margin: 5px 0 0 0;">Complexidade</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Project Type -->
                            <table width="100%" cellpadding="15" cellspacing="0" style="background: #f8fafc; border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td>
                                        <p style="color: #64748b; margin: 0 0 5px 0; font-size: 13px;">Tipo de Projeto</p>
                                        <p style="color: #1e293b; margin: 0; font-size: 16px; font-weight: 600;"><?= esc($resultado['tipo_projeto'] ?? 'Sistema Web') ?></p>
                                    </td>
                                </tr>
                            </table>

                            <!-- Features -->
                            <?php if (!empty($resultado['funcionalidades'])): ?>
                            <h3 style="color: #1e293b; font-size: 16px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">Funcionalidades Identificadas</h3>
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 25px;">
                                <?php foreach ($resultado['funcionalidades'] as $func): ?>
                                <tr>
                                    <td style="padding: 8px 0; color: #64748b; font-size: 14px;">
                                        <span style="color: #22c55e; margin-right: 8px;">✓</span>
                                        <?= esc($func['nome'] ?? '') ?>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </table>
                            <?php endif; ?>

                            <!-- Disclaimer -->
                            <table width="100%" cellpadding="15" cellspacing="0" style="background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 0 8px 8px 0; margin-bottom: 25px;">
                                <tr>
                                    <td style="color: #92400e; font-size: 13px; line-height: 1.6;">
                                        <strong>Importante:</strong> Esta é uma estimativa inicial automática baseada na descrição fornecida. O valor final será definido após análise detalhada do projeto pela nossa equipe técnica.
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-bottom: 20px;">
                                <tr>
                                    <td style="text-align: center; padding: 10px 0;">
                                        <a href="https://wa.me/5511999999999?text=Olá! Recebi o pré-orçamento e gostaria de mais informações sobre o projeto." style="display: inline-block; padding: 16px 40px; background: #25D366; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 15px;">
                                            💬 Falar no WhatsApp
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="text-align: center; padding: 10px 0;">
                                        <a href="<?= site_url('orcamento/resultado/' . $resultado['id']) ?>" style="display: inline-block; padding: 14px 30px; background: linear-gradient(135deg, #6366f1 0%, #06b6d4 100%); color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600; font-size: 14px;">
                                            Ver Orçamento Completo Online
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <!-- Next Steps -->
                            <h3 style="color: #1e293b; font-size: 16px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid #e2e8f0;">Próximos Passos</h3>
                            <ol style="color: #64748b; font-size: 14px; line-height: 1.8; padding-left: 20px; margin-bottom: 20px;">
                                <li style="margin-bottom: 8px;">Entre em contato pelo WhatsApp para agendar uma reunião</li>
                                <li style="margin-bottom: 8px;">Apresentaremos uma proposta detalhada e personalizada</li>
                                <li style="margin-bottom: 8px;">Após aprovação, iniciamos o desenvolvimento do seu projeto</li>
                            </ol>
                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table width="100%" cellpadding="25" cellspacing="0" style="text-align: center;">
                    <tr>
                        <td>
                            <p style="color: #64748b; font-size: 14px; margin: 0 0 10px 0;">
                                <strong>LS Global Tecnologia</strong>
                            </p>
                            <p style="color: #94a3b8; font-size: 13px; margin: 0 0 5px 0;">
                                Desenvolvimento de Sistemas e Aplicativos
                            </p>
                            <p style="color: #94a3b8; font-size: 12px; margin: 0;">
                                <a href="https://lsglobaltecnologia.com.br" style="color: #6366f1; text-decoration: none;">lsglobaltecnologia.com.br</a>
                                &nbsp;|&nbsp;
                                <a href="mailto:contato@lsglobaltecnologia.com.br" style="color: #6366f1; text-decoration: none;">contato@lsglobaltecnologia.com.br</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
