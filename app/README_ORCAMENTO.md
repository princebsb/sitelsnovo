# Sistema de Simulação de Pré-Orçamento com IA

## LS Global Tecnologia

Sistema completo para simulação automática de pré-orçamentos utilizando Inteligência Artificial.

---

## Estrutura de Arquivos

```
app/
├── Config/
│   └── Routes_orcamento.php    # Rotas do sistema
├── Controllers/
│   └── Orcamento.php           # Controller principal
├── Models/
│   └── OrcamentoModel.php      # Model de dados
├── Services/
│   ├── IaService.php           # Integração com IA (Anthropic/OpenAI)
│   └── EstimativaService.php   # Cálculo de estimativas
├── Views/
│   ├── orcamento/
│   │   ├── orcamento_form.php      # Formulário de simulação
│   │   └── orcamento_resultado.php # Página de resultado
│   └── emails/
│       ├── novo_lead.php           # Email para empresa
│       └── orcamento_cliente.php   # Email para cliente
└── Database/
    └── leads_orcamento.sql     # Script SQL das tabelas
```

---

## Instalação

### 1. Banco de Dados

Execute o script SQL para criar as tabelas:

```bash
mysql -u root -p < app/Database/leads_orcamento.sql
```

Ou importe via phpMyAdmin.

### 2. Configuração

Copie as configurações para seu `.env`:

```bash
cat .env.orcamento >> .env
```

Edite o arquivo `.env` e configure:

- Credenciais do banco de dados
- Chave da API de IA (Anthropic ou OpenAI)
- Configurações de email

### 3. Rotas

Adicione as rotas ao seu `app/Config/Routes.php`:

```php
// No final do arquivo, antes de fechar
$routes->get('orcamento', 'Orcamento::index');
$routes->get('simular-orcamento', 'Orcamento::index');
$routes->post('orcamento/processar', 'Orcamento::processar');
$routes->get('orcamento/resultado/(:num)', 'Orcamento::resultado/$1');
$routes->post('orcamento/solicitar-proposta', 'Orcamento::solicitarProposta');
```

### 4. API de IA

Obtenha sua chave de API:

- **Anthropic (Claude)**: https://console.anthropic.com
- **OpenAI (GPT)**: https://platform.openai.com

Configure no `.env`:

```
IA_PROVIDER = anthropic
IA_API_KEY = sua_chave_aqui
IA_MODEL = claude-sonnet-4-20250514
```

---

## Uso

### Acesso

- Formulário: `http://seusite.com/orcamento`
- URL alternativa: `http://seusite.com/simular-orcamento`

### Fluxo

1. Usuário preenche formulário com dados e descrição do projeto
2. Sistema envia descrição para IA analisar
3. IA retorna funcionalidades, integrações e complexidade
4. Sistema calcula horas, prazo e valores
5. Usuário visualiza pré-orçamento
6. Lead é salvo no banco de dados
7. Emails são enviados (cliente e empresa)

---

## Tabela de Horas por Funcionalidade

| Funcionalidade | Horas |
|----------------|-------|
| Login | 8h |
| Cadastro | 10h |
| Painel Admin | 25h |
| CRUD básico | 12h |
| Agendamento | 18h |
| Pagamento Online | 20h |
| Chat | 35h |
| Relatórios | 16h |
| Integração API | 24h |
| API REST | 30h |
| App Híbrido | 80h |

---

## Multiplicadores de Complexidade

| Complexidade | Fator |
|--------------|-------|
| Baixa | 1.0x |
| Média | 1.2x |
| Alta | 1.5x |

---

## Fórmulas de Cálculo

```
Horas = (Horas Base + Funcionalidades + Integrações) × Fator Complexidade

Valor Base = Horas × R$ 180,00

Valor Mínimo = Valor Base × 0.90
Valor Máximo = Valor Base × 1.25

Prazo Mínimo = Horas ÷ 6 (dias)
Prazo Máximo = Horas ÷ 4 (dias)
```

---

## Personalização

### Alterar Valor da Hora

Edite `EstimativaService.php`:

```php
protected float $valorHora = 180.00; // Altere aqui
```

### Adicionar Funcionalidades

Adicione ao array em `EstimativaService.php`:

```php
protected array $horasPorFuncionalidade = [
    'nova_funcionalidade' => 20,
    // ...
];
```

### Modificar Prompt da IA

Edite o método `construirPrompt()` em `IaService.php`.

---

## Segurança

- Proteção CSRF em todos os formulários
- Validação de dados no backend
- Rate limiting (1 orçamento a cada 5 minutos por email)
- Sanitização de inputs
- Logs de erros

---

## Suporte

Em caso de dúvidas ou problemas:
- Email: contato@lsglobaltecnologia.com.br
- WhatsApp: (11) 99999-9999
