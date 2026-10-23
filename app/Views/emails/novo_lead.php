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
                            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Novo Lead - Simulação de Orçamento</h1>
                        </td>
                    </tr>
                </table>

                <!-- Body -->
                <table width="100%" cellpadding="0" cellspacing="0" style="background: #ffffff; padding: 30px; border-radius: 0 0 12px 12px; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
                    <tr>
                        <td>
                            <!-- Alert -->
                            <table width="100%" cellpadding="15" cellspacing="0" style="background: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px; margin-bottom: 20px;">
                                <tr>
                                    <td style="color: #92400e; font-size: 14px;">
                                        <strong>Ação necessária:</strong> Entre em contato com o lead para apresentar proposta detalhada.
                                    </td>
                                </tr>
                            </table>

                            <!-- Lead Info -->
                            <h2 style="color: #1e293b; font-size: 18px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Dados do Lead</h2>
                            <table width="100%" cellpadding="8" cellspacing="0" style="margin-bottom: 25px;">
                                <tr>
                                    <td style="color: #64748b; width: 140px;">Nome:</td>
                                    <td style="color: #1e293b; font-weight: 600;"><?= esc($dados['nome']) ?></td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b;">Email:</td>
                                    <td style="color: #1e293b;"><a href="mailto:<?= esc($dados['email']) ?>" style="color: #6366f1;"><?= esc($dados['email']) ?></a></td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b;">WhatsApp:</td>
                                    <td style="color: #1e293b;"><a href="https://wa.me/55<?= preg_replace('/\D/', '', $dados['whatsapp']) ?>" style="color: #25D366;"><?= esc($dados['whatsapp']) ?></a></td>
                                </tr>
                                <tr>
                                    <td style="color: #64748b;">Tipo de Projeto:</td>
                                    <td style="color: #1e293b;"><?= esc($resultado['tipo_projeto'] ?? $dados['tipo_projeto']) ?></td>
                                </tr>
                            </table>

                            <!-- Estimate -->
                            <h2 style="color: #1e293b; font-size: 18px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Estimativa Gerada</h2>
                            <table width="100%" cellpadding="15" cellspacing="0" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%); border-radius: 8px; margin-bottom: 25px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <p style="color: #64748b; margin: 0 0 5px 0; font-size: 14px;">Faixa de Investimento</p>
                                        <p style="color: #6366f1; font-size: 28px; font-weight: 700; margin: 0;"><?= esc($resultado['valor_texto']) ?></p>
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        <table width="100%" cellpadding="10" cellspacing="0">
                                            <tr>
                                                <td style="text-align: center; width: 33%;">
                                                    <p style="color: #1e293b; font-size: 18px; font-weight: 600; margin: 0;"><?= esc($resultado['prazo_texto']) ?></p>
                                                    <p style="color: #64748b; font-size: 12px; margin: 5px 0 0 0;">Prazo</p>
                                                </td>
                                                <td style="text-align: center; width: 33%;">
                                                    <p style="color: #1e293b; font-size: 18px; font-weight: 600; margin: 0;"><?= esc($resultado['horas_estimadas']) ?>h</p>
                                                    <p style="color: #64748b; font-size: 12px; margin: 5px 0 0 0;">Horas</p>
                                                </td>
                                                <td style="text-align: center; width: 33%;">
                                                    <p style="color: #1e293b; font-size: 18px; font-weight: 600; margin: 0; text-transform: capitalize;"><?= esc($resultado['complexidade']) ?></p>
                                                    <p style="color: #64748b; font-size: 12px; margin: 5px 0 0 0;">Complexidade</p>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- Description -->
                            <h2 style="color: #1e293b; font-size: 18px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Descrição do Projeto</h2>
                            <p style="color: #64748b; line-height: 1.6; margin-bottom: 25px; padding: 15px; background: #f8fafc; border-radius: 8px;">
                                <?= nl2br(esc($dados['descricao_original'])) ?>
                            </p>

                            <!-- Features -->
                            <?php if (!empty($resultado['funcionalidades'])): ?>
                            <h2 style="color: #1e293b; font-size: 18px; margin-bottom: 15px; border-bottom: 1px solid #e2e8f0; padding-bottom: 10px;">Funcionalidades Detectadas</h2>
                            <ul style="color: #64748b; line-height: 1.8; margin-bottom: 25px; padding-left: 20px;">
                                <?php foreach ($resultado['funcionalidades'] as $func): ?>
                                <li style="margin-bottom: 5px;"><?= esc($func['nome'] ?? '') ?></li>
                                <?php endforeach; ?>
                            </ul>
                            <?php endif; ?>

                            <!-- CTA -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 20px;">
                                <tr>
                                    <td style="text-align: center;">
                                        <a href="https://wa.me/55<?= preg_replace('/\D/', '', $dados['whatsapp']) ?>?text=Olá <?= urlencode($dados['nome']) ?>! Sou da LS Global Tecnologia. Recebi sua solicitação de orçamento e gostaria de conversar sobre seu projeto." style="display: inline-block; padding: 14px 30px; background: #25D366; color: #ffffff; text-decoration: none; border-radius: 8px; font-weight: 600;">Contatar via WhatsApp</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>

                <!-- Footer -->
                <table width="100%" cellpadding="20" cellspacing="0" style="text-align: center;">
                    <tr>
                        <td style="color: #94a3b8; font-size: 12px;">
                            LS Global Tecnologia - Sistema de Orçamento Automático<br>
                            Este é um email automático, não responda.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
