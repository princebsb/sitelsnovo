<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\OrcamentoModel;
use App\Services\IaService;
use App\Services\EstimativaService;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Orcamento Controller
 *
 * Controlador para o sistema de simulação de pré-orçamento com IA
 *
 * @package App\Controllers
 */
class Orcamento extends BaseController
{
    protected OrcamentoModel $orcamentoModel;
    protected IaService $iaService;
    protected EstimativaService $estimativaService;

    public function __construct()
    {
        $this->orcamentoModel    = new OrcamentoModel();
        $this->iaService         = new IaService();
        $this->estimativaService = new EstimativaService();
    }

    /**
     * Exibir formulário de simulação
     */
    public function index(): string
    {
        $data = [
            'title'       => 'Simular Orçamento | LS Global Tecnologia',
            'description' => 'Simule gratuitamente o orçamento do seu projeto de software',
            'tipos_projeto' => $this->getTiposProjeto()
        ];

        return view('orcamento/orcamento_form', $data);
    }

    /**
     * Processar formulário e gerar orçamento
     */
    public function processar(): ResponseInterface
    {
        // Validar requisição AJAX
        if (!$this->request->isAJAX()) {
            return $this->response->setStatusCode(400)->setJSON([
                'success' => false,
                'message' => 'Requisição inválida'
            ]);
        }

        // Obter dados do formulário
        $dados = [
            'nome'              => $this->request->getPost('nome'),
            'email'             => $this->request->getPost('email'),
            'whatsapp'          => $this->request->getPost('whatsapp'),
            'tipo_projeto'      => $this->request->getPost('tipo_projeto'),
            'descricao_original'=> $this->request->getPost('descricao')
        ];

        // Validar dados
        if (!$this->orcamentoModel->validate($dados)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Dados inválidos',
                'errors'  => $this->orcamentoModel->errors()
            ]);
        }

        // Verificar limite de requisições (anti-spam)
        if ($this->orcamentoModel->verificarLimiteRequisicoes($dados['email'], 5)) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Você já solicitou um orçamento recentemente. Aguarde 5 minutos.'
            ]);
        }

        try {
            // 1. Analisar projeto com IA
            $analiseIA = $this->iaService->analisarProjeto(
                $dados['descricao_original'],
                $dados['tipo_projeto']
            );

            // 2. Calcular estimativas
            $estimativa = $this->estimativaService->calcular($analiseIA);

            // 3. Preparar dados para salvar
            $dadosCompletos = array_merge($dados, [
                'descricao_melhorada' => $analiseIA['resumo_melhorado'] ?? '',
                'funcionalidades'     => $analiseIA['funcionalidades'] ?? [],
                'integracoes'         => $analiseIA['integracoes'] ?? [],
                'sugestoes'           => $analiseIA['sugestoes'] ?? [],
                'complexidade'        => $estimativa['complexidade'],
                'horas_estimadas'     => $estimativa['horas_estimadas'],
                'valor_min'           => $estimativa['valor_min'],
                'valor_max'           => $estimativa['valor_max'],
                'prazo_min'           => $estimativa['prazo_min'],
                'prazo_max'           => $estimativa['prazo_max']
            ]);

            // 4. Salvar no banco
            $leadId = $this->orcamentoModel->salvarOrcamentoCompleto($dadosCompletos);

            if (!$leadId) {
                throw new \Exception('Erro ao salvar orçamento');
            }

            // 5. Preparar resposta
            $resultado = [
                'id'                => $leadId,
                'nome'              => $dados['nome'],
                'tipo_projeto'      => $this->getTipoProjetoTexto($dados['tipo_projeto']),
                'descricao_melhorada' => $analiseIA['resumo_melhorado'],
                'funcionalidades'   => $analiseIA['funcionalidades'],
                'integracoes'       => $analiseIA['integracoes'],
                'sugestoes'         => $analiseIA['sugestoes'],
                'riscos'            => $analiseIA['riscos'] ?? [],
                'tecnologias'       => $analiseIA['tecnologias_recomendadas'] ?? [],
                'complexidade'      => $estimativa['complexidade'],
                'complexidade_texto'=> $this->estimativaService->getTextoComplexidade($estimativa['complexidade']),
                'horas_estimadas'   => $estimativa['horas_estimadas'],
                'prazo_texto'       => $this->estimativaService->formatarPrazo($estimativa['prazo_min'], $estimativa['prazo_max']),
                'valor_texto'       => $this->estimativaService->formatarFaixaValor($estimativa['valor_min'], $estimativa['valor_max']),
                'valor_min'         => $estimativa['valor_min'],
                'valor_max'         => $estimativa['valor_max'],
                'prazo_min'         => $estimativa['prazo_min'],
                'prazo_max'         => $estimativa['prazo_max'],
                'detalhamento'      => $estimativa['detalhamento']
            ];

            // 6. Enviar email de notificação (assíncrono)
            $this->enviarNotificacaoEmail($dadosCompletos, $resultado);

            return $this->response->setJSON([
                'success'   => true,
                'message'   => 'Orçamento gerado com sucesso!',
                'resultado' => $resultado,
                'redirect'  => site_url("orcamento/resultado/{$leadId}")
            ]);

        } catch (\Exception $e) {
            log_message('error', 'Erro ao processar orçamento: ' . $e->getMessage());

            return $this->response->setJSON([
                'success' => false,
                'message' => 'Ocorreu um erro ao processar seu orçamento. Tente novamente.'
            ]);
        }
    }

    /**
     * Exibir resultado do orçamento
     */
    public function resultado(int $id): string
    {
        $orcamento = $this->orcamentoModel->buscarOrcamento($id);

        if (!$orcamento) {
            throw \CodeIgniter\Exceptions\PageNotFoundException::forPageNotFound('Orçamento não encontrado');
        }

        $data = [
            'title'      => 'Seu Pré-Orçamento | LS Global Tecnologia',
            'orcamento'  => $orcamento,
            'tipo_texto' => $this->getTipoProjetoTexto($orcamento['tipo_projeto']),
            'valor_texto'=> $this->estimativaService->formatarFaixaValor(
                $orcamento['valor_min'],
                $orcamento['valor_max']
            ),
            'prazo_texto'=> $this->estimativaService->formatarPrazo(
                $orcamento['prazo_min'],
                $orcamento['prazo_max']
            ),
            'complexidade_texto' => $this->estimativaService->getTextoComplexidade($orcamento['complexidade'])
        ];

        return view('orcamento/orcamento_resultado', $data);
    }

    /**
     * Solicitar proposta detalhada
     */
    public function solicitarProposta(): ResponseInterface
    {
        $id = $this->request->getPost('orcamento_id');
        $mensagem = $this->request->getPost('mensagem');

        $orcamento = $this->orcamentoModel->buscarOrcamento($id);

        if (!$orcamento) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Orçamento não encontrado'
            ]);
        }

        // Atualizar status
        $this->orcamentoModel->atualizarStatus($id, 'analisado');

        // Enviar notificação para equipe
        $this->enviarNotificacaoProposta($orcamento, $mensagem);

        return $this->response->setJSON([
            'success' => true,
            'message' => 'Solicitação enviada! Nossa equipe entrará em contato em breve.'
        ]);
    }

    /**
     * API: Verificar status do processamento
     */
    public function status(int $id): ResponseInterface
    {
        $orcamento = $this->orcamentoModel->find($id);

        if (!$orcamento) {
            return $this->response->setStatusCode(404)->setJSON([
                'success' => false,
                'message' => 'Orçamento não encontrado'
            ]);
        }

        return $this->response->setJSON([
            'success' => true,
            'status'  => $orcamento['status'],
            'created' => $orcamento['created_at']
        ]);
    }

    /**
     * Obter tipos de projeto disponíveis
     */
    protected function getTiposProjeto(): array
    {
        return [
            'site'        => 'Site Institucional / Landing Page',
            'sistema_web' => 'Sistema Web / Plataforma Online',
            'aplicativo'  => 'Aplicativo Mobile (iOS/Android)',
            'automacao'   => 'Automação de Processos',
            'integracao'  => 'Integração de Sistemas / APIs',
            'outro'       => 'Outro'
        ];
    }

    /**
     * Obter texto do tipo de projeto
     */
    protected function getTipoProjetoTexto(string $tipo): string
    {
        $tipos = $this->getTiposProjeto();
        return $tipos[$tipo] ?? 'Projeto';
    }

    /**
     * Enviar notificação por email
     */
    protected function enviarNotificacaoEmail(array $dados, array $resultado): void
    {
        try {
            $email = service('email');

            // Email para a empresa
            $email->setFrom(env('email.fromEmail', 'sistema@lsglobaltecnologia.com.br'), 'LS Global - Sistema');
            $email->setTo(env('email.notificacao', 'contato@lsglobaltecnologia.com.br'));
            $email->setSubject('Novo Lead - Simulação de Orçamento');

            $mensagem = view('emails/novo_lead', [
                'dados'     => $dados,
                'resultado' => $resultado
            ]);

            $email->setMessage($mensagem);
            $email->send();

            // Email para o cliente
            $email->clear();
            $email->setTo($dados['email']);
            $email->setSubject('Seu Pré-Orçamento - LS Global Tecnologia');

            $mensagemCliente = view('emails/orcamento_cliente', [
                'dados'     => $dados,
                'resultado' => $resultado
            ]);

            $email->setMessage($mensagemCliente);
            $email->send();

        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar email: ' . $e->getMessage());
        }
    }

    /**
     * Enviar notificação de solicitação de proposta
     */
    protected function enviarNotificacaoProposta(array $orcamento, string $mensagem): void
    {
        try {
            $email = service('email');

            $email->setFrom(env('email.fromEmail', 'sistema@lsglobaltecnologia.com.br'), 'LS Global - Sistema');
            $email->setTo(env('email.notificacao', 'contato@lsglobaltecnologia.com.br'));
            $email->setSubject('Solicitação de Proposta Detalhada - Lead #' . $orcamento['id']);

            $corpo = "
            <h2>Solicitação de Proposta Detalhada</h2>
            <p><strong>Lead:</strong> {$orcamento['nome']}</p>
            <p><strong>Email:</strong> {$orcamento['email']}</p>
            <p><strong>WhatsApp:</strong> {$orcamento['whatsapp']}</p>
            <p><strong>Mensagem adicional:</strong></p>
            <p>{$mensagem}</p>
            <hr>
            <p><a href='" . site_url("admin/orcamentos/{$orcamento['id']}") . "'>Ver detalhes do orçamento</a></p>
            ";

            $email->setMessage($corpo);
            $email->send();

        } catch (\Exception $e) {
            log_message('error', 'Erro ao enviar notificação de proposta: ' . $e->getMessage());
        }
    }
}
