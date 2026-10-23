<?php
/**
 * LS Global Tecnologia - Sistema de Simulação de Pré-Orçamento com IA
 * Versão PHP Standalone
 */

session_start();

// Configurações
define('IA_PROVIDER', 'anthropic'); // anthropic ou openai
define('IA_API_KEY', ''); // Coloque sua chave API aqui
define('IA_MODEL', 'claude-sonnet-4-20250514');
define('VALOR_HORA', 180);
define('WHATSAPP', '5561998170414');

// Configurações do Banco de Dados
define('DB_HOST', 'localhost');
define('DB_NAME', 'lsglobal_orcamento');
define('DB_USER', 'root');
define('DB_PASS', '');

// Conexão com banco (opcional - funciona sem banco também)
function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            $pdo = false; // Banco não disponível
        }
    }
    return $pdo;
}

// Tabela de horas por funcionalidade (valores realistas)
$horasPorFuncionalidade = [
    'login' => 4,
    'cadastro' => 6,
    'recuperar_senha' => 3,
    'painel_admin' => 16,
    'crud_basico' => 8,
    'permissoes' => 10,
    'agendamento' => 12,
    'pagamento' => 16,
    'chat' => 24,
    'relatorios' => 12,
    'integracao_api' => 16,
    'notificacoes_push' => 8,
    'notificacoes_email' => 6,
    'upload' => 4,
    'galeria' => 8,
    'busca' => 8,
    'carrinho' => 12,
    'catalogo' => 14,
    'checkout' => 12,
    'dashboard_graficos' => 14,
    'api_rest' => 20,
    'whatsapp' => 10,
    'redes_sociais' => 8,
    'geolocalizacao' => 8,
    'multi_idioma' => 12,
    'pwa' => 10,
    'app_hibrido' => 60,
    'backup' => 4,
    'logs' => 6
];

// Horas base por tipo de projeto (estrutura inicial)
$horasBasePorTipo = [
    'site' => 20,
    'sistema_web' => 24,
    'aplicativo' => 40,
    'automacao' => 16,
    'integracao' => 12,
    'outro' => 20
];

// Fatores de complexidade
$fatoresComplexidade = [
    'baixa' => 1.0,
    'media' => 1.15,
    'alta' => 1.35
];

// Processar formulário
$resultado = null;
$erro = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar dados
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $whatsapp = trim($_POST['whatsapp'] ?? '');
    $tipo_projeto = $_POST['tipo_projeto'] ?? '';
    $descricao = trim($_POST['descricao'] ?? '');

    if (empty($nome) || empty($email) || empty($whatsapp) || empty($tipo_projeto) || strlen($descricao) < 20) {
        $erro = 'Por favor, preencha todos os campos corretamente.';
    } else {
        // Analisar com IA ou usar análise local
        if (!empty(IA_API_KEY)) {
            $analise = analisarComIA($descricao, $tipo_projeto);
        } else {
            $analise = analisarLocal($descricao, $tipo_projeto);
        }

        // Calcular estimativas
        $resultado = calcularEstimativa($analise, $tipo_projeto);
        $resultado['nome'] = $nome;
        $resultado['email'] = $email;
        $resultado['tipo_projeto_texto'] = getTipoProjetoTexto($tipo_projeto);
        $resultado['descricao_original'] = $descricao;
        $resultado['analise'] = $analise;

        // Salvar no banco (se disponível)
        salvarLead($nome, $email, $whatsapp, $tipo_projeto, $descricao, $analise, $resultado);

        // Guardar na sessão
        $_SESSION['orcamento_resultado'] = $resultado;
    }
}

// Funções

function analisarComIA($descricao, $tipoProjeto) {
    $prompt = construirPrompt($descricao, $tipoProjeto);

    try {
        if (IA_PROVIDER === 'anthropic') {
            $resposta = chamarAnthropic($prompt);
        } else {
            $resposta = chamarOpenAI($prompt);
        }

        // Limpar e decodificar
        $resposta = preg_replace('/```json\s*/', '', $resposta);
        $resposta = preg_replace('/```\s*/', '', $resposta);
        $dados = json_decode(trim($resposta), true);

        if ($dados) {
            return $dados;
        }
    } catch (Exception $e) {
        error_log('Erro IA: ' . $e->getMessage());
    }

    return analisarLocal($descricao, $tipoProjeto);
}

function chamarAnthropic($prompt) {
    $ch = curl_init('https://api.anthropic.com/v1/messages');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'x-api-key: ' . IA_API_KEY,
            'anthropic-version: 2023-06-01'
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => IA_MODEL,
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]),
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $body = json_decode($response, true);
    return $body['content'][0]['text'] ?? '';
}

function chamarOpenAI($prompt) {
    $ch = curl_init('https://api.openai.com/v1/chat/completions');

    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => [
            'Content-Type: application/json',
            'Authorization: Bearer ' . IA_API_KEY
        ],
        CURLOPT_POSTFIELDS => json_encode([
            'model' => IA_MODEL,
            'max_tokens' => 2048,
            'messages' => [
                ['role' => 'user', 'content' => $prompt]
            ]
        ]),
        CURLOPT_TIMEOUT => 60
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    $body = json_decode($response, true);
    return $body['choices'][0]['message']['content'] ?? '';
}

function construirPrompt($descricao, $tipoProjeto) {
    $tipoTexto = getTipoProjetoTexto($tipoProjeto);

    return <<<PROMPT
Você é um analista de requisitos sênior. Transforme a descrição simples do cliente em uma especificação DETALHADA com passo a passo.

TIPO: {$tipoTexto}

DESCRIÇÃO DO CLIENTE:
"{$descricao}"

Retorne APENAS este JSON:
{
    "resumo_melhorado": "**Visão Geral:**\nDescreva o objetivo do sistema em 2-3 frases, sendo específico ao negócio do cliente.\n\n**Passo a passo - O que o [usuário/cliente/paciente/aluno] poderá fazer:**\n1. [Primeiro passo detalhado]\n2. [Segundo passo detalhado]\n3. [Continue numerando cada ação que o usuário pode fazer]\n... liste pelo menos 6-8 passos\n\n**Passo a passo - O que o administrador poderá fazer:**\n1. [Primeiro passo do admin]\n2. [Segundo passo do admin]\n3. [Continue numerando]\n... liste pelo menos 6-8 passos\n\n**Diferenciais do sistema:**\n✓ [Diferencial 1]\n✓ [Diferencial 2]\n✓ [Diferencial 3]",
    "funcionalidades": [
        {"nome": "Nome", "slug": "slug_valido", "descricao": "Descrição"}
    ],
    "integracoes": [
        {"nome": "Nome", "slug": "slug"}
    ],
    "complexidade": "baixa|media|alta"
}

SLUGS: login, cadastro, recuperar_senha, painel_admin, crud_basico, permissoes, agendamento, pagamento, chat, relatorios, integracao_api, notificacoes_push, notificacoes_email, upload, galeria, busca, carrinho, catalogo, checkout, dashboard_graficos, api_rest, whatsapp, redes_sociais, geolocalizacao, multi_idioma, pwa, app_hibrido, backup, logs

REGRAS:
1. Seja ESPECÍFICO ao negócio (barbearia: fale de cortes, barbeiros; clínica: fale de consultas, pacientes)
2. O passo a passo deve ser DETALHADO e REALISTA
3. Numere cada passo (1. 2. 3. etc)
4. Use o termo correto (cliente, paciente, aluno, etc)
5. Liste pelo menos 6 passos para usuário e 6 para admin
6. Retorne APENAS JSON válido
PROMPT;
}

function analisarLocal($descricao, $tipoProjeto) {
    $descricaoLower = mb_strtolower($descricao);
    $funcionalidades = [];

    // Mapeamento de palavras-chave para funcionalidades
    $keywords = [
        'login' => ['login', 'logar', 'entrar', 'autenticação', 'autenticar', 'acesso'],
        'cadastro' => ['cadastro', 'cadastrar', 'registro', 'registrar', 'criar conta', 'novo usuário'],
        'recuperar_senha' => ['recuperar senha', 'esqueci senha', 'resetar senha'],
        'painel_admin' => ['painel', 'admin', 'administrador', 'dashboard', 'gestão', 'gerenciar', 'gerenciamento'],
        'crud_basico' => ['cadastro de', 'listar', 'editar', 'excluir', 'adicionar'],
        'permissoes' => ['permissão', 'permissões', 'nível de acesso', 'perfil de usuário', 'roles'],
        'pagamento' => ['pagamento', 'pagar', 'cartão', 'pix', 'boleto', 'cobrança', 'financeiro'],
        'relatorios' => ['relatório', 'relatórios', 'exportar', 'pdf', 'excel', 'gráfico', 'estatística'],
        'agendamento' => ['agenda', 'agendar', 'agendamento', 'horário', 'reserva', 'reservar', 'marcar', 'consulta'],
        'chat' => ['chat', 'mensagem', 'mensagens', 'conversa', 'comunicação', 'atendimento'],
        'upload' => ['upload', 'enviar arquivo', 'anexo', 'documento', 'foto', 'imagem'],
        'galeria' => ['galeria', 'fotos', 'imagens', 'álbum', 'portfólio'],
        'busca' => ['busca', 'buscar', 'pesquisa', 'pesquisar', 'filtro', 'filtrar', 'procurar'],
        'notificacoes_email' => ['email', 'e-mail', 'notificação', 'notificar', 'aviso', 'alerta'],
        'notificacoes_push' => ['push', 'notificação push', 'alerta no celular'],
        'carrinho' => ['carrinho', 'sacola', 'cesta'],
        'catalogo' => ['catálogo', 'produtos', 'vitrine', 'cardápio', 'menu'],
        'checkout' => ['checkout', 'finalizar compra', 'fechar pedido'],
        'integracao_api' => ['api', 'integração', 'integrar', 'webhook', 'conectar'],
        'whatsapp' => ['whatsapp', 'wpp', 'zap', 'whats'],
        'redes_sociais' => ['facebook', 'instagram', 'linkedin', 'rede social', 'compartilhar'],
        'geolocalizacao' => ['localização', 'mapa', 'endereço', 'gps', 'geolocalização'],
        'dashboard_graficos' => ['dashboard', 'gráficos', 'métricas', 'indicadores', 'kpi'],
        'api_rest' => ['api rest', 'backend', 'webservice'],
        'app_hibrido' => ['app', 'aplicativo', 'mobile', 'celular', 'android', 'ios'],
        'multi_idioma' => ['idioma', 'inglês', 'espanhol', 'tradução', 'multilíngue'],
        'backup' => ['backup', 'cópia de segurança'],
        'logs' => ['log', 'histórico', 'auditoria', 'registro de ações']
    ];

    // Detectar funcionalidades mencionadas
    foreach ($keywords as $slug => $termos) {
        foreach ($termos as $termo) {
            if (strpos($descricaoLower, $termo) !== false) {
                $nomeFormatado = getNomeFuncionalidade($slug);
                $funcionalidades[] = [
                    'nome' => $nomeFormatado,
                    'slug' => $slug,
                    'descricao' => getDescricaoModulo($slug)
                ];
                break;
            }
        }
    }

    // Se não detectou NADA, usar funcionalidades básicas mínimas
    if (empty($funcionalidades)) {
        $funcionalidades = [
            ['nome' => 'Estrutura Básica', 'slug' => 'crud_basico', 'descricao' => 'Funcionalidades básicas do sistema']
        ];
    }

    // NÃO adicionar funcionalidades que o cliente não pediu

    $complexidade = count($funcionalidades) > 5 ? 'alta' : (count($funcionalidades) > 2 ? 'media' : 'baixa');

    // Gerar resumo detalhado e específico
    $resumoMelhorado = gerarResumoDetalhado($descricao, $tipoProjeto, $funcionalidades, $complexidade);

    // Gerar sugestões específicas baseadas no projeto
    $sugestoes = gerarSugestoesEspecificas($tipoProjeto, $funcionalidades, $descricao);

    // Detectar integrações possíveis
    $integracoes = detectarIntegracoes($descricao);

    return [
        'resumo_melhorado' => $resumoMelhorado,
        'funcionalidades' => $funcionalidades,
        'integracoes' => $integracoes,
        'complexidade' => $complexidade,
        'sugestoes' => $sugestoes
    ];
}

function gerarResumoDetalhado($descricao, $tipoProjeto, $funcionalidades, $complexidade) {
    $tipoTexto = getTipoProjetoTexto($tipoProjeto);
    $descricaoLower = mb_strtolower($descricao);

    // Detectar tipo de negócio
    $negocio = detectarTipoNegocio($descricaoLower);

    // Criar descrição profissional estruturada
    $resumo = "**Visão Geral:**\n";
    $resumo .= "Sistema desenvolvido para automatizar e profissionalizar as operações do seu negócio, permitindo que seus {$negocio['usuario']}s tenham uma experiência moderna e prática.\n\n";

    $resumo .= "**Passo a passo - O que o {$negocio['usuario']} poderá fazer:**\n";
    $contador = 1;
    foreach ($negocio['passos_usuario'] as $passo) {
        $resumo .= "{$contador}. {$passo}\n";
        $contador++;
    }

    $resumo .= "\n**Passo a passo - O que o administrador poderá fazer:**\n";
    $contador = 1;
    foreach ($negocio['passos_admin'] as $passo) {
        $resumo .= "{$contador}. {$passo}\n";
        $contador++;
    }

    $resumo .= "\n**Diferenciais do sistema:**\n";
    foreach ($negocio['diferenciais'] as $diferencial) {
        $resumo .= "✓ {$diferencial}\n";
    }

    return $resumo;
}

function detectarTipoNegocio($descricaoLower) {
    $negocios = [
        'barbearia' => [
            'keywords' => ['barbearia', 'barbeiro', 'corte de cabelo', 'barba'],
            'usuario' => 'cliente',
            'passos_usuario' => [
                'Acessa o site/app da barbearia pelo celular ou computador',
                'Visualiza os serviços disponíveis (corte, barba, combo, etc) com preços',
                'Escolhe o barbeiro de preferência ou aceita qualquer um disponível',
                'Seleciona a data e horário disponível no calendário',
                'Confirma o agendamento e recebe comprovante por WhatsApp/email',
                'Recebe lembrete automático 24h e 1h antes do horário',
                'Pode cancelar ou remarcar pelo próprio sistema',
                'Após o atendimento, pode avaliar o serviço'
            ],
            'passos_admin' => [
                'Acessa o painel administrativo com login seguro',
                'Visualiza o dashboard com agendamentos do dia e faturamento',
                'Gerencia os horários de funcionamento da barbearia',
                'Cadastra e gerencia os barbeiros e seus horários',
                'Configura os serviços oferecidos com preços e duração',
                'Visualiza histórico completo de cada cliente',
                'Acessa relatórios de faturamento diário, semanal e mensal',
                'Envia promoções e novidades para os clientes cadastrados'
            ],
            'diferenciais' => ['Agendamento online 24h sem precisar ligar', 'Lembretes automáticos reduzem faltas em até 70%', 'Histórico completo de cada cliente', 'Relatórios de faturamento em tempo real']
        ],
        'salao' => [
            'keywords' => ['salão', 'salao', 'beleza', 'cabelereiro', 'manicure', 'estética'],
            'usuario' => 'cliente',
            'passos_usuario' => [
                'Acessa o site/app do salão',
                'Navega pelos serviços disponíveis com fotos e preços',
                'Visualiza o portfólio de cada profissional',
                'Escolhe o serviço, profissional e horário',
                'Pode agendar múltiplos serviços na mesma visita',
                'Recebe confirmação e lembretes automáticos',
                'Pode pagar online antecipadamente ou no local',
                'Avalia o atendimento após o serviço'
            ],
            'passos_admin' => [
                'Acessa o painel com visão geral do salão',
                'Gerencia agenda de todos os profissionais',
                'Controla comissões de cada profissional automaticamente',
                'Cadastra serviços com fotos, preços e tempo de duração',
                'Gerencia estoque de produtos utilizados',
                'Visualiza relatórios de serviços mais procurados',
                'Controla o financeiro com entradas e saídas',
                'Gerencia pacotes e promoções'
            ],
            'diferenciais' => ['Cliente escolhe profissional pelo portfólio', 'Comissões calculadas automaticamente', 'Controle de estoque integrado', 'Gestão de pacotes promocionais']
        ],
        'clinica' => [
            'keywords' => ['clínica', 'clinica', 'médico', 'medico', 'consultório', 'consultorio', 'paciente', 'saúde', 'saude'],
            'usuario' => 'paciente',
            'passos_usuario' => [
                'Realiza cadastro com dados pessoais e histórico médico',
                'Escolhe a especialidade médica desejada',
                'Visualiza os médicos disponíveis e seus horários',
                'Agenda a consulta na data e horário preferidos',
                'Informa se é particular ou convênio',
                'Recebe confirmação com instruções de preparo (se necessário)',
                'Recebe lembretes antes da consulta',
                'Após a consulta, acessa prescrições e pedidos de exames online',
                'Pode visualizar resultados de exames quando disponíveis'
            ],
            'passos_admin' => [
                'Acessa o painel com agenda do dia de todos os médicos',
                'Cadastra e gerencia médicos e especialidades',
                'Gerencia prontuário eletrônico dos pacientes',
                'Emite prescrições e atestados digitais',
                'Controla consultas particulares e por convênio',
                'Gerencia o financeiro com relatórios detalhados',
                'Envia lembretes de retorno para pacientes',
                'Gera relatórios de atendimentos por período'
            ],
            'diferenciais' => ['Prontuário eletrônico seguro (LGPD)', 'Prescrição digital válida', 'Paciente acessa exames online', 'Integração com convênios']
        ],
        'loja' => [
            'keywords' => ['loja', 'e-commerce', 'ecommerce', 'vender', 'produto', 'estoque', 'venda online'],
            'usuario' => 'cliente',
            'passos_usuario' => [
                'Acessa a loja virtual pelo computador ou celular',
                'Navega pelos produtos por categoria ou busca',
                'Filtra por preço, tamanho, cor ou outras características',
                'Visualiza fotos, descrição e avaliações do produto',
                'Adiciona produtos ao carrinho de compras',
                'Aplica cupom de desconto se tiver',
                'Informa endereço de entrega e calcula o frete',
                'Escolhe forma de pagamento (cartão, pix, boleto)',
                'Finaliza a compra e recebe confirmação por email',
                'Acompanha o status do pedido em tempo real',
                'Recebe o produto e pode avaliar a compra'
            ],
            'passos_admin' => [
                'Acessa o painel com resumo de vendas do dia',
                'Cadastra produtos com fotos, descrições e preços',
                'Gerencia estoque com alertas de quantidade baixa',
                'Processa pedidos e atualiza status de envio',
                'Gerencia cupons de desconto e promoções',
                'Configura frete por região ou correios',
                'Visualiza relatórios de vendas e produtos mais vendidos',
                'Gerencia clientes e histórico de compras'
            ],
            'diferenciais' => ['Loja funcionando 24 horas por dia', 'Múltiplas formas de pagamento', 'Rastreamento de pedidos em tempo real', 'Cupons e promoções automáticas']
        ],
        'restaurante' => [
            'keywords' => ['restaurante', 'delivery', 'comida', 'cardápio', 'cardapio', 'pedido', 'lanchonete', 'pizzaria'],
            'usuario' => 'cliente',
            'passos_usuario' => [
                'Acessa o cardápio digital pelo celular',
                'Navega pelas categorias (lanches, bebidas, sobremesas)',
                'Visualiza fotos e descrição dos itens',
                'Adiciona itens ao pedido com observações (sem cebola, etc)',
                'Escolhe adicionais e complementos',
                'Revisa o pedido no carrinho',
                'Informa se é entrega ou retirada',
                'Se entrega, informa o endereço',
                'Escolhe forma de pagamento',
                'Acompanha o status: recebido > preparando > saiu para entrega',
                'Recebe notificação quando o pedido sair'
            ],
            'passos_admin' => [
                'Acessa o painel da cozinha com pedidos em tempo real',
                'Recebe alerta sonoro a cada novo pedido',
                'Atualiza status do pedido (preparando, pronto, enviado)',
                'Gerencia o cardápio com fotos e preços',
                'Define horários de funcionamento',
                'Configura taxa de entrega por região',
                'Pausa itens fora de estoque',
                'Visualiza relatórios de vendas por produto',
                'Identifica horários de pico e produtos mais vendidos'
            ],
            'diferenciais' => ['Pedidos diretos sem pagar taxa de apps', 'Cardápio sempre atualizado', 'Cliente acompanha pedido em tempo real', 'Relatórios de vendas detalhados']
        ],
        'academia' => [
            'keywords' => ['academia', 'treino', 'fitness', 'personal', 'musculação', 'exercício', 'exercicio'],
            'usuario' => 'aluno',
            'passos_usuario' => [
                'Faz login no app com suas credenciais',
                'Visualiza seu treino do dia na tela inicial',
                'Acessa a lista de exercícios com demonstração em vídeo',
                'Marca cada exercício conforme for executando',
                'Registra a carga utilizada e número de repetições',
                'Visualiza seu histórico de treinos anteriores',
                'Acompanha sua evolução através de gráficos',
                'Envia mensagem para o personal se tiver dúvidas',
                'Recebe notificações de incentivo e lembretes'
            ],
            'passos_admin' => [
                'Acessa o painel com lista de alunos',
                'Cria treinos personalizados para cada aluno',
                'Define exercícios, séries, repetições e descanso',
                'Adiciona vídeos demonstrativos aos exercícios',
                'Acompanha a execução dos treinos de cada aluno',
                'Visualiza evolução de carga e frequência',
                'Gerencia mensalidades e pagamentos',
                'Envia mensagens motivacionais para os alunos',
                'Gera relatórios de frequência e resultados'
            ],
            'diferenciais' => ['Treino no celular sem precisar de papel', 'Vídeos demonstrativos de cada exercício', 'Gráficos de evolução motivam o aluno', 'Comunicação direta com personal']
        ],
        'escola' => [
            'keywords' => ['escola', 'curso', 'aula', 'aluno', 'professor', 'educação', 'educacao', 'treinamento', 'ead'],
            'usuario' => 'aluno',
            'passos_usuario' => [
                'Realiza cadastro ou login na plataforma',
                'Visualiza os cursos disponíveis',
                'Escolhe o curso desejado e realiza matrícula',
                'Acessa o conteúdo do curso (vídeos, PDFs, etc)',
                'Assiste às aulas no seu próprio ritmo',
                'Faz exercícios e testes de fixação',
                'Acompanha seu progresso no curso',
                'Participa de fóruns de discussão',
                'Ao concluir, emite seu certificado'
            ],
            'passos_admin' => [
                'Acessa o painel de gestão de cursos',
                'Cria cursos com módulos e aulas',
                'Faz upload de vídeos e materiais',
                'Cria exercícios e provas',
                'Configura certificados automáticos',
                'Acompanha progresso dos alunos',
                'Visualiza relatórios de engajamento',
                'Gerencia matrículas e pagamentos',
                'Envia comunicados para turmas'
            ],
            'diferenciais' => ['Estude a qualquer hora, de qualquer lugar', 'Certificado emitido automaticamente', 'Progresso salvo automaticamente', 'Conteúdo disponível para revisão']
        ],
        'imobiliaria' => [
            'keywords' => ['imobiliária', 'imobiliaria', 'imóvel', 'imovel', 'aluguel', 'corretor', 'apartamento', 'casa'],
            'usuario' => 'cliente',
            'passos_usuario' => [
                'Acessa o site da imobiliária',
                'Define o tipo de busca (compra ou aluguel)',
                'Filtra por localização, preço e características',
                'Visualiza fotos e detalhes do imóvel',
                'Faz tour virtual 360° quando disponível',
                'Salva imóveis favoritos para comparar',
                'Agenda visita presencial pelo sistema',
                'Entra em contato com o corretor responsável',
                'Acompanha o processo de negociação'
            ],
            'passos_admin' => [
                'Cadastra imóveis com fotos e descrições',
                'Adiciona tour virtual e vídeos',
                'Define preço e condições de negociação',
                'Gerencia carteira de imóveis por corretor',
                'Recebe leads de interessados',
                'Gerencia agenda de visitas',
                'Acompanha funil de vendas',
                'Gera relatórios de desempenho',
                'Publica em portais automaticamente'
            ],
            'diferenciais' => ['Tour virtual economiza visitas desnecessárias', 'Busca inteligente por localização', 'Contato direto com corretor', 'Imóveis publicados em múltiplos portais']
        ]
    ];

    // Detectar negócio
    foreach ($negocios as $tipo => $dados) {
        foreach ($dados['keywords'] as $keyword) {
            if (strpos($descricaoLower, $keyword) !== false) {
                return $dados;
            }
        }
    }

    // Genérico
    return [
        'usuario' => 'usuário',
        'passos_usuario' => [
            'Acessa o sistema pelo navegador ou aplicativo',
            'Realiza login com suas credenciais',
            'Navega pelo menu de funcionalidades',
            'Executa as operações desejadas',
            'Visualiza e gerencia seus dados',
            'Recebe notificações importantes',
            'Pode alterar suas configurações',
            'Acessa histórico de atividades'
        ],
        'passos_admin' => [
            'Acessa o painel administrativo',
            'Visualiza dashboard com resumo geral',
            'Gerencia usuários e permissões',
            'Configura parâmetros do sistema',
            'Visualiza relatórios e estatísticas',
            'Gerencia conteúdos e dados',
            'Monitora atividades do sistema',
            'Realiza backup de dados'
        ],
        'diferenciais' => ['Sistema personalizado para sua necessidade', 'Acesso de qualquer dispositivo', 'Suporte técnico incluso', 'Atualizações constantes']
    ];
}


function getNomeFuncionalidade($slug) {
    $nomes = [
        'login' => 'Login/Autenticação',
        'cadastro' => 'Cadastro de Usuários',
        'recuperar_senha' => 'Recuperação de Senha',
        'painel_admin' => 'Painel Administrativo',
        'crud_basico' => 'Gestão de Dados',
        'permissoes' => 'Controle de Permissões',
        'agendamento' => 'Agendamento',
        'pagamento' => 'Pagamentos',
        'chat' => 'Chat/Mensagens',
        'relatorios' => 'Relatórios',
        'integracao_api' => 'Integração com API',
        'notificacoes_push' => 'Notificações Push',
        'notificacoes_email' => 'Notificações por Email',
        'upload' => 'Upload de Arquivos',
        'galeria' => 'Galeria de Imagens',
        'busca' => 'Busca/Filtros',
        'carrinho' => 'Carrinho de Compras',
        'catalogo' => 'Catálogo de Produtos',
        'checkout' => 'Checkout',
        'dashboard_graficos' => 'Dashboard com Gráficos',
        'api_rest' => 'API REST',
        'whatsapp' => 'Integração WhatsApp',
        'redes_sociais' => 'Redes Sociais',
        'geolocalizacao' => 'Geolocalização',
        'multi_idioma' => 'Multi-idioma',
        'pwa' => 'PWA',
        'app_hibrido' => 'App Mobile',
        'backup' => 'Backup',
        'logs' => 'Logs/Auditoria'
    ];
    return $nomes[$slug] ?? ucfirst(str_replace('_', ' ', $slug));
}

function gerarSugestoesEspecificas($tipoProjeto, $funcionalidades, $descricao) {
    // Apenas uma sugestão simples para próximos passos
    return ['Entre em contato para agendar uma reunião e detalhar melhor o projeto.'];
}

function detectarIntegracoes($descricao) {
    $integracoes = [];
    $descricaoLower = mb_strtolower($descricao);

    $possiveisIntegracoes = [
        'whatsapp' => ['whatsapp', 'zap', 'wpp'],
        'google_calendar' => ['google calendar', 'agenda google', 'calendário'],
        'pagamento_online' => ['pagamento', 'pagar', 'cartão', 'boleto', 'pix'],
        'email_marketing' => ['email marketing', 'newsletter', 'mailchimp'],
        'erp' => ['erp', 'totvs', 'sap', 'omie'],
        'nota_fiscal' => ['nota fiscal', 'nfe', 'nfce', 'sefaz'],
        'redes_sociais' => ['instagram', 'facebook', 'linkedin', 'redes sociais'],
        'maps' => ['mapa', 'localização', 'google maps', 'endereço'],
        'analytics' => ['analytics', 'métricas', 'relatório de acesso']
    ];

    foreach ($possiveisIntegracoes as $slug => $termos) {
        foreach ($termos as $termo) {
            if (strpos($descricaoLower, $termo) !== false) {
                $integracoes[] = [
                    'nome' => ucfirst(str_replace('_', ' ', $slug)),
                    'slug' => $slug
                ];
                break;
            }
        }
    }

    return $integracoes;
}

function calcularEstimativa($analise, $tipoProjeto) {
    global $horasPorFuncionalidade, $horasBasePorTipo, $fatoresComplexidade;

    $complexidade = $analise['complexidade'] ?? 'media';
    $fator = $fatoresComplexidade[$complexidade] ?? 1.2;

    // Detalhamento por módulo
    $modulos = [];

    // Módulo base (estrutura do projeto) - sem multiplicador
    $horasBase = $horasBasePorTipo[$tipoProjeto] ?? 20;
    $modulos[] = [
        'nome' => 'Estrutura e Setup',
        'descricao' => 'Criação do projeto • Configuração do ambiente • Estrutura de pastas • Banco de dados • Rotas básicas',
        'horas' => $horasBase,
        'icone' => '🏗️',
        'cor' => '#6366f1'
    ];

    // Módulos de funcionalidades
    $horasFuncionalidades = 0;
    foreach ($analise['funcionalidades'] ?? [] as $func) {
        $slug = $func['slug'] ?? '';
        $horasFunc = $horasPorFuncionalidade[$slug] ?? 8;
        $horasComFator = ceil($horasFunc * $fator);
        $horasFuncionalidades += $horasFunc;

        $modulos[] = [
            'nome' => $func['nome'] ?? ucfirst($slug),
            'descricao' => getDescricaoModulo($slug),
            'horas' => $horasComFator,
            'icone' => getIconeModulo($slug),
            'cor' => getCorModulo($slug)
        ];
    }

    // Módulos de integrações
    $horasIntegracoes = 0;
    foreach ($analise['integracoes'] ?? [] as $integ) {
        $slug = $integ['slug'] ?? '';
        $horasInteg = 8; // 8 horas por integração
        $horasComFator = ceil($horasInteg * $fator);
        $horasIntegracoes += $horasInteg;

        $modulos[] = [
            'nome' => 'Integração: ' . ($integ['nome'] ?? ucfirst($slug)),
            'descricao' => getDescricaoIntegracao($slug),
            'horas' => $horasComFator,
            'icone' => '🔗',
            'cor' => '#06b6d4'
        ];
    }

    // Módulo de testes e qualidade (10% do total)
    $horasTestes = max(4, ceil(($horasBase + $horasFuncionalidades + $horasIntegracoes) * 0.10 * $fator));
    $modulos[] = [
        'nome' => 'Testes e Qualidade',
        'descricao' => 'Testes de funcionalidades • Correção de bugs • Testes em dispositivos • Validação final',
        'horas' => $horasTestes,
        'icone' => '✅',
        'cor' => '#22c55e'
    ];

    // Módulo de deploy e documentação
    $horasDeploy = 4;
    $modulos[] = [
        'nome' => 'Deploy e Entrega',
        'descricao' => 'Configuração do servidor • Publicação do sistema • Testes em produção • Entrega ao cliente',
        'horas' => $horasDeploy,
        'icone' => '🚀',
        'cor' => '#f59e0b'
    ];

    // Calcular totais
    $horasEstimadas = array_sum(array_column($modulos, 'horas'));

    $valorBase = $horasEstimadas * VALOR_HORA;
    $valorMin = round($valorBase * 0.85, 2);
    $valorMax = round($valorBase * 1.15, 2);

    // Prazo baseado em 6h de trabalho efetivo por dia
    $prazoMin = ceil($horasEstimadas / 6);
    $prazoMax = ceil($horasEstimadas / 4);

    return [
        'horas_estimadas' => $horasEstimadas,
        'valor_min' => $valorMin,
        'valor_max' => $valorMax,
        'prazo_min' => $prazoMin,
        'prazo_max' => $prazoMax,
        'complexidade' => $complexidade,
        'valor_texto' => 'R$ ' . number_format($valorMin, 2, ',', '.') . ' a R$ ' . number_format($valorMax, 2, ',', '.'),
        'prazo_texto' => $prazoMin . ' a ' . $prazoMax . ' dias úteis',
        'modulos' => $modulos
    ];
}

function getDescricaoModulo($slug) {
    $descricoes = [
        'login' => 'Tela de login • Validação de credenciais • Controle de sessão • Logout seguro',
        'cadastro' => 'Formulário de registro • Validação de campos • Confirmação por email • Ativação de conta',
        'recuperar_senha' => 'Solicitação por email • Geração de token • Link seguro • Redefinição de senha',
        'painel_admin' => 'Dashboard inicial • Menu de navegação • Estatísticas gerais • Atalhos rápidos',
        'crud_basico' => 'Listagem com filtros • Formulário de cadastro • Edição de registros • Exclusão com confirmação',
        'permissoes' => 'Cadastro de perfis • Definição de acessos • Vinculação de usuários • Validação por rota',
        'agendamento' => 'Calendário visual • Seleção de horários • Confirmação de reserva • Gestão de disponibilidade',
        'pagamento' => 'Seleção de método • Integração com gateway • Processamento seguro • Confirmação de pagamento',
        'chat' => 'Lista de conversas • Envio de mensagens • Notificação em tempo real • Histórico de mensagens',
        'relatorios' => 'Seleção de período • Filtros personalizados • Geração de dados • Exportação PDF/Excel',
        'integracao_api' => 'Configuração de endpoints • Autenticação • Envio/recebimento de dados • Tratamento de erros',
        'notificacoes_push' => 'Configuração do serviço • Registro de dispositivos • Envio de notificações • Gestão de tokens',
        'notificacoes_email' => 'Templates de email • Configuração SMTP • Envio automático • Log de envios',
        'upload' => 'Seleção de arquivos • Validação de tipo/tamanho • Upload seguro • Gestão de arquivos',
        'galeria' => 'Upload de imagens • Organização em álbuns • Visualização ampliada • Exclusão de itens',
        'busca' => 'Campo de pesquisa • Filtros avançados • Ordenação de resultados • Paginação',
        'carrinho' => 'Adicionar produtos • Alterar quantidades • Calcular totais • Salvar carrinho',
        'catalogo' => 'Listagem de produtos • Categorias • Filtros • Página de detalhes',
        'checkout' => 'Resumo do pedido • Dados de entrega • Seleção de pagamento • Confirmação final',
        'dashboard_graficos' => 'Coleta de dados • Geração de gráficos • Indicadores visuais • Atualização em tempo real',
        'api_rest' => 'Estrutura de endpoints • Autenticação JWT • Documentação • Versionamento',
        'whatsapp' => 'Configuração da API • Envio de mensagens • Templates aprovados • Webhook de respostas',
        'redes_sociais' => 'Login social (Google/Facebook) • Obtenção de dados • Vinculação de conta • Compartilhamento',
        'geolocalizacao' => 'Obtenção de coordenadas • Exibição em mapa • Cálculo de distância • Busca por proximidade',
        'multi_idioma' => 'Arquivos de tradução • Seleção de idioma • Tradução de conteúdo • Persistência de preferência',
        'pwa' => 'Service Worker • Cache offline • Manifest.json • Instalação no dispositivo',
        'app_hibrido' => 'Estrutura do app • Telas principais • Navegação • Compilação iOS/Android',
        'backup' => 'Agendamento automático • Backup do banco • Backup de arquivos • Restauração',
        'logs' => 'Registro de ações • Identificação de usuário • Data/hora • Consulta de histórico'
    ];
    return $descricoes[$slug] ?? 'Desenvolvimento conforme especificação do cliente';
}

function getIconeModulo($slug) {
    $icones = [
        'login' => '🔐',
        'cadastro' => '📝',
        'recuperar_senha' => '🔑',
        'painel_admin' => '⚙️',
        'crud_basico' => '📋',
        'permissoes' => '🛡️',
        'agendamento' => '📅',
        'pagamento' => '💳',
        'chat' => '💬',
        'relatorios' => '📊',
        'integracao_api' => '🔗',
        'notificacoes_push' => '🔔',
        'notificacoes_email' => '📧',
        'upload' => '📤',
        'galeria' => '🖼️',
        'busca' => '🔍',
        'carrinho' => '🛒',
        'catalogo' => '📦',
        'checkout' => '✅',
        'dashboard_graficos' => '📈',
        'api_rest' => '🔌',
        'whatsapp' => '📱',
        'redes_sociais' => '🌐',
        'geolocalizacao' => '📍',
        'multi_idioma' => '🌍',
        'pwa' => '📲',
        'app_hibrido' => '📱',
        'backup' => '💾',
        'logs' => '📜'
    ];
    return $icones[$slug] ?? '⚡';
}

function getCorModulo($slug) {
    $cores = [
        'login' => '#6366f1',
        'cadastro' => '#8b5cf6',
        'painel_admin' => '#06b6d4',
        'pagamento' => '#22c55e',
        'chat' => '#3b82f6',
        'relatorios' => '#f59e0b',
        'agendamento' => '#ec4899',
        'carrinho' => '#14b8a6',
        'checkout' => '#22c55e'
    ];
    return $cores[$slug] ?? '#64748b';
}

function getDescricaoIntegracao($slug) {
    $descricoes = [
        'whatsapp' => 'Configuração API WhatsApp • Envio de mensagens • Notificações automáticas • Respostas via webhook',
        'google_calendar' => 'Autenticação Google • Sincronização de eventos • Criação de compromissos • Notificações',
        'pagamento_online' => 'Configuração do gateway • Checkout seguro • Confirmação de pagamento • Webhook de status',
        'email_marketing' => 'Integração com serviço • Listas de contatos • Disparo de campanhas • Métricas de abertura',
        'erp' => 'Conexão com sistema ERP • Sincronização de dados • Importação/exportação • Logs de integração',
        'nota_fiscal' => 'Configuração SEFAZ • Emissão de NF-e • Cancelamento • Consulta de status',
        'redes_sociais' => 'APIs sociais • Login OAuth • Compartilhamento • Publicação automática',
        'maps' => 'API Google Maps • Exibição de mapa • Marcadores • Cálculo de rotas',
        'analytics' => 'Código de rastreamento • Eventos personalizados • Dashboard de métricas • Relatórios'
    ];
    return $descricoes[$slug] ?? 'Configuração • Autenticação • Troca de dados • Testes de integração';
}

function getTipoProjetoTexto($tipo) {
    $tipos = [
        'site' => 'Site Institucional / Landing Page',
        'sistema_web' => 'Sistema Web / Plataforma Online',
        'aplicativo' => 'Aplicativo Mobile (iOS/Android)',
        'automacao' => 'Automação de Processos',
        'integracao' => 'Integração de Sistemas / APIs',
        'outro' => 'Outro'
    ];
    return $tipos[$tipo] ?? 'Projeto';
}

function salvarLead($nome, $email, $whatsapp, $tipo, $descricao, $analise, $resultado) {
    $db = getDB();
    if (!$db) return false;

    try {
        $sql = "INSERT INTO leads_orcamento
                (nome, email, whatsapp, tipo_projeto, descricao_original, descricao_melhorada,
                 funcionalidades, integracoes, sugestoes, complexidade, horas_estimadas,
                 valor_min, valor_max, prazo_min, prazo_max, ip_address, created_at)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";

        $stmt = $db->prepare($sql);
        $stmt->execute([
            $nome,
            $email,
            $whatsapp,
            $tipo,
            $descricao,
            $analise['resumo_melhorado'] ?? '',
            json_encode($analise['funcionalidades'] ?? []),
            json_encode($analise['integracoes'] ?? []),
            json_encode($analise['sugestoes'] ?? []),
            $resultado['complexidade'],
            $resultado['horas_estimadas'],
            $resultado['valor_min'],
            $resultado['valor_max'],
            $resultado['prazo_min'],
            $resultado['prazo_max'],
            $_SERVER['REMOTE_ADDR'] ?? ''
        ]);

        return $db->lastInsertId();
    } catch (Exception $e) {
        error_log('Erro ao salvar lead: ' . $e->getMessage());
        return false;
    }
}

function h($str) {
    return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Simular Orçamento | LS Global Tecnologia</title>
    <meta name="description" content="Simule gratuitamente o orçamento do seu projeto de software com inteligência artificial.">
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
            margin-bottom: 40px;
        }

        .logo { margin-bottom: 24px; }
        .logo img { height: 50px; }

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
        }

        /* Card */
        .card {
            background: var(--color-white);
            border-radius: 16px;
            padding: 40px;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
            margin-bottom: 24px;
        }

        .section-title {
            font-size: 1.125rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--color-border);
        }

        /* Form */
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

        .form-group.full { grid-column: span 2; }

        label {
            font-size: 0.875rem;
            font-weight: 500;
        }

        .required { color: var(--color-error); }

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

        textarea {
            resize: vertical;
            min-height: 150px;
        }

        /* Buttons */
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
            text-decoration: none;
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

        .btn-whatsapp {
            background: var(--color-whatsapp);
            color: white;
        }

        .btn-whatsapp:hover {
            background: #20bd5a;
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

        /* Alert */
        .alert {
            padding: 16px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            color: var(--color-error);
            border-left: 4px solid var(--color-error);
        }

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            color: #92400e;
            border-left: 4px solid var(--color-warning);
        }

        /* Result */
        .result-header {
            background: var(--gradient-primary);
            color: white;
            padding: 32px;
            border-radius: 16px 16px 0 0;
            margin: -40px -40px 32px -40px;
            text-align: center;
        }

        .result-header h2 {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

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
        }

        .price-detail-label {
            font-size: 0.8125rem;
            color: var(--color-text-light);
        }

        /* Features */
        .features-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 12px;
            margin-bottom: 24px;
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
            font-size: 12px;
        }

        .feature-name { font-weight: 500; }

        /* Complexity Badge */
        .complexity-badge {
            display: inline-block;
            padding: 8px 16px;
            border-radius: 50px;
            font-weight: 600;
            font-size: 0.875rem;
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

        /* CTA */
        .cta-buttons {
            display: flex;
            gap: 16px;
            flex-wrap: wrap;
            margin-top: 24px;
        }

        .cta-buttons .btn { flex: 1; min-width: 200px; }

        /* Tips */
        .tips {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.1) 0%, rgba(6, 182, 212, 0.1) 100%);
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 24px;
        }

        .tips h4 {
            font-size: 0.9375rem;
            margin-bottom: 12px;
        }

        .tips ul {
            list-style: none;
            font-size: 0.875rem;
            color: var(--color-text-light);
        }

        .tips li {
            padding: 4px 0 4px 20px;
            position: relative;
        }

        .tips li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--color-success);
        }

        /* Project Summary Beautiful */
        .project-summary {
            background: var(--color-white);
            border-radius: 16px;
            padding: 28px;
            margin-bottom: 32px;
            border: 1px solid var(--color-border);
            box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        }

        .project-summary h3 {
            font-size: 1.125rem;
            font-weight: 700;
            margin-bottom: 20px;
            color: var(--color-text);
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 12px;
            border-bottom: 1px solid var(--color-border);
        }

        .summary-content {
            color: var(--color-text);
            line-height: 1.9;
            font-size: 0.9375rem;
        }

        .summary-content br + br {
            display: block;
            margin-top: 16px;
            content: "";
        }

        /* Section labels in summary */
        .summary-section {
            margin-bottom: 20px;
            padding-bottom: 16px;
            border-bottom: 1px dashed var(--color-border);
        }

        .summary-section:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }

        .summary-label {
            display: inline-block;
            font-weight: 600;
            color: var(--color-primary);
            font-size: 0.8125rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 8px;
        }

        .summary-text {
            color: var(--color-text);
            line-height: 1.7;
            background: var(--color-bg);
            padding: 12px 16px;
            border-radius: 8px;
            border-left: 3px solid var(--color-primary);
        }

        .summary-detail {
            color: var(--color-text);
            line-height: 1.9;
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.05) 0%, rgba(6, 182, 212, 0.05) 100%);
            padding: 20px 24px;
            border-radius: 12px;
            border: 1px solid rgba(99, 102, 241, 0.2);
            font-size: 0.9375rem;
        }

        .summary-detail strong {
            color: var(--color-primary);
            font-weight: 700;
            display: block;
            margin-top: 20px;
            margin-bottom: 12px;
            font-size: 0.9375rem;
            padding-bottom: 8px;
            border-bottom: 1px solid rgba(99, 102, 241, 0.2);
        }

        .summary-detail strong:first-child {
            margin-top: 0;
        }

        .step-number {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 22px;
            height: 22px;
            background: var(--color-primary);
            color: white;
            border-radius: 50%;
            font-size: 0.75rem;
            font-weight: 600;
            margin-right: 10px;
        }

        .step-check {
            color: var(--color-success);
            font-weight: bold;
            margin-right: 8px;
        }

        .summary-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .summary-list li {
            padding: 6px 0 6px 24px;
            position: relative;
            color: var(--color-text);
        }

        .summary-list li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: var(--color-success);
            font-weight: bold;
        }

        /* Modules Breakdown */
        .modules-section {
            margin-bottom: 32px;
        }

        .modules-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 12px;
            border-bottom: 2px solid var(--color-border);
        }

        .modules-header h3 {
            font-size: 1.125rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .modules-total {
            font-size: 0.875rem;
            color: var(--color-text-light);
            background: var(--color-bg);
            padding: 8px 16px;
            border-radius: 50px;
        }

        .modules-total strong {
            color: var(--color-primary);
        }

        .modules-list {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .module-card {
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 20px;
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            transition: all 0.3s ease;
        }

        .module-card:hover {
            border-color: var(--color-primary);
            box-shadow: 0 4px 12px rgba(99, 102, 241, 0.1);
            transform: translateX(4px);
        }

        .module-icon {
            width: 50px;
            height: 50px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 12px;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .module-info {
            flex: 1;
            min-width: 0;
        }

        .module-name {
            font-weight: 600;
            font-size: 1rem;
            margin-bottom: 4px;
            color: var(--color-text);
        }

        .module-desc {
            font-size: 0.8125rem;
            color: var(--color-text-light);
            line-height: 1.6;
        }

        .module-steps {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }

        .module-step {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 4px 10px;
            background: var(--color-bg);
            border-radius: 4px;
            font-size: 0.75rem;
            color: var(--color-text);
        }

        .module-step::before {
            content: "→";
            color: var(--color-primary);
            font-size: 0.625rem;
        }

        .module-hours {
            text-align: right;
            flex-shrink: 0;
        }

        .module-hours-value {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--color-primary);
        }

        .module-hours-label {
            font-size: 0.8125rem;
            color: var(--color-success);
            font-weight: 600;
        }

        .module-hours-days {
            font-size: 0.75rem;
            color: var(--color-text-light);
            margin-top: 4px;
        }

        /* Investment Breakdown */
        .investment-breakdown {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            margin-bottom: 32px;
        }

        .investment-card {
            background: var(--color-white);
            border: 1px solid var(--color-border);
            border-radius: 12px;
            padding: 20px;
            text-align: center;
        }

        .investment-card-icon {
            font-size: 1.5rem;
            margin-bottom: 8px;
        }

        .investment-card-value {
            font-size: 1.25rem;
            font-weight: 700;
            color: var(--color-text);
            margin-bottom: 4px;
        }

        .investment-card-label {
            font-size: 0.75rem;
            color: var(--color-text-light);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        /* Footer */
        .page-footer {
            text-align: center;
            margin-top: 32px;
        }

        .page-footer a {
            color: var(--color-primary);
            text-decoration: none;
        }

        .page-footer a:hover { text-decoration: underline; }

        /* Loading */
        .loading {
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

        .loading.active { display: flex; }

        .spinner {
            width: 60px;
            height: 60px;
            border: 4px solid var(--color-border);
            border-top-color: var(--color-primary);
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        /* Responsive */
        @media (max-width: 768px) {
            .container { padding: 24px 16px; }
            .page-title { font-size: 1.75rem; }
            .card { padding: 24px; }
            .result-header { margin: -24px -24px 24px -24px; padding: 24px; }
            .form-row { grid-template-columns: 1fr; }
            .form-group.full { grid-column: span 1; }
            .features-grid { grid-template-columns: 1fr; }
            .price-details { flex-direction: column; gap: 16px; }
            .cta-buttons { flex-direction: column; }
            .cta-buttons .btn { width: 100%; }
            .investment-breakdown { grid-template-columns: 1fr; }
            .module-card { flex-direction: column; text-align: center; gap: 12px; padding: 16px; }
            .module-hours { text-align: center; }
            .module-steps { justify-content: center; }
            .modules-header { flex-direction: column; gap: 12px; align-items: flex-start; }
            .project-summary { padding: 20px; }
        }
    </style>
    <!-- Favicon -->
    <link rel="icon" href="favicon.ico?v=20260915" sizes="any">
    <link rel="icon" type="image/png" sizes="32x32" href="img/favicon-32x32.png?v=20260915">
    <link rel="apple-touch-icon" sizes="180x180" href="img/apple-touch-icon.png">
    <link rel="manifest" href="site.webmanifest">
    <meta name="theme-color" content="#0f2a6b">
</head>
<body>
    <div class="loading" id="loading">
        <div class="spinner"></div>
        <p>Analisando seu projeto com IA...</p>
    </div>

    <div class="container">
        <!-- Header -->
        <header class="page-header">
            <div class="logo">
                <a href="index.html"><img src="logo.svg" alt="LS Global Tecnologia"></a>
            </div>
            <?php if (!$resultado): ?>
            <h1 class="page-title">Simule seu Orçamento</h1>
            <p class="page-subtitle">Descreva sua ideia e receba uma estimativa automática de custo e prazo.</p>
            <?php else: ?>
            <h1 class="page-title">Seu Pré-Orçamento</h1>
            <p class="page-subtitle">Confira abaixo a estimativa do seu projeto</p>
            <?php endif; ?>
        </header>

        <?php if ($erro): ?>
        <div class="alert alert-error"><?= h($erro) ?></div>
        <?php endif; ?>

        <?php if ($resultado): ?>
        <!-- RESULTADO -->
        <div class="card">
            <div class="result-header">
                <h2>Olá, <?= h($resultado['nome']) ?>!</h2>
                <p><?= h($resultado['tipo_projeto_texto']) ?></p>
            </div>

            <!-- Investimento Principal -->
            <div class="price-box">
                <p class="price-label">Investimento Estimado</p>
                <p class="price-value"><?= h($resultado['valor_texto']) ?></p>
                <div class="price-details">
                    <div class="price-detail">
                        <span class="complexity-badge complexity-<?= $resultado['complexidade'] ?>">
                            Complexidade <?= ucfirst($resultado['complexidade']) ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Cards de Resumo -->
            <div class="investment-breakdown">
                <div class="investment-card">
                    <div class="investment-card-icon">📦</div>
                    <div class="investment-card-value"><?= count($resultado['modulos'] ?? []) ?></div>
                    <div class="investment-card-label">Módulos</div>
                </div>
                <div class="investment-card">
                    <div class="investment-card-icon">⏱️</div>
                    <div class="investment-card-value"><?= $resultado['horas_estimadas'] ?>h</div>
                    <div class="investment-card-label">Total Horas</div>
                </div>
                <div class="investment-card">
                    <div class="investment-card-icon">📅</div>
                    <div class="investment-card-value"><?= $resultado['prazo_min'] ?>-<?= $resultado['prazo_max'] ?></div>
                    <div class="investment-card-label">Dias Úteis</div>
                </div>
            </div>

            <!-- Resumo do Projeto - O que o cliente pediu -->
            <div class="project-summary">
                <h3>📋 Seu Projeto</h3>

                <div class="summary-section">
                    <div class="summary-label">O que você pediu</div>
                    <div class="summary-text">
                        <?= nl2br(h($resultado['descricao_original'] ?? '')) ?>
                    </div>
                </div>

                <?php if (!empty($resultado['analise']['resumo_melhorado'])): ?>
                <div class="summary-section">
                    <div class="summary-label">Como ficaria o sistema</div>
                    <div class="summary-detail">
                        <?php
                        $texto = $resultado['analise']['resumo_melhorado'];
                        // Primeiro escapar HTML
                        $texto = h($texto);
                        // Converter **texto** para <strong>texto</strong>
                        $texto = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $texto);
                        // Converter números no início da linha para badges
                        $texto = preg_replace('/^(\d+)\.\s*/m', '<span class="step-number">$1</span>', $texto);
                        // Converter ✓ para check verde
                        $texto = str_replace('✓', '<span class="step-check">✓</span>', $texto);
                        // Converter quebras de linha
                        $texto = nl2br($texto);
                        echo $texto;
                        ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (!empty($resultado['analise']['funcionalidades'])): ?>
                <div class="summary-section">
                    <div class="summary-label">Funcionalidades incluídas</div>
                    <ul class="summary-list">
                        <?php foreach ($resultado['analise']['funcionalidades'] as $func): ?>
                        <li><?= h($func['nome'] ?? '') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>

                <?php if (!empty($resultado['analise']['integracoes'])): ?>
                <div class="summary-section">
                    <div class="summary-label">Integrações incluídas</div>
                    <ul class="summary-list">
                        <?php foreach ($resultado['analise']['integracoes'] as $integ): ?>
                        <li><?= h($integ['nome'] ?? '') ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endif; ?>
            </div>

            <!-- Detalhamento por Módulo -->
            <?php if (!empty($resultado['modulos'])): ?>
            <div class="modules-section">
                <div class="modules-header">
                    <h3>🔧 Detalhamento por Módulo</h3>
                    <span class="modules-total"><strong><?= $resultado['horas_estimadas'] ?>h</strong> total</span>
                </div>
                <div class="modules-list">
                    <?php foreach ($resultado['modulos'] as $mod):
                        $dias = ceil($mod['horas'] / 6);
                        $valorMod = $mod['horas'] * VALOR_HORA;
                        $cor = $mod['cor'] ?? '#6366f1';
                    ?>
                    <div class="module-card">
                        <div class="module-icon" style="background: <?= $cor ?>15; color: <?= $cor ?>;">
                            <?= $mod['icone'] ?>
                        </div>
                        <div class="module-info">
                            <div class="module-name"><?= h($mod['nome']) ?></div>
                            <div class="module-steps">
                                <?php
                                $passos = explode(' • ', $mod['descricao']);
                                foreach ($passos as $passo):
                                ?>
                                <span class="module-step"><?= h(trim($passo)) ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="module-hours">
                            <div class="module-hours-value"><?= $mod['horas'] ?>h</div>
                            <div class="module-hours-label">R$ <?= number_format($valorMod, 0, ',', '.') ?></div>
                            <div class="module-hours-days">≈ <?= $dias ?> <?= $dias > 1 ? 'dias' : 'dia' ?></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

            <div class="alert alert-warning">
                <strong>⚠️ Importante:</strong> Esta é uma estimativa inicial automática baseada na descrição fornecida. O valor e prazo finais serão definidos após análise detalhada pela nossa equipe técnica.
            </div>

            <div class="cta-buttons">
                <a href="https://wa.me/<?= WHATSAPP ?>?text=<?= urlencode("Olá! Vi o pré-orçamento do meu projeto e gostaria de agendar uma reunião para detalhar melhor.") ?>" class="btn btn-whatsapp" target="_blank">
                    💬 Falar no WhatsApp
                </a>
                <a href="orcamento.php" class="btn btn-outline">
                    ← Fazer Nova Simulação
                </a>
            </div>
        </div>

        <?php else: ?>
        <!-- FORMULÁRIO -->
        <div class="card">
            <form method="POST" id="orcamentoForm">
                <div class="section">
                    <h3 class="section-title">Seus Dados</h3>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="nome">Nome completo <span class="required">*</span></label>
                            <input type="text" id="nome" name="nome" required placeholder="Seu nome">
                        </div>
                        <div class="form-group">
                            <label for="email">E-mail <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="seu@email.com">
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="whatsapp">WhatsApp <span class="required">*</span></label>
                            <input type="tel" id="whatsapp" name="whatsapp" required placeholder="(61) 99999-9999">
                        </div>
                        <div class="form-group">
                            <label for="tipo_projeto">Tipo de projeto <span class="required">*</span></label>
                            <select id="tipo_projeto" name="tipo_projeto" required>
                                <option value="">Selecione...</option>
                                <option value="site">Site Institucional / Landing Page</option>
                                <option value="sistema_web">Sistema Web / Plataforma Online</option>
                                <option value="aplicativo">Aplicativo Mobile (iOS/Android)</option>
                                <option value="automacao">Automação de Processos</option>
                                <option value="integracao">Integração de Sistemas / APIs</option>
                                <option value="outro">Outro</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3 class="section-title">Descreva seu Projeto</h3>
                    <div class="form-group full">
                        <label for="descricao">Descrição detalhada <span class="required">*</span></label>
                        <textarea id="descricao" name="descricao" required minlength="20"
                            placeholder="Descreva sua ideia de sistema ou aplicativo. Quanto mais detalhes, melhor a estimativa.

Exemplo: Preciso de um sistema para gerenciar minha clínica médica com agendamento de consultas, cadastro de pacientes, prontuário eletrônico e controle financeiro."></textarea>
                    </div>
                </div>

                <div class="tips">
                    <h4>💡 Dicas para uma boa descrição</h4>
                    <ul>
                        <li>Descreva o objetivo principal do sistema</li>
                        <li>Liste as funcionalidades que você precisa</li>
                        <li>Mencione integrações desejadas (pagamentos, WhatsApp, etc)</li>
                        <li>Indique se precisa de aplicativo mobile</li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary">
                    Gerar Orçamento Automático →
                </button>
            </form>
        </div>
        <?php endif; ?>

        <footer class="page-footer">
            <p><a href="index.html">← Voltar ao site</a></p>
            <p style="margin-top: 12px; color: var(--color-text-light); font-size: 0.875rem;">
                LS Global Tecnologia - Desenvolvimento de Sistemas e Aplicativos
            </p>
        </footer>
    </div>

    <script>
        // Loading on submit
        document.getElementById('orcamentoForm')?.addEventListener('submit', function() {
            document.getElementById('loading').classList.add('active');
        });

        // WhatsApp mask
        document.getElementById('whatsapp')?.addEventListener('input', function(e) {
            let v = e.target.value.replace(/\D/g, '');
            if (v.length <= 11) {
                if (v.length > 6) v = `(${v.slice(0,2)}) ${v.slice(2,7)}-${v.slice(7)}`;
                else if (v.length > 2) v = `(${v.slice(0,2)}) ${v.slice(2)}`;
                else if (v.length > 0) v = `(${v}`;
            }
            e.target.value = v;
        });
    </script>
</body>
</html>
