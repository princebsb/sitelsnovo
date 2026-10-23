-- ============================================
-- LS GLOBAL TECNOLOGIA
-- Sistema de Simulação de Pré-Orçamento com IA
-- ============================================

-- Criar banco de dados (se necessário)
-- CREATE DATABASE IF NOT EXISTS lsglobal_orcamento CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE lsglobal_orcamento;

-- Tabela principal de leads e orçamentos
CREATE TABLE IF NOT EXISTS leads_orcamento (
    id INT AUTO_INCREMENT PRIMARY KEY,

    -- Dados do lead
    nome VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL,
    whatsapp VARCHAR(30) NOT NULL,

    -- Dados do projeto
    tipo_projeto VARCHAR(50) NOT NULL,
    descricao_original TEXT NOT NULL,
    descricao_melhorada TEXT,

    -- Análise da IA (JSON)
    funcionalidades JSON,
    integracoes JSON,
    sugestoes JSON,

    -- Classificação
    complexidade VARCHAR(20) DEFAULT 'media',

    -- Estimativas calculadas
    horas_estimadas INT DEFAULT 0,
    valor_min DECIMAL(10,2) DEFAULT 0.00,
    valor_max DECIMAL(10,2) DEFAULT 0.00,
    prazo_min INT DEFAULT 0,
    prazo_max INT DEFAULT 0,

    -- Controle
    status ENUM('novo', 'analisado', 'contatado', 'convertido', 'descartado') DEFAULT 'novo',
    ip_address VARCHAR(45),
    user_agent TEXT,

    -- Timestamps
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    -- Índices
    INDEX idx_email (email),
    INDEX idx_status (status),
    INDEX idx_created (created_at),
    INDEX idx_tipo (tipo_projeto)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de log de requisições à IA (opcional, para controle de custos)
CREATE TABLE IF NOT EXISTS logs_ia (
    id INT AUTO_INCREMENT PRIMARY KEY,
    lead_id INT,
    prompt_enviado TEXT,
    resposta_ia TEXT,
    tokens_usados INT DEFAULT 0,
    tempo_resposta DECIMAL(5,2),
    modelo_usado VARCHAR(50),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (lead_id) REFERENCES leads_orcamento(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabela de configurações do sistema
CREATE TABLE IF NOT EXISTS config_orcamento (
    id INT AUTO_INCREMENT PRIMARY KEY,
    chave VARCHAR(100) UNIQUE NOT NULL,
    valor TEXT,
    descricao VARCHAR(255),
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir configurações padrão
INSERT INTO config_orcamento (chave, valor, descricao) VALUES
('valor_hora', '180', 'Valor da hora de desenvolvimento em R$'),
('fator_baixa', '1.0', 'Multiplicador para complexidade baixa'),
('fator_media', '1.2', 'Multiplicador para complexidade média'),
('fator_alta', '1.5', 'Multiplicador para complexidade alta'),
('margem_min', '0.9', 'Fator para cálculo do valor mínimo'),
('margem_max', '1.25', 'Fator para cálculo do valor máximo'),
('horas_dia', '6', 'Horas produtivas por dia para cálculo de prazo mínimo'),
('horas_dia_max', '4', 'Horas produtivas por dia para cálculo de prazo máximo'),
('api_ia_provider', 'anthropic', 'Provedor de IA (anthropic, openai)'),
('api_ia_model', 'claude-sonnet-4-20250514', 'Modelo de IA a ser usado'),
('email_notificacao', 'contato@lsglobaltecnologia.com.br', 'Email para notificações de novos leads');

-- Tabela de funcionalidades padrão com horas estimadas
CREATE TABLE IF NOT EXISTS funcionalidades_padrao (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    slug VARCHAR(100) UNIQUE NOT NULL,
    horas INT NOT NULL,
    categoria VARCHAR(50),
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Inserir funcionalidades padrão
INSERT INTO funcionalidades_padrao (nome, slug, horas, categoria, descricao) VALUES
('Login de usuários', 'login', 8, 'autenticacao', 'Sistema de autenticação com login e logout'),
('Cadastro de usuários', 'cadastro', 10, 'autenticacao', 'Registro de novos usuários com validação'),
('Recuperação de senha', 'recuperar_senha', 6, 'autenticacao', 'Fluxo de recuperação de senha por email'),
('Painel administrativo', 'painel_admin', 25, 'gestao', 'Dashboard administrativo completo'),
('CRUD básico', 'crud_basico', 12, 'gestao', 'Operações de criar, ler, atualizar e deletar'),
('Gestão de permissões', 'permissoes', 18, 'autenticacao', 'Controle de acesso por níveis'),
('Agendamento', 'agendamento', 18, 'funcionalidade', 'Sistema de agendamento com calendário'),
('Pagamento online', 'pagamento', 20, 'integracao', 'Integração com gateway de pagamento'),
('Chat em tempo real', 'chat', 35, 'funcionalidade', 'Sistema de mensagens em tempo real'),
('Relatórios', 'relatorios', 16, 'gestao', 'Geração de relatórios e exportação'),
('Integração com API', 'integracao_api', 24, 'integracao', 'Conexão com APIs externas'),
('Notificações push', 'notificacoes_push', 15, 'funcionalidade', 'Sistema de notificações push'),
('Notificações por email', 'notificacoes_email', 10, 'funcionalidade', 'Envio de emails automáticos'),
('Upload de arquivos', 'upload', 8, 'funcionalidade', 'Sistema de upload e gestão de arquivos'),
('Galeria de imagens', 'galeria', 12, 'funcionalidade', 'Galeria com visualização de imagens'),
('Busca avançada', 'busca', 14, 'funcionalidade', 'Sistema de busca com filtros'),
('Carrinho de compras', 'carrinho', 16, 'ecommerce', 'Carrinho de compras completo'),
('Catálogo de produtos', 'catalogo', 20, 'ecommerce', 'Listagem e gestão de produtos'),
('Checkout', 'checkout', 18, 'ecommerce', 'Processo de finalização de compra'),
('Dashboard com gráficos', 'dashboard_graficos', 20, 'gestao', 'Painel com visualização de dados'),
('API REST', 'api_rest', 30, 'integracao', 'Desenvolvimento de API REST completa'),
('Integração WhatsApp', 'whatsapp', 16, 'integracao', 'Integração com WhatsApp Business API'),
('Integração redes sociais', 'redes_sociais', 14, 'integracao', 'Login e compartilhamento social'),
('Geolocalização', 'geolocalizacao', 12, 'funcionalidade', 'Recursos baseados em localização'),
('Multi-idioma', 'multi_idioma', 20, 'funcionalidade', 'Suporte a múltiplos idiomas'),
('PWA', 'pwa', 16, 'mobile', 'Progressive Web App'),
('App híbrido', 'app_hibrido', 80, 'mobile', 'Aplicativo para iOS e Android'),
('Backup automático', 'backup', 8, 'infraestrutura', 'Sistema de backup automatizado'),
('Logs e auditoria', 'logs', 12, 'infraestrutura', 'Registro de ações e auditoria');
