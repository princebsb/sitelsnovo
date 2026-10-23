<?php
/**
 * Recebe o formulário de contato do site e envia por SMTP autenticado.
 * Responde sempre em JSON: {"ok": true} ou {"ok": false, "erro": "..."}.
 */

declare(strict_types=1);

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');

function responder(bool $ok, string $mensagem = '', int $status = 200): void
{
    http_response_code($status);
    echo json_encode($ok ? ['ok' => true] : ['ok' => false, 'erro' => $mensagem],
        JSON_UNESCAPED_UNICODE);
    exit;
}

if (($_SERVER['REQUEST_METHOD'] ?? '') !== 'POST') {
    responder(false, 'Método não permitido.', 405);
}

$config = require __DIR__ . '/config-email.php';

// ---------------------------------------------------------------- entrada
$campo = static function (string $nome, int $limite): string {
    $valor = trim((string) ($_POST[$nome] ?? ''));
    $valor = str_replace(["\r", "\0"], '', $valor);
    return mb_substr($valor, 0, $limite);
};

$honeypot = trim((string) ($_POST['website'] ?? ''));   // isca para robôs
if ($honeypot !== '') {
    responder(true);                                     // finge sucesso
}

$nome     = $campo('name', 120);
$email    = $campo('email', 160);
$telefone = $campo('phone', 40);
$servico  = $campo('service', 80);
$mensagem = $campo('message', 5000);

if ($nome === '' || $email === '' || $mensagem === '') {
    responder(false, 'Preencha nome, e-mail e mensagem.', 422);
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    responder(false, 'Informe um e-mail válido.', 422);
}

if (mb_strlen($mensagem) < 10) {
    responder(false, 'Escreva um pouco mais sobre o que você precisa.', 422);
}

// ---------------------------------------------------------- limite por IP
$ip = (string) ($_SERVER['REMOTE_ADDR'] ?? '0.0.0.0');
$controle = sys_get_temp_dir() . '/lsglobal-contato-' . md5($ip) . '.txt';
$agora = time();
$envios = [];

if (is_readable($controle)) {
    $envios = array_filter(
        array_map('intval', explode(',', (string) file_get_contents($controle))),
        static fn (int $t): bool => $t > $agora - 3600
    );
}

if (count($envios) >= 5) {
    responder(false, 'Muitas mensagens enviadas. Tente novamente mais tarde ou fale pelo WhatsApp.', 429);
}

// ------------------------------------------------------------- montagem
$servicos = [
    'web'                => 'Desenvolvimento de Sistemas Web',
    'mobile'             => 'Desenvolvimento de Aplicativos',
    'sistema'            => 'Sistemas Sob Medida',
    'integracao'         => 'Integração de APIs',
    'automacao'          => 'Automação de Processos',
    'ia'                 => 'Inteligência Artificial e Agentes',
    'sustentacao'        => 'Sustentação e Evolução de Sistemas',
    'saude-digital'      => 'Saúde Digital e Sistemas Públicos',
    'consultoria'        => 'Consultoria em Tecnologia',
    'gestao-ti'          => 'Gestão de TI e Infraestrutura',
    'automacao-predial'  => 'Gestão de Sistemas Prediais',
];
$servicoNome = $servicos[$servico] ?? ($servico !== '' ? $servico : 'Não informado');

$corpo = "Nova mensagem pelo site\n"
    . str_repeat('-', 40) . "\n\n"
    . "Nome:     {$nome}\n"
    . "E-mail:   {$email}\n"
    . "Telefone: " . ($telefone !== '' ? $telefone : 'Não informado') . "\n"
    . "Serviço:  {$servicoNome}\n\n"
    . "Mensagem:\n{$mensagem}\n\n"
    . str_repeat('-', 40) . "\n"
    . 'Enviado em ' . date('d/m/Y H:i:s') . " (IP {$ip})\n";

$assunto = "Contato pelo site - {$nome} ({$servicoNome})";

// ---------------------------------------------------------------- envio
$erro = '';
$enviado = enviarSmtp($config, $assunto, $corpo, $email, $nome, $erro);

if (!$enviado) {
    error_log('[contato] falha no envio: ' . $erro);
    responder(false, 'Não foi possível enviar agora. Fale conosco pelo WhatsApp ou pelo e-mail contato@lsglobaltecnologia.com.br.', 502);
}

$envios[] = $agora;
@file_put_contents($controle, implode(',', $envios));

responder(true);

// =====================================================================
//  Cliente SMTP mínimo (sem dependências externas)
// =====================================================================
function enviarSmtp(array $cfg, string $assunto, string $corpo, string $respondePara,
                    string $nomeRemetenteVisitante, string &$erro): bool
{
    $prefixo = $cfg['seguranca'] === 'ssl' ? 'ssl://' : 'tcp://';
    // Em produção o certificado é validado. Só desative para testes locais em
    // que o PHP não tem o pacote de certificados (CA bundle) configurado.
    $verificar = $cfg['verificar_certificado'] ?? true;

    $contexto = stream_context_create([
        'ssl' => [
            'peer_name'         => $cfg['host'],
            'SNI_enabled'       => true,
            'verify_peer'       => $verificar,
            'verify_peer_name'  => $verificar,
            'allow_self_signed' => !$verificar,
        ],
    ]);

    $socket = @stream_socket_client(
        $prefixo . $cfg['host'] . ':' . (int) $cfg['port'],
        $errno, $errstr, 20, STREAM_CLIENT_CONNECT, $contexto
    );

    if (!$socket) {
        $erro = "conexão falhou ({$errno}) {$errstr}";
        return false;
    }

    stream_set_timeout($socket, 20);

    $ler = static function ($socket) use (&$erro): string {
        $resposta = '';
        while (($linha = fgets($socket, 515)) !== false) {
            $resposta .= $linha;
            if (strlen($linha) < 4 || $linha[3] !== '-') {
                break;
            }
        }
        return $resposta;
    };

    $comando = static function ($socket, string $cmd, string $esperado) use ($ler, &$erro): bool {
        if ($cmd !== '') {
            fwrite($socket, $cmd . "\r\n");
        }
        $resposta = $ler($socket);
        if (strncmp($resposta, $esperado, strlen($esperado)) !== 0) {
            $erro = trim(($cmd !== '' ? substr($cmd, 0, 12) . ' -> ' : '') . $resposta);
            return false;
        }
        return true;
    };

    $dominio = $_SERVER['SERVER_NAME'] ?? 'lsglobaltecnologia.com.br';
    $ok = $comando($socket, '', '220')
        && $comando($socket, 'EHLO ' . $dominio, '250');

    if ($ok && $cfg['seguranca'] === 'tls') {
        $ok = $comando($socket, 'STARTTLS', '220');

        if ($ok) {
            $metodos = STREAM_CRYPTO_METHOD_TLSv1_2_CLIENT;
            if (defined('STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT')) {
                $metodos |= STREAM_CRYPTO_METHOD_TLSv1_3_CLIENT;
            }

            if (!@stream_socket_enable_crypto($socket, true, $metodos)) {
                $erro = 'falha ao iniciar TLS (STARTTLS)';
                @fclose($socket);
                return false;
            }

            $ok = $comando($socket, 'EHLO ' . $dominio, '250');
        }
    }

    $ok = $ok
        && $comando($socket, 'AUTH LOGIN', '334')
        && $comando($socket, base64_encode((string) $cfg['usuario']), '334')
        && $comando($socket, base64_encode((string) $cfg['senha']), '235')
        && $comando($socket, 'MAIL FROM:<' . $cfg['remetente'] . '>', '250')
        && $comando($socket, 'RCPT TO:<' . $cfg['destinatario'] . '>', '250');

    if ($ok && !empty($cfg['copia'])) {
        $ok = $comando($socket, 'RCPT TO:<' . $cfg['copia'] . '>', '250');
    }

    if ($ok) {
        $ok = $comando($socket, 'DATA', '354');
    }

    if ($ok) {
        $codificar = static fn (string $t): string => '=?UTF-8?B?' . base64_encode($t) . '?=';

        $cabecalhos = [
            'Date: ' . date('r'),
            'From: ' . $codificar((string) $cfg['nome_remetente']) . ' <' . $cfg['remetente'] . '>',
            'To: <' . $cfg['destinatario'] . '>',
            'Reply-To: ' . $codificar($nomeRemetenteVisitante) . ' <' . $respondePara . '>',
            'Subject: ' . $codificar($assunto),
            'Message-ID: <' . bin2hex(random_bytes(12)) . '@lsglobaltecnologia.com.br>',
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: base64',
        ];

        if (!empty($cfg['copia'])) {
            $cabecalhos[] = 'Cc: <' . $cfg['copia'] . '>';
        }

        $mensagem = implode("\r\n", $cabecalhos) . "\r\n\r\n"
            . chunk_split(base64_encode($corpo), 76, "\r\n");

        fwrite($socket, $mensagem . "\r\n.\r\n");
        $ok = $comando($socket, '', '250');
    }

    @fwrite($socket, "QUIT\r\n");
    @fclose($socket);

    return $ok;
}
