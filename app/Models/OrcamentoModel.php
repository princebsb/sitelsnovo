<?php

namespace App\Models;

use CodeIgniter\Model;

/**
 * OrcamentoModel
 *
 * Model para gerenciar leads e orçamentos do sistema de simulação
 *
 * @package App\Models
 */
class OrcamentoModel extends Model
{
    protected $table            = 'leads_orcamento';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;

    protected $allowedFields = [
        'nome',
        'email',
        'whatsapp',
        'tipo_projeto',
        'descricao_original',
        'descricao_melhorada',
        'funcionalidades',
        'integracoes',
        'sugestoes',
        'complexidade',
        'horas_estimadas',
        'valor_min',
        'valor_max',
        'prazo_min',
        'prazo_max',
        'status',
        'ip_address',
        'user_agent'
    ];

    // Dates
    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    // Validation
    protected $validationRules = [
        'nome'              => 'required|min_length[3]|max_length[150]',
        'email'             => 'required|valid_email|max_length[150]',
        'whatsapp'          => 'required|min_length[10]|max_length[30]',
        'tipo_projeto'      => 'required|in_list[site,sistema_web,aplicativo,automacao,integracao,outro]',
        'descricao_original'=> 'required|min_length[20]'
    ];

    protected $validationMessages = [
        'nome' => [
            'required'   => 'O nome é obrigatório.',
            'min_length' => 'O nome deve ter pelo menos 3 caracteres.'
        ],
        'email' => [
            'required'    => 'O email é obrigatório.',
            'valid_email' => 'Informe um email válido.'
        ],
        'whatsapp' => [
            'required'   => 'O WhatsApp é obrigatório.',
            'min_length' => 'Informe um número de WhatsApp válido.'
        ],
        'tipo_projeto' => [
            'required' => 'Selecione o tipo de projeto.',
            'in_list'  => 'Tipo de projeto inválido.'
        ],
        'descricao_original' => [
            'required'   => 'A descrição do projeto é obrigatória.',
            'min_length' => 'Descreva seu projeto com pelo menos 20 caracteres.'
        ]
    ];

    protected $skipValidation = false;

    /**
     * Salvar orçamento completo com análise da IA
     */
    public function salvarOrcamentoCompleto(array $dados): int|false
    {
        // Preparar dados JSON
        if (isset($dados['funcionalidades']) && is_array($dados['funcionalidades'])) {
            $dados['funcionalidades'] = json_encode($dados['funcionalidades'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($dados['integracoes']) && is_array($dados['integracoes'])) {
            $dados['integracoes'] = json_encode($dados['integracoes'], JSON_UNESCAPED_UNICODE);
        }

        if (isset($dados['sugestoes']) && is_array($dados['sugestoes'])) {
            $dados['sugestoes'] = json_encode($dados['sugestoes'], JSON_UNESCAPED_UNICODE);
        }

        // Adicionar IP e User Agent
        $request = service('request');
        $dados['ip_address'] = $request->getIPAddress();
        $dados['user_agent'] = $request->getUserAgent()->getAgentString();

        // Inserir e retornar ID
        if ($this->insert($dados)) {
            return $this->getInsertID();
        }

        return false;
    }

    /**
     * Buscar orçamento por ID com dados decodificados
     */
    public function buscarOrcamento(int $id): ?array
    {
        $orcamento = $this->find($id);

        if ($orcamento) {
            // Decodificar campos JSON
            $orcamento['funcionalidades'] = json_decode($orcamento['funcionalidades'] ?? '[]', true) ?: [];
            $orcamento['integracoes'] = json_decode($orcamento['integracoes'] ?? '[]', true) ?: [];
            $orcamento['sugestoes'] = json_decode($orcamento['sugestoes'] ?? '[]', true) ?: [];
        }

        return $orcamento;
    }

    /**
     * Listar orçamentos recentes
     */
    public function listarRecentes(int $limite = 10): array
    {
        return $this->orderBy('created_at', 'DESC')
                    ->limit($limite)
                    ->findAll();
    }

    /**
     * Buscar por email
     */
    public function buscarPorEmail(string $email): array
    {
        return $this->where('email', $email)
                    ->orderBy('created_at', 'DESC')
                    ->findAll();
    }

    /**
     * Atualizar status do lead
     */
    public function atualizarStatus(int $id, string $status): bool
    {
        return $this->update($id, ['status' => $status]);
    }

    /**
     * Estatísticas gerais
     */
    public function getEstatisticas(): array
    {
        $db = $this->db;

        return [
            'total'       => $this->countAll(),
            'novos'       => $this->where('status', 'novo')->countAllResults(),
            'convertidos' => $this->where('status', 'convertido')->countAllResults(),
            'valor_medio' => $db->query("SELECT AVG((valor_min + valor_max) / 2) as media FROM {$this->table}")->getRow()->media ?? 0,
            'por_tipo'    => $db->query("SELECT tipo_projeto, COUNT(*) as total FROM {$this->table} GROUP BY tipo_projeto")->getResultArray()
        ];
    }

    /**
     * Verificar se email já solicitou orçamento recentemente (anti-spam)
     */
    public function verificarLimiteRequisicoes(string $email, int $minutos = 5): bool
    {
        $tempo = date('Y-m-d H:i:s', strtotime("-{$minutos} minutes"));

        $count = $this->where('email', $email)
                      ->where('created_at >', $tempo)
                      ->countAllResults();

        return $count > 0;
    }
}
