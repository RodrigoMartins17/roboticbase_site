<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../models/RequisicaoSala.php';
require_once __DIR__ . '/../models/Sala.php';

// Este controller é parecido com o das requisições de material, mas para SALAS.
// Trata das reservas de salas: pedir, o clube aceitar/rejeitar, dar acesso à sala
// (check-in) e no fim fechar a reserva (check-out).
class RequisicaoSalaController extends Controller
{
    // Página com as reservas de sala DESTE utilizador.
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $reqModel = new RequisicaoSala();
        $all = $reqModel->porUtilizador($user['id']);

        // Separo em pendentes (à espera) e histórico, como fiz nos materiais.
        $pendentes = [];
        $historico = [];
        foreach ($all as $req) {
            if ($req['estado_sala'] === 'PENDENTE') {
                $pendentes[] = $req;
            } else {
                $historico[] = $req;
            }
        }

        $this->view('requisicoes_sala/index', [
            'pendentes' => $pendentes,
            'historico' => $historico,
            'pageTitle' => 'Requisições de sala'
        ]);
    }

    // Criar uma reserva de sala nova.
    public function criar()
    {
        Auth::requireLogin();
        // As salas só podem ser reservadas por professores/admin, não por alunos.
        if (Auth::isAluno()) {
            http_response_code(403);
            echo 'Sem permissao. Apenas professores e administradores podem requisitar salas.';
            return;
        }

        $salaModel = new Sala();
        $reqModel = new RequisicaoSala();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $user = Auth::user();
            $salaId = (int) ($_POST['sala_id'] ?? 0);
            $dataInicio = $_POST['data_inicio'] ?? '';
            $dataFim = $_POST['data_fim'] ?? '';
            $observacao = $this->normalizeText($_POST['observacao'] ?? '', 2000);

            // Confirmo que escolheu uma sala e que as datas são válidas.
            if ($salaId <= 0 || !$this->isValidDateTime($dataInicio) || !$this->isValidDateTime($dataFim)) {
                $this->setFlash('error', 'Dados invalidos na reserva de sala.');
                $this->redirect('requisicaoSala/criar');
            }

            $dataInicioSql = date('Y-m-d H:i:s', strtotime($dataInicio));
            $dataFimSql = date('Y-m-d H:i:s', strtotime($dataFim));

            // O fim tem de ser depois do início.
            if (strtotime($dataFimSql) <= strtotime($dataInicioSql)) {
                $this->setFlash('error', 'A data/hora de fim deve ser posterior ao inicio.');
                $this->redirect('requisicaoSala/criar');
            }

            // A sala tem de estar DISPONÍVEL (não pode estar indisponível/manutenção).
            require_once __DIR__ . '/../models/Sala.php';
            $salaEscolhida = (new Sala())->find($salaId);
            if (!$salaEscolhida || ($salaEscolhida['estado'] ?? '') !== 'DISPONIVEL') {
                $this->setFlash('error', 'Esta sala não está disponível para requisição.');
                $this->redirect('requisicaoSala/criar');
            }

            // Verifico se a sala já está reservada nessas horas (para não haver choques).
            if ($reqModel->verificarConflito($salaId, $dataInicioSql, $dataFimSql)) {
                $this->setFlash('error', 'A sala ja esta reservada neste periodo.');
                $this->redirect('requisicaoSala/criar?sala_id=' . $salaId);
            }

            // Tudo bem: crio a reserva (começa em PENDENTE).
            try {
                $novoId = $reqModel->criar((int) $user['id'], $salaId, $dataInicioSql, $dataFimSql, $observacao ?: null);
                $this->setFlash('success', 'Pedido de sala registado. Estado: PENDENTE.');
                // Email de confirmação a dizer que o pedido deu entrada no sistema.
                if ($novoId && !empty($user['email'])) {
                    try { Mailer::sendStatusUpdate($user['email'], $user['nome'] ?? 'Utilizador', 'SALA', (int)$novoId, 'PENDENTE'); } catch (\Throwable $e) {}
                }
            } catch (PDOException $e) {
                $this->setFlash('error', $this->dbErrorMessage($e));
            }

            $this->redirect('requisicaoSala/index');
        }

        // Se não foi POST, mostro o formulário com a lista de salas.
        // Só mostro salas DISPONÍVEIS — as indisponíveis não podem ser requisitadas.
        $salas = $salaModel->disponiveis();
        $salaSelecionada = (int) ($_GET['sala_id'] ?? 0);
        $this->view('requisicoes_sala/form', [
            'salas' => $salas,
            'salaSelecionada' => $salaSelecionada,
            'pageTitle' => 'Reservar sala'
        ]);
    }

    // O clube decide sobre a reserva: aceitar, rejeitar, dar acesso ou finalizar.
    public function decidir()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isResponsavel() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $acao = $_POST['acao'] ?? '';
            // As quatro ações possíveis para uma reserva de sala.
            if ($id <= 0 || !$this->isValidEnumValue($acao, ['aceitar', 'rejeitar', 'entregar', 'finalizar'])) {
                $this->setFlash('error', 'Dados invalidos para atualizar a requisicao.');
                $this->redirect('requisicaoSala/index');
            }

            $reqModel = new RequisicaoSala();

            try {
                if ($acao === 'aceitar') {
                    // Aceitar a reserva e avisar por email.
                    $reqModel->atualizarEstado($id, 'ACEITE');
                    $req = $reqModel->find($id);
                    if ($req) {
                        Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'SALA', $id, 'ACEITE');
                    }
                    $this->setFlash('success', 'Pedido de sala aceite.');
                } elseif ($acao === 'rejeitar') {
                    // Rejeitar a reserva.
                    $reqModel->rejeitar($id);
                    $req = $reqModel->find($id);
                    if ($req) {
                        Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'SALA', $id, 'REJEITADO');
                    }
                    $this->setFlash('warning', 'Pedido de sala recusado.');
                } elseif ($acao === 'entregar') {
                    // Dar acesso à sala (check-in): a sala passa a estar em uso.
                    $reqModel->marcarEntrega($id);
                    $req = $reqModel->find($id);
                    if ($req) {
                        Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'SALA', $id, 'EM_USO');
                    }
                    $this->setFlash('success', 'Sala entregue ao requisitante (Check-in efetuado).');
                } elseif ($acao === 'finalizar') {
                    // Fechar a reserva (check-out) e registar como veio a sala.
                    $estadoDevolucao = $_POST['estado_devolucao'] ?? 'NORMAL';
                    $reqModel->finalizar($id, $estadoDevolucao);
                    $req = $reqModel->find($id);
                    if ($req) {
                        Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'SALA', $id, 'CONCLUIDO');
                    }
                    $this->setFlash('success', 'Sala devolvida e fluxo concluido (Check-out efetuado).');
                }
            } catch (PDOException $e) {
                $this->setFlash('error', $this->dbErrorMessage($e));
            }
            $this->redirect('requisicaoSala/index');
        }
    }

    // Atalho para abrir o calendário (a mesma página do calendário geral).
    public function calendario()
    {
        Auth::requireLogin();
        $this->viewRaw('calendario/index');
    }

    // Página de acompanhamento da reserva (fluxo tipo CTT), igual à dos materiais.
    public function detalhe($id = 0)
    {
        Auth::requireLogin();
        $id = (int) $id;
        $reqModel = new RequisicaoSala();
        $req = $reqModel->find($id);

        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }

        // Só o dono da reserva ou o pessoal do clube é que pode ver.
        $user = Auth::user();
        $isStaff = Auth::isAdmin() || Auth::isResponsavel() || Auth::isProf();
        if (!$isStaff && (int) ($req['id_utilizador'] ?? 0) !== (int) ($user['id'] ?? -1)) {
            http_response_code(403);
            echo 'Sem permissão para ver este pedido.';
            return;
        }

        $this->view('requisicoes_sala/detalhe', ['req' => $req, 'pageTitle' => 'Acompanhar reserva']);
    }
}
