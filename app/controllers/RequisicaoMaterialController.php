<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../models/RequisicaoMaterial.php';
require_once __DIR__ . '/../models/Material.php';

// Este controller trata dos pedidos de MATERIAL (quando um aluno quer levantar,
// por exemplo, um Arduino ou uma câmara). Faz o percurso todo do pedido:
// criar -> o clube aceita ou rejeita -> entregar ao aluno -> receber de volta.
class RequisicaoMaterialController extends Controller
{
    // Página "Minhas Requisições": mostra os pedidos DESTE utilizador.
    public function index()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $reqModel = new RequisicaoMaterial();
        // Só vou buscar os pedidos feitos por esta pessoa.
        $all = $reqModel->porUtilizador($user['id']);

        // Separo os pedidos em dois grupos: os que ainda estão à espera (pendentes)
        // e o resto (o histórico), para depois mostrar em separadores diferentes.
        $pendentes = [];
        $historico = [];
        foreach ($all as $req) {
            if ($req['estado_pedido'] === 'PENDENTE') {
                $pendentes[] = $req;
            } else {
                $historico[] = $req;
            }
        }

        $this->view('requisicoes_material/index', [
            'pendentes' => $pendentes,
            'historico' => $historico,
            'pageTitle' => 'Requisições de material'
        ]);
    }

    // Criar um pedido novo. Se o formulário foi submetido (POST), gravo o pedido;
    // caso contrário, mostro o formulário para a pessoa preencher.
    public function criar()
    {
        Auth::requireLogin();
        $materialModel = new Material();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Vou buscar os dados que a pessoa escreveu no formulário.
            $materialItemId = (int) ($_POST['material_item_id'] ?? 0);
            $observacao = $this->normalizeText($_POST['observacao'] ?? '', 2000);
            $dataLevantamento = $_POST['data_levantamento'] ?? '';
            $dataDevolucao = $_POST['data_devolucao'] ?? '';
            $user = Auth::user();

            // Validações: confirmo que escolheu um item e que as datas fazem sentido.
            if ($materialItemId <= 0 || !$this->isValidDateTime($dataLevantamento) || !$this->isValidDateTime($dataDevolucao)) {
                $this->setFlash('error', 'Dados invalidos no pedido de material.');
                $this->redirect('requisicaoMaterial/criar');
            }

            // Ponho as datas no formato que o MySQL gosta (ano-mês-dia horas).
            $dataLevantamentoSql = date('Y-m-d H:i:s', strtotime($dataLevantamento));
            $dataDevolucaoSql = date('Y-m-d H:i:s', strtotime($dataDevolucao));

            // A devolução tem de ser DEPOIS do levantamento, senão não faz sentido.
            if (strtotime($dataDevolucaoSql) <= strtotime($dataLevantamentoSql)) {
                $this->setFlash('error', 'A data de devolucao deve ser posterior ao levantamento.');
                $this->redirect('requisicaoMaterial/criar');
            }

            // Confirmo que o exemplar existe e está mesmo disponível.
            $item = $materialModel->findItem($materialItemId);
            if (!$item || ($item['estado'] ?? '') !== 'DISPONIVEL') {
                $this->setFlash('error', 'Exemplar indisponivel.');
                $this->redirect('requisicaoMaterial/criar');
            }

            // Está tudo bem: crio o pedido. Ele começa sempre no estado PENDENTE.
            $reqModel = new RequisicaoMaterial();
            try {
                $novoId = $reqModel->criar((int) $user['id'], $materialItemId, $observacao ?: null, $dataLevantamentoSql, $dataDevolucaoSql);
                $this->setFlash('success', 'Pedido registado com sucesso. Estado: PENDENTE.');
                // Email de confirmação a dizer que o pedido deu entrada no sistema.
                if ($novoId && !empty($user['email'])) {
                    try { Mailer::sendStatusUpdate($user['email'], $user['nome'] ?? 'Utilizador', 'MATERIAL', (int)$novoId, 'PENDENTE'); } catch (\Throwable $e) {}
                }
            } catch (PDOException $e) {
                $this->setFlash('error', $this->dbErrorMessage($e));
            }

            $this->redirect('requisicaoMaterial/index');
        }

        // Se não foi POST, preparo os dados para MOSTRAR o formulário.
        $materialId = (int) ($_GET['material_id'] ?? 0);
        $itensDisponiveis = [];
        $material = null;
        $todosMateriais = [];

        if ($materialId > 0) {
            // Se vier um material específico, mostro só os exemplares desse material.
            $material = $materialModel->findModelo($materialId);
            $itens = $materialModel->itensPorModelo($materialId);
            $itensDisponiveis = array_filter($itens, static fn($item) => ($item['estado'] ?? '') === 'DISPONIVEL');
        } else {
            // Senão, mostro todos os exemplares disponíveis e todos os materiais.
            $itensDisponiveis = $materialModel->itensDisponiveis();
            $todosMateriais = $materialModel->todosModelos();
        }

        $this->view('requisicoes_material/form', [
            'material' => $material,
            'itens' => $itensDisponiveis,
            'todosMateriais' => $todosMateriais,
            'pageTitle' => 'Requisitar material'
        ]);
    }

    // O clube decide o que fazer com o pedido: aceitar, rejeitar ou entregar.
    // Só o pessoal (admin/responsável/professor) é que pode fazer isto.
    public function decidir()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isResponsavel() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        // Este método só funciona por POST (vem de um botão de um formulário).
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('requisicaoMaterial/index');
        }

        $id = (int) ($_POST['id'] ?? 0);
        $acao = $_POST['acao'] ?? '';
        $dataLevantamento = $_POST['data_levantamento'] ?? null;

        if ($id <= 0) {
            $this->setFlash('error', 'Requisicao invalida.');
            $this->redirect('requisicaoMaterial/index');
        }
        // Só deixo passar estas três ações; qualquer outra coisa é recusada.
        if (!$this->isValidEnumValue($acao, ['aceitar', 'rejeitar', 'entregar'])) {
            $this->setFlash('error', 'Acao invalida para a requisicao.');
            $this->redirect('requisicaoMaterial/index');
        }

        $reqModel = new RequisicaoMaterial();
        try {
            if ($acao === 'aceitar') {
                // Aceitar: se não veio uma data de entrega, uso a de agora.
                $data = $dataLevantamento && $this->isValidDateTime($dataLevantamento)
                    ? date('Y-m-d H:i:s', strtotime($dataLevantamento))
                    : date('Y-m-d H:i:s');
                $reqModel->atualizarEstado($id, 'ACEITE', $data);
                // E aviso a pessoa por email que o pedido foi aceite.
                $req = $reqModel->find($id);
                if ($req) {
                    Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'MATERIAL', $id, 'ACEITE');
                }
                $this->setFlash('success', 'Pedido aceite. Data de entrega definida.');
            } elseif ($acao === 'rejeitar') {
                // Rejeitar: mudo o estado e aviso por email.
                $reqModel->rejeitar($id);
                $req = $reqModel->find($id);
                if ($req) {
                    Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'MATERIAL', $id, 'REJEITADO');
                }
                $this->setFlash('warning', 'Pedido recusado.');
            } elseif ($acao === 'entregar') {
                // Entregar: o aluno foi buscar o material (fica "em uso").
                $reqModel->marcarEmUso($id);
                $req = $reqModel->find($id);
                if ($req) {
                    Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'MATERIAL', $id, 'EM_USO');
                }
                $this->setFlash('success', 'Material entregue ao requisitante.');
            }
        } catch (PDOException $e) {
            $this->setFlash('error', $this->dbErrorMessage($e));
        }

        $this->redirect('requisicaoMaterial/index');
    }

    // Última fase: o aluno devolve o material ao clube.
    // Aqui também se regista se veio bem, danificado ou se se perdeu.
    public function devolver()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf() && !Auth::isResponsavel()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = (int) ($_POST['id'] ?? 0);
            $estadoDevolucao = $_POST['estado_devolucao'] ?? 'OK';
            // Só aceito estes três estados de devolução.
            if ($id <= 0 || !$this->isValidEnumValue($estadoDevolucao, ['OK', 'DANIFICADO', 'PERDIDO'])) {
                $this->setFlash('error', 'Dados invalidos na devolucao.');
                $this->redirect('requisicaoMaterial/index');
            }

            $reqModel = new RequisicaoMaterial();
            try {
                // Marco como devolvido (o pedido fica CONCLUIDO) e aviso por email.
                $reqModel->devolver($id, $estadoDevolucao);
                $req = $reqModel->find($id);
                if ($req) {
                    Mailer::sendStatusUpdate($req['utilizador_email'] ?? '', $req['utilizador_nome'] ?? 'Utilizador', 'MATERIAL', $id, 'CONCLUIDO');
                }
                $this->setFlash('success', 'Devolucao concluida com sucesso.');
            } catch (PDOException $e) {
                $this->setFlash('error', $this->dbErrorMessage($e));
            }

            $this->redirect('requisicaoMaterial/index');
        }
    }

    // Página de acompanhamento do pedido (o tal fluxo tipo CTT).
    // Mostra em que fase está o pedido, com as datas de cada passo.
    public function detalhe($id = 0)
    {
        Auth::requireLogin();
        $id = (int) $id;
        $reqModel = new RequisicaoMaterial();
        $req = $reqModel->find($id);

        // Se o pedido não existir, mostro erro 404.
        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }

        // Só o dono do pedido ou o pessoal do clube é que pode ver o detalhe.
        $user = Auth::user();
        $isStaff = Auth::isAdmin() || Auth::isResponsavel() || Auth::isProf();
        if (!$isStaff && (int) ($req['id_utilizador'] ?? 0) !== (int) ($user['id'] ?? -1)) {
            http_response_code(403);
            echo 'Sem permissão para ver este pedido.';
            return;
        }

        $this->view('requisicoes_material/detalhe', ['req' => $req, 'pageTitle' => 'Acompanhar pedido']);
    }
}
