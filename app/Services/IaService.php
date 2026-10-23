<?php

namespace App\Services;

use CodeIgniter\HTTP\CURLRequest;
use Exception;

/**
 * IaService
 *
 * Serviço para integração com APIs de Inteligência Artificial
 * Suporta: Anthropic (Claude), OpenAI (GPT)
 *
 * @package App\Services
 */
class IaService
{
    protected string $provider;
    protected string $apiKey;
    protected string $model;
    protected CURLRequest $client;

    // Configurações dos provedores
    protected array $providers = [
        'anthropic' => [
            'url'     => 'https://api.anthropic.com/v1/messages',
            'models'  => ['claude-sonnet-4-20250514', 'claude-3-haiku-20240307'],
            'version' => '2023-06-01'
        ],
        'openai' => [
            'url'    => 'https://api.openai.com/v1/chat/completions',
            'models' => ['gpt-4o', 'gpt-4o-mini']
        ]
    ];

    public function __construct()
    {
        $this->provider = env('IA_PROVIDER', 'anthropic');
        $this->apiKey   = env('IA_API_KEY', '');
        $this->model    = env('IA_MODEL', 'claude-sonnet-4-20250514');

        $this->client = service('curlrequest', [
            'timeout'  => 60,
            'connect_timeout' => 10
        ]);
    }

    /**
     * Analisar descrição do projeto usando IA
     *
     * @param string $descricao Descrição do projeto enviada pelo usuário
     * @param string $tipoProjeto Tipo de projeto selecionado
     * @return array Análise estruturada do projeto
     */
    public function analisarProjeto(string $descricao, string $tipoProjeto = ''): array
    {
        $prompt = $this->construirPrompt($descricao, $tipoProjeto);

        try {
            $resposta = match ($this->provider) {
                'anthropic' => $this->chamarAnthropic($prompt),
                'openai'    => $this->chamarOpenAI($prompt),
                default     => throw new Exception("Provedor de IA não suportado: {$this->provider}")
            };

            return $this->processarResposta($resposta);

        } catch (Exception $e) {
            log_message('error', 'IaService Error: ' . $e->getMessage());

            // Retornar análise padrão em caso de erro
            return $this->analiseDefault($descricao, $tipoProjeto);
        }
    }

    /**
     * Construir prompt para análise
     */
    protected function construirPrompt(string $descricao, string $tipoProjeto): string
    {
        $tipoTexto = $this->traduzirTipoProjeto($tipoProjeto);

        return <<<PROMPT
Você é um analista de requisitos de software experiente da LS Global Tecnologia.

Analise a seguinte descrição de projeto enviada por um potencial cliente e forneça uma análise técnica estruturada.

TIPO DE PROJETO SELECIONADO: {$tipoTexto}

DESCRIÇÃO DO CLIENTE:
"{$descricao}"

Analise e retorne APENAS um JSON válido (sem markdown, sem explicações) com a seguinte estrutura:

{
    "tipo_projeto": "string (site|sistema_web|aplicativo|automacao|integracao)",
    "resumo_melhorado": "string (descrição técnica clara e profissional do projeto em 2-3 frases)",
    "funcionalidades": [
        {
            "nome": "string (nome da funcionalidade)",
            "slug": "string (identificador: login, cadastro, painel_admin, crud_basico, agendamento, pagamento, chat, relatorios, integracao_api, notificacoes_push, notificacoes_email, upload, galeria, busca, carrinho, catalogo, checkout, dashboard_graficos, api_rest, whatsapp, redes_sociais, geolocalizacao, multi_idioma, pwa, app_hibrido, backup, logs, permissoes, recuperar_senha)",
            "descricao": "string (breve descrição da funcionalidade no contexto do projeto)"
        }
    ],
    "integracoes": [
        {
            "nome": "string (nome da integração)",
            "tipo": "string (pagamento|comunicacao|dados|autenticacao|outros)",
            "descricao": "string (como será usada)"
        }
    ],
    "complexidade": "string (baixa|media|alta)",
    "justificativa_complexidade": "string (explicação breve da classificação)",
    "sugestoes": [
        "string (sugestão de melhoria ou funcionalidade adicional recomendada)"
    ],
    "riscos": [
        "string (possíveis riscos ou pontos de atenção)"
    ],
    "tecnologias_recomendadas": [
        "string (tecnologias sugeridas para o projeto)"
    ]
}

REGRAS:
1. Identifique TODAS as funcionalidades mencionadas ou implícitas
2. Use os slugs padronizados quando possível
3. Seja realista na classificação de complexidade
4. Sugira funcionalidades que agreguem valor ao projeto
5. Retorne APENAS o JSON, sem texto adicional
PROMPT;
    }

    /**
     * Chamar API da Anthropic (Claude)
     */
    protected function chamarAnthropic(string $prompt): string
    {
        $config = $this->providers['anthropic'];

        $response = $this->client->request('POST', $config['url'], [
            'headers' => [
                'Content-Type'      => 'application/json',
                'x-api-key'         => $this->apiKey,
                'anthropic-version' => $config['version']
            ],
            'json' => [
                'model'      => $this->model,
                'max_tokens' => 2048,
                'messages'   => [
                    [
                        'role'    => 'user',
                        'content' => $prompt
                    ]
                ]
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        if (isset($body['content'][0]['text'])) {
            return $body['content'][0]['text'];
        }

        throw new Exception('Resposta inválida da API Anthropic');
    }

    /**
     * Chamar API da OpenAI (GPT)
     */
    protected function chamarOpenAI(string $prompt): string
    {
        $config = $this->providers['openai'];

        $response = $this->client->request('POST', $config['url'], [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => "Bearer {$this->apiKey}"
            ],
            'json' => [
                'model'       => $this->model,
                'max_tokens'  => 2048,
                'temperature' => 0.3,
                'messages'    => [
                    [
                        'role'    => 'system',
                        'content' => 'Você é um analista de requisitos de software. Responda apenas em JSON válido.'
                    ],
                    [
                        'role'    => 'user',
                        'content' => $prompt
                    ]
                ]
            ]
        ]);

        $body = json_decode($response->getBody(), true);

        if (isset($body['choices'][0]['message']['content'])) {
            return $body['choices'][0]['message']['content'];
        }

        throw new Exception('Resposta inválida da API OpenAI');
    }

    /**
     * Processar resposta da IA
     */
    protected function processarResposta(string $resposta): array
    {
        // Limpar possíveis marcadores de código
        $resposta = preg_replace('/```json\s*/', '', $resposta);
        $resposta = preg_replace('/```\s*/', '', $resposta);
        $resposta = trim($resposta);

        $dados = json_decode($resposta, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('Erro ao decodificar resposta da IA: ' . json_last_error_msg());
        }

        // Validar e normalizar dados
        return [
            'tipo_projeto'               => $dados['tipo_projeto'] ?? 'sistema_web',
            'resumo_melhorado'           => $dados['resumo_melhorado'] ?? '',
            'funcionalidades'            => $dados['funcionalidades'] ?? [],
            'integracoes'                => $dados['integracoes'] ?? [],
            'complexidade'               => $this->normalizarComplexidade($dados['complexidade'] ?? 'media'),
            'justificativa_complexidade' => $dados['justificativa_complexidade'] ?? '',
            'sugestoes'                  => $dados['sugestoes'] ?? [],
            'riscos'                     => $dados['riscos'] ?? [],
            'tecnologias_recomendadas'   => $dados['tecnologias_recomendadas'] ?? []
        ];
    }

    /**
     * Análise padrão quando a IA falha
     */
    protected function analiseDefault(string $descricao, string $tipoProjeto): array
    {
        // Análise básica por palavras-chave
        $descricaoLower = mb_strtolower($descricao);
        $funcionalidades = [];

        $keywords = [
            'login'       => ['login', 'entrar', 'autenticação', 'autenticar'],
            'cadastro'    => ['cadastro', 'registro', 'registrar', 'criar conta'],
            'painel_admin'=> ['painel', 'admin', 'administrativo', 'dashboard', 'gestão'],
            'pagamento'   => ['pagamento', 'pagar', 'cobrança', 'cartão', 'pix', 'boleto'],
            'relatorios'  => ['relatório', 'relatorios', 'exportar', 'pdf', 'excel'],
            'agendamento' => ['agenda', 'agendamento', 'horário', 'reserva', 'marcar'],
            'chat'        => ['chat', 'mensagem', 'conversa', 'tempo real'],
            'upload'      => ['upload', 'arquivo', 'anexo', 'documento', 'imagem'],
            'busca'       => ['busca', 'pesquisa', 'filtro', 'procurar'],
            'notificacoes_email' => ['email', 'notificação', 'aviso', 'alerta'],
            'carrinho'    => ['carrinho', 'compra', 'produto', 'loja'],
            'api_rest'    => ['api', 'integração', 'conectar', 'webhook']
        ];

        foreach ($keywords as $slug => $termos) {
            foreach ($termos as $termo) {
                if (strpos($descricaoLower, $termo) !== false) {
                    $funcionalidades[] = [
                        'nome'      => ucfirst(str_replace('_', ' ', $slug)),
                        'slug'      => $slug,
                        'descricao' => "Funcionalidade identificada automaticamente"
                    ];
                    break;
                }
            }
        }

        // Determinar complexidade pelo tamanho
        $complexidade = count($funcionalidades) > 5 ? 'alta' :
                       (count($funcionalidades) > 2 ? 'media' : 'baixa');

        return [
            'tipo_projeto'               => $tipoProjeto ?: 'sistema_web',
            'resumo_melhorado'           => $descricao,
            'funcionalidades'            => $funcionalidades,
            'integracoes'                => [],
            'complexidade'               => $complexidade,
            'justificativa_complexidade' => 'Análise automática baseada nas funcionalidades detectadas.',
            'sugestoes'                  => ['Agende uma reunião para detalhamento completo do projeto.'],
            'riscos'                     => [],
            'tecnologias_recomendadas'   => ['PHP', 'CodeIgniter 4', 'MySQL', 'Bootstrap']
        ];
    }

    /**
     * Normalizar valor de complexidade
     */
    protected function normalizarComplexidade(string $valor): string
    {
        $valor = mb_strtolower(trim($valor));

        return match (true) {
            in_array($valor, ['baixa', 'simples', 'low', 'simple'])  => 'baixa',
            in_array($valor, ['alta', 'complexa', 'high', 'complex']) => 'alta',
            default => 'media'
        };
    }

    /**
     * Traduzir tipo de projeto para texto
     */
    protected function traduzirTipoProjeto(string $tipo): string
    {
        return match ($tipo) {
            'site'        => 'Site institucional ou landing page',
            'sistema_web' => 'Sistema web / Plataforma online',
            'aplicativo'  => 'Aplicativo mobile (iOS/Android)',
            'automacao'   => 'Automação de processos',
            'integracao'  => 'Integração de sistemas / APIs',
            'outro'       => 'Outro tipo de projeto',
            default       => 'Não especificado'
        };
    }

    /**
     * Verificar se o serviço está configurado
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Obter provedor atual
     */
    public function getProvider(): string
    {
        return $this->provider;
    }
}
