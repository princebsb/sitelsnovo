<?php

namespace App\Services;

/**
 * EstimativaService
 *
 * Serviço para cálculo de estimativas de horas, prazo e valor
 * baseado nas funcionalidades detectadas pela IA
 *
 * @package App\Services
 */
class EstimativaService
{
    // Tabela de horas por funcionalidade (slug => horas)
    protected array $horasPorFuncionalidade = [
        // Autenticação
        'login'            => 8,
        'cadastro'         => 10,
        'recuperar_senha'  => 6,
        'permissoes'       => 18,

        // Gestão
        'painel_admin'     => 25,
        'crud_basico'      => 12,
        'relatorios'       => 16,
        'dashboard_graficos' => 20,
        'logs'             => 12,
        'backup'           => 8,

        // Funcionalidades
        'agendamento'      => 18,
        'chat'             => 35,
        'upload'           => 8,
        'galeria'          => 12,
        'busca'            => 14,
        'notificacoes_push'  => 15,
        'notificacoes_email' => 10,
        'geolocalizacao'   => 12,
        'multi_idioma'     => 20,

        // E-commerce
        'carrinho'         => 16,
        'catalogo'         => 20,
        'checkout'         => 18,

        // Integrações
        'pagamento'        => 20,
        'integracao_api'   => 24,
        'api_rest'         => 30,
        'whatsapp'         => 16,
        'redes_sociais'    => 14,

        // Mobile
        'pwa'              => 16,
        'app_hibrido'      => 80,

        // Genéricos
        'funcionalidade_basica' => 8,
        'funcionalidade_media'  => 16,
        'funcionalidade_complexa' => 30
    ];

    // Configurações de cálculo
    protected float $valorHora       = 180.00;
    protected float $fatorBaixa      = 1.0;
    protected float $fatorMedia      = 1.2;
    protected float $fatorAlta       = 1.5;
    protected float $margemMin       = 0.90;
    protected float $margemMax       = 1.25;
    protected int   $horasDiaMin     = 6;  // Para prazo mínimo
    protected int   $horasDiaMax     = 4;  // Para prazo máximo

    // Horas base por tipo de projeto
    protected array $horasBasePorTipo = [
        'site'        => 40,
        'sistema_web' => 60,
        'aplicativo'  => 100,
        'automacao'   => 40,
        'integracao'  => 30,
        'outro'       => 50
    ];

    /**
     * Calcular estimativa completa
     *
     * @param array $analiseIA Resultado da análise da IA
     * @return array Estimativa calculada
     */
    public function calcular(array $analiseIA): array
    {
        // Extrair dados
        $funcionalidades = $analiseIA['funcionalidades'] ?? [];
        $integracoes     = $analiseIA['integracoes'] ?? [];
        $complexidade    = $analiseIA['complexidade'] ?? 'media';
        $tipoProjeto     = $analiseIA['tipo_projeto'] ?? 'sistema_web';

        // Calcular horas base pelo tipo de projeto
        $horasBase = $this->horasBasePorTipo[$tipoProjeto] ?? 50;

        // Calcular horas das funcionalidades
        $horasFuncionalidades = $this->calcularHorasFuncionalidades($funcionalidades);

        // Calcular horas das integrações
        $horasIntegracoes = $this->calcularHorasIntegracoes($integracoes);

        // Total de horas brutas
        $horasBrutas = $horasBase + $horasFuncionalidades + $horasIntegracoes;

        // Aplicar fator de complexidade
        $fatorComplexidade = $this->getFatorComplexidade($complexidade);
        $horasEstimadas = ceil($horasBrutas * $fatorComplexidade);

        // Calcular valores
        $valorBase = $horasEstimadas * $this->valorHora;
        $valorMin  = round($valorBase * $this->margemMin, 2);
        $valorMax  = round($valorBase * $this->margemMax, 2);

        // Calcular prazos (em dias úteis)
        $prazoMin = ceil($horasEstimadas / $this->horasDiaMin);
        $prazoMax = ceil($horasEstimadas / $this->horasDiaMax);

        // Detalhamento das horas
        $detalhamento = $this->gerarDetalhamento($funcionalidades, $integracoes, $horasBase);

        return [
            'horas_estimadas' => $horasEstimadas,
            'valor_min'       => $valorMin,
            'valor_max'       => $valorMax,
            'prazo_min'       => $prazoMin,
            'prazo_max'       => $prazoMax,
            'complexidade'    => $complexidade,
            'fator_complexidade' => $fatorComplexidade,
            'detalhamento'    => $detalhamento,
            'resumo'          => [
                'horas_base'           => $horasBase,
                'horas_funcionalidades'=> $horasFuncionalidades,
                'horas_integracoes'    => $horasIntegracoes,
                'horas_brutas'         => $horasBrutas,
                'valor_hora'           => $this->valorHora
            ]
        ];
    }

    /**
     * Calcular horas totais das funcionalidades
     */
    protected function calcularHorasFuncionalidades(array $funcionalidades): int
    {
        $total = 0;

        foreach ($funcionalidades as $func) {
            $slug = $func['slug'] ?? '';
            $total += $this->getHorasFuncionalidade($slug);
        }

        return $total;
    }

    /**
     * Obter horas de uma funcionalidade pelo slug
     */
    protected function getHorasFuncionalidade(string $slug): int
    {
        // Procurar slug exato
        if (isset($this->horasPorFuncionalidade[$slug])) {
            return $this->horasPorFuncionalidade[$slug];
        }

        // Procurar por similaridade
        foreach ($this->horasPorFuncionalidade as $key => $horas) {
            if (strpos($slug, $key) !== false || strpos($key, $slug) !== false) {
                return $horas;
            }
        }

        // Valor padrão para funcionalidades não mapeadas
        return $this->horasPorFuncionalidade['funcionalidade_media'];
    }

    /**
     * Calcular horas das integrações
     */
    protected function calcularHorasIntegracoes(array $integracoes): int
    {
        $horasPorTipo = [
            'pagamento'     => 20,
            'comunicacao'   => 16,
            'dados'         => 24,
            'autenticacao'  => 12,
            'outros'        => 18
        ];

        $total = 0;

        foreach ($integracoes as $integracao) {
            $tipo = $integracao['tipo'] ?? 'outros';
            $total += $horasPorTipo[$tipo] ?? 18;
        }

        return $total;
    }

    /**
     * Obter fator multiplicador por complexidade
     */
    protected function getFatorComplexidade(string $complexidade): float
    {
        return match ($complexidade) {
            'baixa' => $this->fatorBaixa,
            'alta'  => $this->fatorAlta,
            default => $this->fatorMedia
        };
    }

    /**
     * Gerar detalhamento das horas
     */
    protected function gerarDetalhamento(array $funcionalidades, array $integracoes, int $horasBase): array
    {
        $itens = [];

        // Horas base
        $itens[] = [
            'item'      => 'Setup inicial e configuração',
            'categoria' => 'base',
            'horas'     => $horasBase
        ];

        // Funcionalidades
        foreach ($funcionalidades as $func) {
            $slug = $func['slug'] ?? '';
            $nome = $func['nome'] ?? ucfirst(str_replace('_', ' ', $slug));
            $horas = $this->getHorasFuncionalidade($slug);

            $itens[] = [
                'item'      => $nome,
                'categoria' => 'funcionalidade',
                'horas'     => $horas
            ];
        }

        // Integrações
        foreach ($integracoes as $integracao) {
            $nome = $integracao['nome'] ?? 'Integração';
            $tipo = $integracao['tipo'] ?? 'outros';

            $horasPorTipo = [
                'pagamento'    => 20,
                'comunicacao'  => 16,
                'dados'        => 24,
                'autenticacao' => 12,
                'outros'       => 18
            ];

            $itens[] = [
                'item'      => "Integração: {$nome}",
                'categoria' => 'integracao',
                'horas'     => $horasPorTipo[$tipo] ?? 18
            ];
        }

        return $itens;
    }

    /**
     * Formatar valor em reais
     */
    public function formatarValor(float $valor): string
    {
        return 'R$ ' . number_format($valor, 2, ',', '.');
    }

    /**
     * Formatar faixa de valores
     */
    public function formatarFaixaValor(float $min, float $max): string
    {
        return $this->formatarValor($min) . ' a ' . $this->formatarValor($max);
    }

    /**
     * Formatar prazo
     */
    public function formatarPrazo(int $min, int $max): string
    {
        if ($min === $max) {
            return "{$min} dias úteis";
        }
        return "{$min} a {$max} dias úteis";
    }

    /**
     * Obter texto de complexidade
     */
    public function getTextoComplexidade(string $complexidade): string
    {
        return match ($complexidade) {
            'baixa' => 'Baixa - Projeto simples com funcionalidades básicas',
            'alta'  => 'Alta - Projeto complexo com múltiplas integrações',
            default => 'Média - Projeto com funcionalidades intermediárias'
        };
    }

    /**
     * Atualizar configurações
     */
    public function setConfiguracoes(array $config): void
    {
        if (isset($config['valor_hora'])) {
            $this->valorHora = (float) $config['valor_hora'];
        }
        if (isset($config['fator_baixa'])) {
            $this->fatorBaixa = (float) $config['fator_baixa'];
        }
        if (isset($config['fator_media'])) {
            $this->fatorMedia = (float) $config['fator_media'];
        }
        if (isset($config['fator_alta'])) {
            $this->fatorAlta = (float) $config['fator_alta'];
        }
    }

    /**
     * Obter configurações atuais
     */
    public function getConfiguracoes(): array
    {
        return [
            'valor_hora'    => $this->valorHora,
            'fator_baixa'   => $this->fatorBaixa,
            'fator_media'   => $this->fatorMedia,
            'fator_alta'    => $this->fatorAlta,
            'margem_min'    => $this->margemMin,
            'margem_max'    => $this->margemMax,
            'horas_dia_min' => $this->horasDiaMin,
            'horas_dia_max' => $this->horasDiaMax
        ];
    }
}
