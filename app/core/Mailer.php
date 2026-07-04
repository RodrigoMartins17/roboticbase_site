<?php
class Mailer
{
    private static function smtpRead($socket): string
    {
        $data = '';
        while ($line = fgets($socket, 515)) {
            $data .= $line;
            if (isset($line[3]) && $line[3] === ' ') {
                break;
            }
        }
        return $data;
    }

    private static function smtpWrite($socket, string $command): void
    {
        fwrite($socket, $command . "\r\n");
    }

    private static function smtpExpect(string $response, array $codes): bool
    {
        foreach ($codes as $code) {
            if (str_starts_with($response, (string)$code)) {
                return true;
            }
        }
        return false;
    }

    public static function send(string $to, string $subject, string $html): bool
    {
        if (empty($to) || empty(SMTP_HOST) || empty(SMTP_USER) || empty(SMTP_PASS)) {
            return false;
        }

        $socket = @stream_socket_client('tcp://' . SMTP_HOST . ':' . SMTP_PORT, $errno, $errstr, 20);
        if (!$socket) {
            return false;
        }

        stream_set_timeout($socket, 20);

        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [220])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'EHLO localhost');
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [250])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'STARTTLS');
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [220])) {
            fclose($socket);
            return false;
        }

        if (!stream_socket_enable_crypto($socket, true, STREAM_CRYPTO_METHOD_TLS_CLIENT)) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'EHLO localhost');
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [250])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'AUTH LOGIN');
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [334])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, base64_encode(SMTP_USER));
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [334])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, base64_encode(SMTP_PASS));
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [235])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'MAIL FROM:<' . SMTP_FROM_EMAIL . '>');
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [250])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'RCPT TO:<' . $to . '>');
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [250, 251])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'DATA');
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [354])) {
            fclose($socket);
            return false;
        }

        $headers = [];
        $headers[] = 'From: ' . SMTP_FROM_NAME . ' <' . SMTP_FROM_EMAIL . '>';
        $headers[] = 'To: <' . $to . '>';
        $headers[] = 'Subject: =?UTF-8?B?' . base64_encode($subject) . '?=';
        $headers[] = 'MIME-Version: 1.0';
        $headers[] = 'Content-Type: text/html; charset=UTF-8';

        // Normalizo as quebras de linha para CRLF e faço "dot-stuffing" (linhas que
        // começam por "." têm de ficar ".." para não terminarem o DATA a meio).
        $html = preg_replace("/\r\n|\r|\n/", "\r\n", $html);
        $html = preg_replace("/\r\n\\./", "\r\n..", $html);

        $message = implode("\r\n", $headers) . "\r\n\r\n" . $html . "\r\n.";
        self::smtpWrite($socket, $message);
        $response = self::smtpRead($socket);
        if (!self::smtpExpect($response, [250])) {
            fclose($socket);
            return false;
        }

        self::smtpWrite($socket, 'QUIT');
        fclose($socket);
        return true;
    }

    // ---------------------------------------------------------------------
    //  Templates HTML (profissionais, ao estilo do ecrã de acompanhamento)
    // ---------------------------------------------------------------------

    // Moldura comum de todos os emails: cabeçalho + corpo + assinatura do clube.
    private static function layout(string $corpo): string
    {
        $ano = date('Y');
        return '<!DOCTYPE html>' . "\n"
            . '<html lang="pt"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"></head>' . "\n"
            . '<body style="margin:0;padding:0;background:#f1f3f4;font-family:Arial,Helvetica,sans-serif;color:#202124;">' . "\n"
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f1f3f4;padding:24px 12px;"><tr><td align="center">' . "\n"
            . '<table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:14px;overflow:hidden;border:1px solid #e8eaed;">' . "\n"
            . '<tr><td style="background:#0b1220;padding:22px 28px;">' . "\n"
            . '<span style="color:#ffffff;font-size:20px;font-weight:800;letter-spacing:.3px;">RoboticaXL</span>'
            . '<span style="color:#8ab4f8;font-size:14px;font-weight:400;"> &nbsp;·&nbsp; Clube de Robótica</span>' . "\n"
            . '</td></tr>' . "\n"
            . '<tr><td style="padding:28px;">' . "\n" . $corpo . "\n" . '</td></tr>' . "\n"
            . '<tr><td style="background:#f8f9fa;padding:20px 28px;border-top:1px solid #e8eaed;">' . "\n"
            . '<p style="margin:0;font-size:13px;color:#5f6368;line-height:1.6;">Com os melhores cumprimentos,<br>'
            . '<strong style="color:#202124;">Clube de Robótica RoboticaXL</strong><br>'
            . 'Escola Secundária Dr. Francisco Fernandes Lopes</p>' . "\n"
            . '<p style="margin:12px 0 0;font-size:11px;color:#9aa0a6;">Email automático — por favor não respondas a esta mensagem. &copy; ' . $ano . ' RoboticaXL.</p>' . "\n"
            . '</td></tr></table>' . "\n"
            . '</td></tr></table>' . "\n"
            . '</body></html>';
    }

    // Desenha a linha do tempo (etapas) com círculos coloridos por estado.
    // Cada etapa: ['label'=>, 'sub'=>, 'state'=>'done'|'current'|'pending'|'rejected', 'date'=>?]
    private static function timeline(array $passos): string
    {
        $n = count($passos);
        $out = '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:6px 0;">' . "\n";
        foreach ($passos as $i => $s) {
            $isLast = ($i === $n - 1);
            switch ($s['state']) {
                case 'done':     $bg = '#188038'; $icon = '&#10003;'; $tc = '#202124'; $sc = '#5f6368'; break;
                case 'current':  $bg = '#1a73e8'; $icon = '&bull;';   $tc = '#1a73e8'; $sc = '#3c4043'; break;
                case 'rejected': $bg = '#d93025'; $icon = '&#10005;'; $tc = '#d93025'; $sc = '#5f6368'; break;
                default:         $bg = '#dadce0'; $icon = '&nbsp;';   $tc = '#9aa0a6'; $sc = '#bdc1c6'; break;
            }
            $corLinha = ($s['state'] === 'done') ? '#188038' : '#e8eaed';
            $linha = $isLast ? '' : '<div style="width:2px;height:30px;background:' . $corLinha . ';margin:3px auto 0;line-height:1px;font-size:1px;">&nbsp;</div>';
            $badge = ($s['state'] === 'current')
                ? ' <span style="display:inline-block;background:#e8f0fe;color:#1a73e8;font-size:10px;font-weight:700;padding:2px 8px;border-radius:10px;">ATUALIZADO AGORA</span>'
                : '';
            $data = !empty($s['date']) ? '<div style="font-size:12px;color:#80868b;margin-top:3px;">&#128197; ' . htmlspecialchars($s['date']) . '</div>' : '';
            $out .= '<tr>'
                . '<td width="42" valign="top" align="center">'
                . '<table role="presentation" cellpadding="0" cellspacing="0" align="center"><tr>'
                . '<td width="28" height="28" align="center" valign="middle" style="width:28px;height:28px;background:' . $bg . ';border-radius:50%;color:#ffffff;font-size:14px;font-weight:700;text-align:center;line-height:28px;">' . $icon . '</td>'
                . '</tr></table>' . $linha
                . '</td>'
                . '<td valign="top" style="padding:0 0 16px 10px;">'
                . '<div style="font-size:15px;font-weight:700;color:' . $tc . ';">' . htmlspecialchars($s['label']) . $badge . '</div>'
                . '<div style="font-size:13px;color:' . $sc . ';margin-top:2px;line-height:1.4;">' . htmlspecialchars($s['sub']) . '</div>'
                . $data
                . '</td></tr>' . "\n";
        }
        return $out . '</table>';
    }

    // Monta as 4 etapas do fluxo com o estado certo consoante o estado atual.
    private static function passosEstado(string $tipo, string $estado): array
    {
        $ehSala = (stripos($tipo, 'sala') !== false);
        $L = [
            'pedido'    => 'Pedido registado',
            'aceite'    => 'Aceite pelo clube',
            'entregue'  => $ehSala ? 'Sala entregue (check-in)' : 'Entregue ao aluno',
            'devolvido' => $ehSala ? 'Sala devolvida ao clube' : 'Devolvido ao clube',
        ];
        $S = [
            'pedido'    => 'O pedido deu entrada no sistema.',
            'aceite'    => 'O clube aprovou o teu pedido.',
            'entregue'  => $ehSala ? 'A sala está reservada e em utilização.' : 'O material foi entregue ao aluno.',
            'devolvido' => $ehSala ? 'A sala foi devolvida ao clube.' : 'O material voltou ao clube. Requisição concluída.',
        ];
        $e = strtolower($estado);

        // Pedido recusado: fica pelo registo + nó vermelho de recusa.
        if (strpos($e, 'recus') !== false || strpos($e, 'rejeit') !== false) {
            return [
                ['label' => $L['pedido'], 'sub' => $S['pedido'], 'state' => 'done'],
                ['label' => 'Pedido recusado', 'sub' => 'Infelizmente o teu pedido não foi aprovado pelo clube.', 'state' => 'rejected'],
            ];
        }

        // Índice da etapa atual: 1 pedido, 2 aceite, 3 entregue/em uso, 4 devolvido.
        $idx = 1;
        if (strpos($e, 'devolv') !== false || strpos($e, 'conclu') !== false) $idx = 4;
        elseif (strpos($e, 'entreg') !== false || strpos($e, 'uso') !== false) $idx = 3;
        elseif (strpos($e, 'aceit') !== false) $idx = 2;

        $ordem = ['pedido', 'aceite', 'entregue', 'devolvido'];
        $passos = [];
        foreach ($ordem as $k => $chave) {
            $pos = $k + 1;
            $state = $pos < $idx ? 'done' : ($pos === $idx ? 'current' : 'pending');
            $passos[] = ['label' => $L[$chave], 'sub' => $S[$chave], 'state' => $state];
        }
        return $passos;
    }

    public static function sendValidationEmail(string $to, string $name, string $token): bool
    {
        $url = BASE_URL . 'auth/verifyEmail?token=' . urlencode($token);
        $corpo =
            '<p style="margin:0 0 4px;font-size:16px;">Olá <strong>' . htmlspecialchars($name) . '</strong>,</p>' . "\n"
            . '<p style="margin:0 0 20px;font-size:14px;color:#3c4043;line-height:1.5;">Bem-vindo ao Clube de Robótica RoboticaXL! Para ativares a tua conta, confirma o teu email carregando no botão abaixo:</p>' . "\n"
            . '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;"><tr>'
            . '<td style="border-radius:8px;background:#1a73e8;"><a href="' . htmlspecialchars($url) . '" style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;">Validar a minha conta</a></td>'
            . '</tr></table>' . "\n"
            . '<p style="margin:0;font-size:12px;color:#80868b;line-height:1.5;">Se o botão não funcionar, copia e cola este link no browser:<br>'
            . '<span style="color:#1a73e8;word-break:break-all;">' . htmlspecialchars($url) . '</span></p>' . "\n"
            . '<p style="margin:16px 0 0;font-size:12px;color:#9aa0a6;">Se não foste tu a criar esta conta, ignora este email.</p>';
        return self::send($to, 'Validar conta — Clube de Robótica RoboticaXL', self::layout($corpo));
    }

    // Email com o link para definir uma nova palavra-passe.
    public static function sendPasswordReset(string $to, string $name, string $token): bool
    {
        $url = BASE_URL . 'auth/resetPassword?token=' . urlencode($token);
        $corpo =
            '<p style="margin:0 0 4px;font-size:16px;">Olá <strong>' . htmlspecialchars($name) . '</strong>,</p>' . "\n"
            . '<p style="margin:0 0 20px;font-size:14px;color:#3c4043;line-height:1.5;">Recebemos um pedido para alterar a tua palavra-passe. Carrega no botão abaixo para definires uma nova. Este link é válido durante <strong>1 hora</strong>.</p>' . "\n"
            . '<table role="presentation" cellpadding="0" cellspacing="0" style="margin:0 0 20px;"><tr>'
            . '<td style="border-radius:8px;background:#1a73e8;"><a href="' . htmlspecialchars($url) . '" style="display:inline-block;padding:13px 28px;font-size:15px;font-weight:700;color:#ffffff;text-decoration:none;">Definir nova palavra-passe</a></td>'
            . '</tr></table>' . "\n"
            . '<p style="margin:0;font-size:12px;color:#80868b;line-height:1.5;">Se o botão não funcionar, copia e cola este link no browser:<br>'
            . '<span style="color:#1a73e8;word-break:break-all;">' . htmlspecialchars($url) . '</span></p>' . "\n"
            . '<p style="margin:16px 0 0;font-size:12px;color:#9aa0a6;">Se não foste tu a pedir isto, ignora este email — a tua palavra-passe continua a mesma.</p>';
        return self::send($to, 'Alterar palavra-passe — Clube de Robótica RoboticaXL', self::layout($corpo));
    }

    public static function sendStatusUpdate(string $to, string $name, string $tipo, int $id, string $estado): bool
    {
        $tipoLbl = (stripos($tipo, 'sala') !== false) ? 'sala' : 'material';
        $passos = self::passosEstado($tipo, $estado);
        $corpo =
            '<p style="margin:0 0 4px;font-size:16px;">Olá <strong>' . htmlspecialchars($name) . '</strong>,</p>' . "\n"
            . '<p style="margin:0 0 18px;font-size:14px;color:#3c4043;line-height:1.5;">A tua requisição de ' . $tipoLbl . ' <strong>#' . $id . '</strong> foi atualizada. Aqui está o estado atual do teu pedido:</p>' . "\n"
            . self::timeline($passos) . "\n"
            . '<p style="margin:18px 0 0;font-size:13px;color:#5f6368;line-height:1.5;">Podes acompanhar tudo em tempo real na tua área <em>As minhas requisições</em> no site do clube.</p>';
        return self::send($to, 'Requisição #' . $id . ' atualizada — ' . $estado, self::layout($corpo));
    }

    public static function sendWarning12h(string $to, string $name, string $tipo, int $id, string $acao, string $data, string $item): bool
    {
        $ehSala = (stripos($tipo, 'sala') !== false);
        $buscar = ($acao === 'LEVANTAMENTO');
        $accLabel = $buscar
            ? ($ehSala ? 'iniciar a reserva da sala' : 'ires buscar o material')
            : ($ehSala ? 'entregares a sala ao clube' : 'devolveres o material');
        $quando = date('d/m/Y \à\s H:i', strtotime($data));

        $passos = $buscar
            ? [
                ['label' => 'Levantamento', 'sub' => 'Está quase na hora de ' . $accLabel . '.', 'state' => 'current', 'date' => $quando],
                ['label' => 'Devolução', 'sub' => 'Depois não te esqueças de devolver ao clube.', 'state' => 'pending'],
            ]
            : [
                ['label' => 'Levantamento', 'sub' => 'Já foi levantado.', 'state' => 'done'],
                ['label' => 'Devolução', 'sub' => 'Está quase na hora de ' . $accLabel . '.', 'state' => 'current', 'date' => $quando],
            ];

        $corpo =
            '<p style="margin:0 0 4px;font-size:16px;">Olá <strong>' . htmlspecialchars($name) . '</strong>,</p>' . "\n"
            . '<p style="margin:0 0 14px;font-size:14px;color:#3c4043;line-height:1.5;">Lembrete: faltam cerca de <strong>12 horas</strong> para ' . $accLabel . '.</p>' . "\n"
            . '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:0 0 16px;background:#fef7e0;border:1px solid #fde293;border-radius:10px;"><tr>'
            . '<td style="padding:12px 16px;font-size:14px;color:#7c5c00;line-height:1.5;"><strong>' . htmlspecialchars($item) . '</strong> &nbsp;·&nbsp; Requisição #' . $id . '<br>Data marcada: <strong>' . $quando . '</strong></td>'
            . '</tr></table>' . "\n"
            . self::timeline($passos) . "\n"
            . '<p style="margin:16px 0 0;font-size:13px;color:#5f6368;">Por favor, sê pontual. Obrigado!</p>';
        return self::send($to, 'Lembrete: faltam ~12h — requisição #' . $id, self::layout($corpo));
    }
}
