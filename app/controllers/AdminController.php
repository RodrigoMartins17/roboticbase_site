<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/Sala.php';
require_once __DIR__ . '/../models/Utilizador.php';
require_once __DIR__ . '/../models/RequisicaoMaterial.php';
require_once __DIR__ . '/../models/RequisicaoSala.php';
require_once __DIR__ . '/../models/Exemplar.php';

require_once __DIR__ . '/../models/PortfolioEvento.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../models/Log.php';

class AdminController extends Controller
{
    // Envia um email ao utilizador a avisar que a requisição mudou de estado,
    // e deixa um registo de auditoria (quem alterou o quê).
    // $req já vem do find() (traz utilizador_nome e utilizador_email).
    private function notificarEstado($req, string $tipoReq, string $estadoLabel): void
    {
        $email = $req['utilizador_email'] ?? '';
        $nome  = $req['utilizador_nome'] ?? 'Utilizador';
        $id    = (int)($req['id'] ?? 0);
        if (!empty($email)) {
            // Se o email falhar não quero rebentar com a ação do admin, por isso "engolimos" o erro.
            try { Mailer::sendStatusUpdate($email, $nome, $tipoReq, $id, $estadoLabel); } catch (\Throwable $e) {}
        }
        // Auditoria: fica registado que o admin mudou o estado desta requisição.
        $admin = Auth::user();
        (new Log())->registar(isset($admin['id']) ? (int)$admin['id'] : null, 'ALTERACAO',
            'Requisição de ' . $tipoReq . ' #' . $id . ' -> ' . $estadoLabel . ' (utilizador: ' . $nome . ')');
    }

    // Regista uma ação do admin no log de auditoria (quem fez o quê).
    // Uso isto nas criações e eliminações de registos do painel.
    private function registarAcao(string $tipo, string $descricao): void
    {
        $admin = Auth::user();
        (new Log())->registar(isset($admin['id']) ? (int)$admin['id'] : null, $tipo, $descricao);
    }

    private function requireAdmin()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isResponsavel()) {
            http_response_code(403);
            echo 'Sem permissão.';
            exit;
        }
    }

    public function index()
    {
        $this->requireAdmin();
        $materialModel = new Material();
        $categoriaModel = new Categoria();
        $salaModel = new Sala();
        $utilizadorModel = new Utilizador();
        $reqMat = new RequisicaoMaterial();
        $reqSala = new RequisicaoSala();
        
        // Datas para o calendário — com navegação de semanas (?semana=-1, 0, +1, ...).
        $offsetSemana = (int)($_GET['semana'] ?? 0);
        $hoje = date('Y-m-d');
        $diaSemana = date('N');
        // Segunda-feira da semana atual e depois "salto" de X semanas conforme o offset.
        $segundaAtual = date('Y-m-d', strtotime('-' . ($diaSemana - 1) . ' days'));
        $inicioSemana = date('Y-m-d', strtotime($segundaAtual . ' ' . ($offsetSemana * 7) . ' days'));
        $fimSemana = date('Y-m-d', strtotime($inicioSemana . ' + 6 days'));
        
        // --- PREENCHER O CALENDÁRIO ---
        // Aqui chamamos as novas funções que criaste nos Models
        $eventosMateriais = $reqMat->getEventosCalendario($inicioSemana, $fimSemana);
        $eventosSalas = $reqSala->getEventosCalendario($inicioSemana, $fimSemana);
        
        // Junta os dois arrays num só
        $eventosBrutos = array_merge($eventosMateriais, $eventosSalas);
        // ------------------------------

        // Estatísticas e contagens
        $totalMateriais   = $materialModel->totalModelos();
        $totalExemplares  = $materialModel->totalExemplares();
        $totalCategorias = count($categoriaModel->all());
        $totalSalas      = count($salaModel->todas());
        $totalUtilizadores = count($utilizadorModel->all());
        $reqMatPendentes = count($reqMat->pendentes());
        $reqSalaPendentes = count($reqSala->pendentes());
        $reqMatTotal     = $reqMat->total();
        $reqSalaTotal    = $reqSala->total();
        $totalRequisicoes = $reqMatTotal + $reqSalaTotal;

        // Dados para os gráficos e tabelas
        $porAceitarMat  = $reqMat->pendentes();
        $porAceitarSala = $reqSala->pendentes();
        $aEntregarMat   = $reqMat->aceites();
        $aEntregarSala  = $reqSala->aceites();

        
        // Enviar tudo para a View
        $this->viewAdmin('administracao/index', [
            'totalMateriais'     => $totalMateriais,
            'totalExemplares'    => $totalExemplares,
            'totalCategorias'    => $totalCategorias,
            'totalSalas'         => $totalSalas,
            'totalUtilizadores'  => $totalUtilizadores,
            'totalRequisicoes'   => $totalRequisicoes,
            'porAceitarMat'      => $porAceitarMat,
            'porAceitarSala'     => $porAceitarSala,
            'aEntregarMat'       => $aEntregarMat,
            'aEntregarSala'      => $aEntregarSala,
            'eventosBrutos'      => $eventosBrutos,
            'inicioSemanaCal'    => $inicioSemana,
            'offsetSemana'       => $offsetSemana,
        ], '', 'inicio');
    }
    
    // ---------- Materiais ----------
    public function materiais()
    {
        $this->requireAdmin();
        $model = new Material();
        $todos = $model->attachImagemSrc($model->todosModelos());

        // Pesquisa pela designação do material + páginas de 8.
        $filtrados = $this->aplicarFiltros($todos, ['designacao']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/materiais/index', [
            'materiais' => $paginacao['itens'],
            'paginacao' => $paginacao,
        ], 'Inventário (Materiais)', 'materiais');
    }

    public function materialCreate()
    {
        $this->requireAdmin();
        $catModel = new Categoria();
        $categorias = $catModel->all();
        $this->viewAdmin('administracao/materiais/create', ['categorias' => $categorias, 'material' => null], 'Novo Material', 'materiais');
    }

    public function materialStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/materiais');
            return;
        }
        $model = new Material();
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $dataModelo = [
            'designacao' => $_POST['designacao'] ?? '',
            'descricao'  => $_POST['descricao'] ?? '',
            'imagem'     => $imagemBlob,
        ];
        try {
            $model->createModelo($dataModelo);
            $modeloId = (int)$model->getDb()->lastInsertId();
            if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
                foreach ($_POST['categorias'] as $categoriaId) {
                    $model->adicionarCategoria($modeloId, (int)$categoriaId);
                }
            } elseif (!empty($_POST['categoria_id'])) {
                $model->adicionarCategoria($modeloId, (int)$_POST['categoria_id']);
            }
            $numItens = (int)($_POST['num_itens'] ?? 1);
            $baseRef = trim($_POST['num_referencia'] ?? '');
            if ($baseRef === '') {
                $baseRef = 'REF-' . date('Ymd-His');
            }
            for ($i = 0; $i < $numItens; $i++) {
                $numReferencia = $baseRef . ($i > 0 ? '-' . ($i + 1) : '');
                $model->createItem([
                    'num_referencia' => $numReferencia,
                    'id_material'   => $modeloId,
                    'estado'       => 'DISPONIVEL',
                    'observacao'   => null,
                ]);
            }
        } catch (PDOException $e) {
            $catModel = new Categoria();
            $categorias = $catModel->all();
            $this->viewAdmin('administracao/materiais/create', [
                'categorias' => $categorias,
                'material'   => null,
                'error'     => $this->dbErrorMessage($e),
            ], 'Novo Material', 'materiais');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Material criado: ' . ($_POST['designacao'] ?? '?'));
        $this->redirect('admin/materiais');
    }

    public function materialEdit($id)
    {
        $this->requireAdmin();
        $model = new Material();
        $catModel = new Categoria();
        $material = $model->modeloComCategorias((int)$id);
        if (!$material) {
            http_response_code(404);
            echo 'Material não encontrado.';
            return;
        }
        $material['imagem_src'] = $model->getImagemSrc($material['imagem'] ?? null);
        $categorias = $catModel->all();
        $itens = $model->itensPorModelo((int)$id);
        $this->viewAdmin('administracao/materiais/edit', [
            'material'   => $material,
            'categorias' => $categorias,
            'itens'      => $itens,
        ], 'Editar Material', 'materiais');
    }

    public function materialUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/materiais');
            return;
        }
        $model = new Material();
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $current = $model->findModelo((int)$id);
        $imagemFinal = $imagemBlob ?? ($current['imagem'] ?? null);
        try {
            $model->updateModelo((int)$id, [
                'designacao' => $_POST['designacao'] ?? '',
                'descricao'  => $_POST['descricao'] ?? '',
                'imagem'     => $imagemFinal,
            ]);
            if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
                $model->atualizarCategorias((int)$id, array_map('intval', $_POST['categorias']));
            } elseif (!empty($_POST['categoria_id'])) {
                $model->atualizarCategorias((int)$id, [(int)$_POST['categoria_id']]);
            }
        } catch (PDOException $e) {
            $catModel = new Categoria();
            $material = $model->modeloComCategorias((int)$id);
            if ($material) {
                $material['imagem_src'] = $model->getImagemSrc($material['imagem'] ?? null);
            }
            $this->viewAdmin('administracao/materiais/edit', [
                'material'   => $material,
                'categorias' => $catModel->all(),
                'itens'      => $model->itensPorModelo((int)$id),
                'error'      => $this->dbErrorMessage($e),
            ], 'Editar Material', 'materiais');
            return;
        }
        $this->redirect('admin/materiais');
    }

    public function materialView($id)
    {
        $this->requireAdmin();
        $model = new Material();
        $material = $model->modeloComCategorias((int)$id);
        if (!$material) {
            http_response_code(404);
            echo 'Material não encontrado.';
            return;
        }
        $material['imagem_src'] = $model->getImagemSrc($material['imagem'] ?? null);
        $itens = $model->itensPorModelo((int)$id);
        $this->viewAdmin('administracao/materiais/view', [
            'material' => $material,
            'itens'   => $itens,
        ], 'Detalhe Material', 'materiais');
    }

    public function materialDelete($id)
    {
        $this->requireAdmin();
        
        $model = new Material();
        $material = $model->findModelo((int)$id);
        
        if (!$material) {
            $this->redirect('admin/materiais');
            return;
        }

        $this->viewAdmin('administracao/materiais/delete', ['material' => $material], 'Eliminar Material', 'materiais');
    }

    public function materialDestroy($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/materiais');
            return;
        }

        $model = new Material();
        $id = (int)$id;
        try {
            $db = $model->getDb();
            $db->beginTransaction();
            $ids = $db->prepare("SELECT id FROM exemplar WHERE id_material = ?");
            $ids->execute([$id]);
            $exemplarIds = $ids->fetchAll(PDO::FETCH_COLUMN);
            if (!empty($exemplarIds)) {
                $placeholders = implode(',', array_fill(0, count($exemplarIds), '?'));
                $stmt = $db->prepare("DELETE FROM requisicao_exemplar WHERE id_exemplar IN ($placeholders)");
                $stmt->execute($exemplarIds);
            }
            $stmt = $db->prepare("DELETE FROM exemplar WHERE id_material = ?");
            $stmt->execute([$id]);
            $stmt = $db->prepare("DELETE FROM categoria_material WHERE id_material = ?");
            $stmt->execute([$id]);
            $model->deleteModelo($id);
            $db->commit();
        } catch (PDOException $e) {
            if (isset($db)) {
                $db->rollBack();
            }
            $material = $model->findModelo((int)$id);
            $this->viewAdmin('administracao/materiais/delete', [
                'material' => $material,
                'error' => $this->dbErrorMessage($e),
            ], 'Eliminar Material', 'materiais');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Material eliminado (id ' . (int)$id . ')');
        $this->redirect('admin/materiais');
    }

    // ---------- Exemplares ----------
    public function exemplares()
    {
        $this->requireAdmin();
        $model = new Material();
        $salaModel = new Sala();
        $exemplares = $model->todosItens();
        foreach ($exemplares as &$e) {
            $stmt = $model->getDb()->prepare("SELECT s.* FROM exemplar_sala es JOIN sala s ON es.id_sala = s.id WHERE es.id_exemplar = ?");
            $stmt->execute([$e['id']]);
            $sala = $stmt->fetch(PDO::FETCH_ASSOC);
            $e['sala_nome'] = $sala ? $sala['bloco'] . $sala['andar'] . '.' . $sala['numero'] : 'N/A';
        }
        // Pesquisa pelo material ou nº de referência, dropdown do estado, páginas de 8.
        $filtrados = $this->aplicarFiltros($exemplares, ['num_referencia', 'sala_nome'], ['estado' => 'estado', 'material' => 'designacao']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/exemplares/index', [
            'exemplares' => $paginacao['itens'],
            'paginacao'  => $paginacao,
            'estadosExemplar' => $this->opcoesDe($exemplares, 'estado'),
            // Lista dos materiais que existem, para o dropdown "Material".
            'materiaisOpcoes' => $this->opcoesDe($exemplares, 'designacao'),
            'materialFiltro' => null,
        ], 'Exemplares', 'exemplares');
    }

    public function exemplaresPorMaterial($materialId)
    {
        $this->requireAdmin();
        $model = new Material();
        $salaModel = new Sala();
        $material = $model->findModelo((int)$materialId);
        if (!$material) {
            http_response_code(404);
            echo 'Material não encontrado.';
            return;
        }
        $exemplares = $model->itensPorModelo((int)$materialId);
        foreach ($exemplares as &$e) {
            $stmt = $model->getDb()->prepare("SELECT s.* FROM exemplar_sala es JOIN sala s ON es.id_sala = s.id WHERE es.id_exemplar = ?");
            $stmt->execute([$e['id']]);
            $sala = $stmt->fetch(PDO::FETCH_ASSOC);
            $e['sala_nome'] = $sala ? $sala['bloco'] . $sala['andar'] . '.' . $sala['numero'] : 'N/A';
        }
        $this->viewAdmin('administracao/exemplares/index', [
            'exemplares' => $exemplares,
            'materialFiltro' => $material,
        ], 'Exemplares: ' . ($material['designacao'] ?? ''), 'exemplares');
    }

    public function exemplarCreate()
    {
        $this->requireAdmin();
        $model = new Material();
        $salaModel = new Sala();
        $materiais = $model->todosModelos();
        $salas = $salaModel->todas();
        $this->viewAdmin('administracao/exemplares/create', ['materiais' => $materiais, 'salas' => $salas, 'materialPre' => null], 'Novo Exemplar', 'exemplares');
    }

    public function exemplarCreateComMaterial($materialId)
    {
        $this->requireAdmin();
        $model = new Material();
        $salaModel = new Sala();
        $material = $model->findModelo((int)$materialId);
        if (!$material) {
            http_response_code(404);
            echo 'Material não encontrado.';
            return;
        }
        $materiais = $model->todosModelos();
        $salas = $salaModel->todas();
        $this->viewAdmin('administracao/exemplares/create', ['materiais' => $materiais, 'salas' => $salas, 'materialPre' => $material], 'Novo Exemplar', 'exemplares');
    }

    public function exemplarStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/exemplares');
            return;
        }
        $model = new Material();
        $idMaterial = (int)($_POST['id_material'] ?? 0);
        $numRef = trim($_POST['num_referencia'] ?? '');
        if ($numRef === '') {
            $numRef = 'REF-' . uniqid();
        }
        $data = [
            'num_referencia' => $numRef,
            'id_material'    => $idMaterial,
            'estado'         => $_POST['estado'] ?? 'DISPONIVEL',
            'observacao'     => !empty($_POST['observacao']) ? $_POST['observacao'] : null,
        ];
        try {
            $model->createItem($data);
            $lastId = $model->getDb()->lastInsertId();
            if (!empty($_POST['id_sala']) && (int)$_POST['id_sala'] > 0) {
                $model->adicionarSalaExemplar($lastId, (int)$_POST['id_sala']);
            }
        } catch (PDOException $e) {
            $materiais = $model->todosModelos();
            $salas = (new Sala())->todas();
            $this->viewAdmin('administracao/exemplares/create', [
                'materiais'   => $materiais,
                'salas'       => $salas,
                'materialPre' => $idMaterial ? $model->findModelo($idMaterial) : null,
                'error'       => $this->dbErrorMessage($e),
            ], 'Novo Exemplar', 'exemplares');
            return;
        }
        if ($idMaterial) {
            $this->redirect('admin/exemplaresPorMaterial/' . $idMaterial);
        } else {
            // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Exemplar criado: ' . ($_POST['num_referencia'] ?? '?'));
        $this->redirect('admin/exemplares');
        }
    }
       

    public function exemplarView($id)
    {
        $this->requireAdmin();
        $model = new Material();
        $exemplar = $model->findItem((int)$id);
        if (!$exemplar) {
            http_response_code(404);
            echo 'Exemplar não encontrado.';
            return;
        }
        $material = $model->findModelo((int)$exemplar['id_material']);

        // Vou buscar a sala onde este exemplar está armazenado (se tiver uma),
        // para a ficha mostrar a localização em vez de "Nenhuma sala associada".
        $stmt = $model->getDb()->prepare(
            "SELECT s.* FROM exemplar_sala es JOIN sala s ON es.id_sala = s.id WHERE es.id_exemplar = ?"
        );
        $stmt->execute([(int)$exemplar['id']]);
        $sala = $stmt->fetch(PDO::FETCH_ASSOC);
        $exemplar['nome_sala'] = $sala ? ($sala['bloco'] . $sala['andar'] . '.' . $sala['numero']) : null;

        $this->viewAdmin('administracao/exemplares/view', [
            'exemplar' => $exemplar,
            'material' => $material,
            'sala'     => $sala ?: null,
        ], 'Exemplar', 'exemplares');
    }

    public function exemplarEdit($id)
    {
        $this->requireAdmin();
        $model = new Material();
        $salaModel = new Sala();
        $exemplar = $model->findItem((int)$id);
        if (!$exemplar) {
            http_response_code(404);
            echo 'Exemplar não encontrado.';
            return;
        }
        $materiais = $model->todosModelos();
        $salas = $salaModel->todas();
        $this->viewAdmin('administracao/exemplares/edit', [
            'exemplar'  => $exemplar,
            'materiais' => $materiais,
            'salas'     => $salas,
        ], 'Editar Exemplar', 'exemplares');
    }

    public function exemplarDelete($id)
    {
        $this->requireAdmin();
        
        $exemplarModel = new Exemplar();
        $exemplar = $exemplarModel->find((int)$id);
        
        if (!$exemplar) {
            $this->redirect('admin/exemplares');
            return;
        }

        $this->viewAdmin('administracao/exemplares/delete', ['exemplar' => $exemplar], 'Eliminar Exemplar', 'exemplares');
    }

    public function exemplarDestroy($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/exemplares');
            return;
        }

        $id = (int)$id;
        $exemplarModel = new Exemplar();
        try {
            $db = $exemplarModel->getDb();
            $db->beginTransaction();

            // 1. Eliminar requisições associadas a este exemplar
            $stmt = $db->prepare("DELETE FROM requisicao_exemplar WHERE id_exemplar = ?");
            $stmt->execute([$id]);

            // 2. Eliminar associações de sala
            $stmt = $db->prepare("DELETE FROM exemplar_sala WHERE id_exemplar = ?");
            $stmt->execute([$id]);

            // 3. Eliminar o exemplar
            $stmt = $db->prepare("DELETE FROM exemplar WHERE id = ?");
            $stmt->execute([$id]);

            $db->commit();
        } catch (PDOException $e) {
            if (isset($db)) $db->rollBack();
            $exemplar = $exemplarModel->find($id);
            $this->viewAdmin('administracao/exemplares/delete', [
                'exemplar' => $exemplar,
                'error' => 'Não foi possível eliminar o exemplar: ' . $this->dbErrorMessage($e),
            ], 'Eliminar Exemplar', 'exemplares');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Exemplar eliminado (id ' . (int)$id . ')');
        $this->redirect('admin/exemplares');
    }

    public function exemplarUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/exemplares');
            return;
        }
        $model = new Material();
        $exemplar = $model->findItem((int)$id);
        if (!$exemplar) {
            $this->redirect('admin/exemplares');
            return;
        }
        $numRef = trim($_POST['num_referencia'] ?? '');
        if ($numRef === '') {
            $numRef = $exemplar['num_referencia'];
        }
        $data = [
            'num_referencia' => $numRef,
            'id_material'    => (int)($_POST['id_material'] ?? $exemplar['id_material']),
            'estado'         => $_POST['estado'] ?? 'DISPONIVEL',
            'observacao'     => !empty($_POST['observacao']) ? $_POST['observacao'] : null,
        ];
        $newIdSala = !empty($_POST['id_sala']) ? (int)$_POST['id_sala'] : 0;
        try {
            $model->updateItem((int)$id, $data);
            if ($newIdSala > 0) {
                $model->adicionarSalaExemplar((int)$id, $newIdSala);
            } else {
                $model->removerSalaExemplar((int)$id);
            }
        } catch (PDOException $e) {
            $materiais = $model->todosModelos();
            $this->viewAdmin('administracao/exemplares/edit', [
                'exemplar'  => $exemplar,
                'materiais' => $materiais,
                'error'     => $this->dbErrorMessage($e),
            ], 'Editar Exemplar', 'exemplares');
            return;
        }
        $this->redirect('admin/exemplaresPorMaterial/' . (int)$data['id_material']);
    }



    // ---------- Categorias ----------
    public function categorias()
    {
        $this->requireAdmin();
        $model = new Categoria();
        $todas = $model->all();

        // Pesquisa pelo nome da categoria + páginas de 8.
        $filtradas = $this->aplicarFiltros($todas, ['categoria']);
        $paginacao = $this->paginar($filtradas, 8);

        $this->viewAdmin('administracao/categorias/index', [
            'categorias' => $paginacao['itens'],
            'paginacao'  => $paginacao,
        ], 'Categorias', 'categorias');
    }

    public function categoriaCreate()
    {
        $this->requireAdmin();
        $this->viewAdmin('administracao/categorias/create', ['categoria' => null], 'Nova Categoria', 'categorias');
    }

    public function categoriaStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categorias');
            return;
        }
        $nome = trim($_POST['categoria'] ?? '');
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        try {
            $model = new Categoria();
            $model->create($nome, $imagemBlob);
        } catch (PDOException $e) {
            $this->viewAdmin('administracao/categorias/create', [
                'categoria' => null,
                'error'    => $this->dbErrorMessage($e),
            ], 'Nova Categoria', 'categorias');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Categoria criada: ' . ($_POST['categoria'] ?? $_POST['nome'] ?? '?'));
        $this->redirect('admin/categorias');
    }

    public function categoriaView($id)
    {
        $this->requireAdmin();
        $model = new Categoria();
        $categoria = $model->find((int)$id);
        if (!$categoria) {
            http_response_code(404);
            echo 'Categoria não encontrada.';
            return;
        }
        $categoria['imagem_src'] = (new PortfolioEvento())->getImagemSrc($categoria['imagem'] ?? null);
        $materiais = method_exists($model, 'materiais') ? $model->materiais((int)$id) : [];
        $this->viewAdmin('administracao/categorias/view', [
            'categoria' => $categoria,
            'materiais' => $materiais
        ], 'Detalhes da Categoria', 'categorias');
    }

    public function categoriaEdit($id)
    {
        $this->requireAdmin();
        $model = new Categoria();
        $categoria = $model->find((int)$id);
        if (!$categoria) {
            http_response_code(404);
            echo 'Categoria não encontrada.';
            return;
        }
        $categoria['imagem_src'] = (new Material())->getImagemSrc($categoria['imagem'] ?? null);
        $this->viewAdmin('administracao/categorias/edit', ['categoria' => $categoria], 'Editar Categoria', 'categorias');
    }

    public function categoriaUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categorias');
            return;
        }
        $nome = trim($_POST['categoria'] ?? '');
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $model = new Categoria();
        $current = $model->find((int)$id);
        $imagemFinal = $imagemBlob ?? ($current['imagem'] ?? null);
        try {
            $model->update((int)$id, $nome, $imagemFinal);
        } catch (PDOException $e) {
            $categoria = $model->find((int)$id);
            $categoria['imagem_src'] = (new Material())->getImagemSrc($categoria['imagem'] ?? null);
            $this->viewAdmin('administracao/categorias/edit', [
                'categoria' => $categoria,
                'error'    => $this->dbErrorMessage($e),
            ], 'Editar Categoria', 'categorias');
            return;
        }
        $this->redirect('admin/categorias');
    }

    public function salas()
    {
        $this->requireAdmin();
        $model = new Sala();
        $todas = $model->todas();

        // Pesquisa por bloco/número, dropdown do estado, páginas de 8.
        $filtradas = $this->aplicarFiltros($todas, ['bloco', 'numero', 'andar'], ['estado' => 'estado']);
        $paginacao = $this->paginar($filtradas, 8);

        $this->viewAdmin('administracao/salas/index', [
            'salas'        => $paginacao['itens'],
            'paginacao'    => $paginacao,
            'estadosSala'  => $this->opcoesDe($todas, 'estado'),
        ], 'Salas', 'salas');
    }

// 1. MOSTRAR A PÁGINA DE CONFIRMAÇÃO
    public function categoriaDelete($id)
    {
        $this->requireAdmin();
        
        $model = new Categoria();
        $categoria = $model->find((int)$id);
        
        // Se a categoria não existir, volta para a lista
        if (!$categoria) {
            $this->redirect('admin/categorias');
            return;
        }

        // Manda para a vista delete.php com os dados da categoria
        $this->viewAdmin('administracao/categorias/delete', ['categoria' => $categoria], 'Eliminar Categoria', 'categorias');
    }

    // 2. EXECUTAR O APAGAMENTO (Quando o utilizador clica em "Apagar" no delete.php)
    public function categoriaDestroy($id)
    {
        $this->requireAdmin();
        
        // Garante que só apaga se vier do formulário (método POST)
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/categorias');
            return;
        }

        $model = new Categoria();
        $forcar = isset($_POST['forcar']) && $_POST['forcar'] === '1';

        try {
            // Se houver botão de forçar (por causa de ligações noutras tabelas)
            if ($forcar && method_exists($model, 'removerAssociacoes')) {
                $model->removerAssociacoes((int)$id);
            }
            
            // Apaga definitivamente
            $model->delete((int)$id);
            
        } catch (PDOException $e) {
            // Se der erro (ex: categoria tem materiais associados e não usaste o forçar)
            $categoria = $model->find((int)$id);
            $this->viewAdmin('administracao/categorias/delete', [
                'categoria' => $categoria,
                'erro' => $this->dbErrorMessage($e),
                'mostrar_botao_forcar' => true
            ], 'Eliminar Categoria', 'categorias');
            return;
        }

        // Volta para a lista de categorias com sucesso
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Categoria eliminada (id ' . (int)$id . ')');
        $this->redirect('admin/categorias');
    }

    public function salaCreate()
    {
        $this->requireAdmin();
        $this->viewAdmin('administracao/salas/create', ['sala' => null], 'Nova Sala', 'salas');
    }

    public function salaStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/salas');
            return;
        }
        // Foto da sala (opcional). O readImageBlob valida e comprime.
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $data = [
            'numero'     => trim($_POST['numero'] ?? ''),
            'andar'      => (int)($_POST['andar'] ?? 0),
            'bloco'      => trim($_POST['bloco'] ?? ''),
            'capacidade' => (int)($_POST['capacidade'] ?? 0),
            'descricao'  => trim($_POST['descricao'] ?? null) ?: null,
            'estado'     => $_POST['estado'] ?? 'DISPONIVEL',
            'imagem'     => $imagemBlob,
        ];
        if (empty($data['numero']) || empty($data['bloco'])) {
            $this->viewAdmin('administracao/salas/create', [
                'sala'  => (object)$data,
                'error' => 'Número e bloco são obrigatórios.',
            ], 'Nova Sala', 'salas');
            return;
        }
        try {
            $model = new Sala();
            $model->create($data);
        } catch (PDOException $e) {
            $this->viewAdmin('administracao/salas/create', [
                'sala'   => null,
                'error'  => $this->dbErrorMessage($e),
            ], 'Nova Sala', 'salas');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Sala criada: ' . ($_POST['bloco'] ?? '') . ($_POST['andar'] ?? '') . '.' . ($_POST['numero'] ?? ''));
        $this->redirect('admin/salas');
    }

    public function salaEdit($id)
    {
        $this->requireAdmin();
        $model = new Sala();
        $sala = $model->find((int)$id);
        if (!$sala) {
            http_response_code(404);
            echo 'Sala não encontrada.';
            return;
        }
        $this->viewAdmin('administracao/salas/edit', ['sala' => $sala], 'Editar Sala', 'salas');
    }

    public function salaUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/salas');
            return;
        }
        $model = new Sala();
        $sala = $model->find((int)$id);
        // Se enviaram foto nova leio-a; se não, fica NULL e o model mantém a antiga.
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $data = [
            'numero'     => trim($_POST['numero'] ?? $sala['numero'] ?? ''),
            'andar'      => (int)($_POST['andar'] ?? $sala['andar'] ?? 0),
            'bloco'      => trim($_POST['bloco'] ?? $sala['bloco'] ?? ''),
            'capacidade' => (int)($_POST['capacidade'] ?? $sala['capacidade'] ?? 0),
            'descricao'  => trim($_POST['descricao'] ?? $sala['descricao'] ?? null) ?: null,
            'estado'     => $_POST['estado'] ?? $sala['estado'] ?? 'DISPONIVEL',
            'imagem'     => $imagemBlob,
        ];
        if (empty($data['numero']) || empty($data['bloco'])) {
            $this->viewAdmin('administracao/salas/edit', [
                'sala'   => $sala ?: (object)$data,
                'error'  => 'Número e bloco são obrigatórios.',
            ], 'Editar Sala', 'salas');
            return;
        }
        try {
            $model->updateById((int)$id, $data);
        } catch (PDOException $e) {
            $sala = $model->find((int)$id);
            $this->viewAdmin('administracao/salas/edit', [
                'sala'   => $sala,
                'error'  => $this->dbErrorMessage($e),
            ], 'Editar Sala', 'salas');
            return;
        }
        $this->redirect('admin/salas');
    }

    public function salaView($id)
    {
        $this->requireAdmin();
        $model = new Sala();
        $sala = $model->find((int)$id);
        if (!$sala) {
            http_response_code(404);
            echo 'Sala não encontrada.';
            return;
        }
        $this->viewAdmin('administracao/salas/view', ['sala' => $sala], 'Detalhe Sala', 'salas');
    }

    public function salaDelete($id)
    {
        $this->requireAdmin();
        
        $model = new Sala();
        $sala = $model->find((int)$id);
        
        if (!$sala) {
            $this->redirect('admin/salas');
            return;
        }

        $this->viewAdmin('administracao/salas/delete', ['sala' => $sala], 'Eliminar Sala', 'salas');
    }

    public function salaDestroy($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/salas');
            return;
        }

        $id = (int)$id;
        $model = new Sala();
        try {
            $db = $model->getDb();
            $db->beginTransaction();

            // 1. Eliminar requisições associadas a esta sala
            $stmt = $db->prepare("DELETE FROM requisicao_sala WHERE id_sala = ?");
            $stmt->execute([$id]);

            // 2. Eliminar associações de exemplares (exemplar_sala)
            $stmt = $db->prepare("DELETE FROM exemplar_sala WHERE id_sala = ?");
            $stmt->execute([$id]);

            // 3. Eliminar a sala
            $model->deleteById($id);

            $db->commit();
        } catch (PDOException $e) {
            if (isset($db)) $db->rollBack();
            $sala = $model->find($id);
            $this->viewAdmin('administracao/salas/delete', [
                'sala' => $sala,
                'error' => 'Não foi possível eliminar a sala: ' . $this->dbErrorMessage($e),
            ], 'Eliminar Sala', 'salas');
            return;
        }

        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Sala eliminada (id ' . (int)$id . ')');
        $this->redirect('admin/salas');
    }

    // ---------- Utilizadores ----------
    public function utilizadores()
    {
        $this->requireAdmin();
        $model = new Utilizador();
        $todos = $model->all();

        // Filtro pela pesquisa (nome ou email) e pelo dropdown do tipo de conta,
        // e depois parto o resultado em páginas de 8.
        $filtrados = $this->aplicarFiltros($todos, ['nome', 'email'], ['tipo' => 'tipo']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/utilizadores/index', [
            'users'     => $paginacao['itens'],
            'paginacao' => $paginacao,
            // As opções do dropdown vêm dos dados reais (os tipos que existem mesmo).
            'tiposUser' => $this->opcoesDe($todos, 'tipo'),
        ], 'Utilizadores', 'utilizadores');
    }

    public function utilizadorCreate()
    {
        $this->requireAdmin();
        $this->viewAdmin('administracao/utilizadores/create', ['user' => null], 'Novo Utilizador', 'utilizadores');
    }

    public function utilizadorStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/utilizadores');
            return;
        }
        $password = $_POST['palavra_passe'] ?? '';
        if ($password === '') {
            $password = 'password';
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $tipo = $_POST['tipo'] ?? 'ALUNO';
        if ($tipo === 'PROFESSOR' && isset($_POST['responsavel'])) {
            $tipo = 'RESPONSAVEL';
        }
        $fotoBlob = isset($_FILES['foto_perfil']) ? $this->readImageBlob($_FILES['foto_perfil']) : null;
        $data = [
            'nome'           => $_POST['nome'] ?? '',
            'email'          => $_POST['email'] ?? '',
            'password_hash'  => $passwordHash,
            'tipo'           => $tipo,
            'telefone'       => $_POST['telefone'] ?? '',
            'linkedin'       => !empty($_POST['linkedin']) ? $_POST['linkedin'] : null,
            'turma'          => !empty($_POST['turma']) ? $_POST['turma'] : null,
            'data_nascimento' => !empty($_POST['data_nascimento']) ? $_POST['data_nascimento'] : date('Y-m-d'),
            'foto'           => $fotoBlob,
            // Contas criadas pelo admin nascem já verificadas (não faz sentido
            // pedir ao próprio admin para ir confirmar o email da pessoa).
            'email_verificado'        => 1,
            'email_verificacao_token' => null,
        ];
        try {
            $model = new Utilizador();
            $model->create($data);
        } catch (PDOException $e) {
            $this->viewAdmin('administracao/utilizadores/create', [
                'user'   => null,
                'error'  => $this->dbErrorMessage($e),
            ], 'Novo Utilizador', 'utilizadores');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Utilizador criado: ' . ($_POST['email'] ?? '?'));
        $this->redirect('admin/utilizadores');
    }

    public function utilizadorEdit($id)
    {
        $this->requireAdmin();
        $model = new Utilizador();
        $user = $model->find((int)$id);
        if (!$user) {
            http_response_code(404);
            echo 'Utilizador não encontrado.';
            return;
        }
        unset($user['password_hash']);
        $this->viewAdmin('administracao/utilizadores/edit', ['user' => $user], 'Editar Utilizador', 'utilizadores');
    }

    public function utilizadorUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/utilizadores');
            return;
        }
        $model = new Utilizador();
        $user = $model->find((int)$id);
        $tipo = $_POST['tipo'] ?? 'ALUNO';
        if ($tipo === 'PROFESSOR' && isset($_POST['responsavel'])) {
            $tipo = 'RESPONSAVEL';
        }
        $fotoBlob = isset($_FILES['foto_perfil']) ? $this->readImageBlob($_FILES['foto_perfil']) : null;
        $data = [
            'nome'           => $_POST['nome'] ?? '',
            'email'          => $_POST['email'] ?? '',
            'tipo'           => $tipo,
            'telefone'       => $_POST['telefone'] ?? ($user['telefone'] ?? ''),
            'linkedin'       => !empty($_POST['linkedin']) ? $_POST['linkedin'] : null,
            'turma'          => !empty($_POST['turma']) ? $_POST['turma'] : null,
            'data_nascimento' => $_POST['data_nascimento'] ?? ($user['data_nascimento'] ?? date('Y-m-d')),
        ];
        if ($fotoBlob !== null) {
            $data['foto'] = $fotoBlob;
        }
        try {
            $model->updateById((int)$id, $data);
            if (!empty($_POST['palavra_passe'])) {
                $model->updatePassword((int)$id, password_hash($_POST['palavra_passe'], PASSWORD_DEFAULT));
            }
        } catch (PDOException $e) {
            $user = $model->find((int)$id);
            unset($user['password_hash']);
            $this->viewAdmin('administracao/utilizadores/edit', [
                'user'   => $user,
                'error'  => $this->dbErrorMessage($e),
            ], 'Editar Utilizador', 'utilizadores');
            return;
        }
        $this->redirect('admin/utilizadores');
    }

    public function utilizadorView($id)
    {
        $this->requireAdmin();
        $model = new Utilizador();
        $utilizador = $model->find((int)$id);
        if (!$utilizador) {
            http_response_code(404);
            echo 'Utilizador não encontrado.';
            return;
        }
        unset($utilizador['password_hash']);
        
        $reqMatModel = new RequisicaoMaterial();
        $reqSalaModel = new RequisicaoSala();
        $reqMateriais = $reqMatModel->porUtilizador((int)$id);
        $reqSalas = $reqSalaModel->porUtilizador((int)$id);
        
        $this->viewAdmin('administracao/utilizadores/view', [
            'utilizador' => $utilizador,
            'reqMateriais' => $reqMateriais,
            'reqSalas' => $reqSalas
        ], 'Detalhes do Utilizador', 'utilizadores');
    }

    public function utilizadorDelete($id)
    {
        $this->requireAdmin();
        
        $model = new Utilizador();
        $user = $model->find((int)$id);
        
        if (!$user) {
            $this->redirect('admin/utilizadores');
            return;
        }

        $this->viewAdmin('administracao/utilizadores/delete', ['user' => $user], 'Eliminar Utilizador', 'utilizadores');
    }

    public function utilizadorDestroy($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/utilizadores');
            return;
        }

        $id = (int)$id;
        $model = new Utilizador();
        
        // Impedir que o admin se apague a si próprio
        if ($id === ($_SESSION['user']['id'] ?? 0)) {
            $user = $model->find($id);
            $this->viewAdmin('administracao/utilizadores/delete', [
                'user' => $user,
                'error' => 'Não pode eliminar a sua própria conta.',
            ], 'Eliminar Utilizador', 'utilizadores');
            return;
        }

        try {
            $db = $model->getDb();
            $db->beginTransaction();

            // 1. Eliminar requisições de exemplares associadas
            $stmt = $db->prepare("DELETE FROM requisicao_exemplar WHERE id_utilizador = ?");
            $stmt->execute([$id]);

            // 2. Eliminar requisições de salas associadas
            $stmt = $db->prepare("DELETE FROM requisicao_sala WHERE id_utilizador = ?");
            $stmt->execute([$id]);

            // 3. Eliminar o utilizador
            $model->deleteById($id);

            $db->commit();
        } catch (PDOException $e) {
            if (isset($db)) $db->rollBack();
            $user = $model->find($id);
            $this->viewAdmin('administracao/utilizadores/delete', [
                'user' => $user,
                'error' => 'Não foi possível eliminar o utilizador. Verifique se existem dependências ativas ou erro na base de dados: ' . $this->dbErrorMessage($e),
            ], 'Eliminar Utilizador', 'utilizadores');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Utilizador eliminado (id ' . (int)$id . ')');
        $this->redirect('admin/utilizadores');
    }

    // ---------- Requisições Materiais ----------
    public function requisicoesMateriais()
    {
        $this->requireAdmin();
        $reqMat = new RequisicaoMaterial();
        // As filas de trabalho (pendentes/aceites) ficam sempre completas à vista;
        // os filtros e a paginação aplicam-se ao HISTÓRICO, que é o que cresce.
        $historicoTodos = $reqMat->historico();
        $filtrados = $this->aplicarFiltros($historicoTodos, ['material_designacao', 'num_referencia'], ['estado' => 'estado_pedido', 'utilizador' => 'utilizador_nome']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/requisicoes_materiais/index', [
            'pendentes'  => $reqMat->pendentes(),
            'aceites'    => $reqMat->aceites(),
            'historico'  => $paginacao['itens'],
            'paginacao'  => $paginacao,
            'estadosReq' => $this->opcoesDe($historicoTodos, 'estado_pedido'),
            // Utilizadores que aparecem no histórico, para o dropdown "Utilizador".
            'utilizadoresReq' => $this->opcoesDe($historicoTodos, 'utilizador_nome'),
        ], 'Requisições Materiais', 'req_materiais');
    }

    public function reqMaterialView($id)
    {
        $this->requireAdmin();
        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }
        $this->viewAdmin('administracao/requisicoes_materiais/view', ['requisicao' => $req], 'Requisição Material', 'req_materiais');
    }

    public function reqMaterialAceitar($id)
    {
        $this->requireAdmin();
        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        if ($req && ($req['estado_pedido'] ?? '') === 'PENDENTE') {
            $reqMat->aceitar((int)$id);
            $this->notificarEstado($req, 'material', 'Aceite');
        }
        $this->redirect('admin/requisicoesMateriais');
    }

    public function reqMaterialRejeitar($id)
    {
        $this->requireAdmin();
        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        if ($req && ($req['estado_pedido'] ?? '') === 'PENDENTE') {
            $reqMat->rejeitar((int)$id);
            $this->notificarEstado($req, 'material', 'Recusada');
        }
        $this->redirect('admin/requisicoesMateriais');
    }

    // Marcar o material como ENTREGUE ao aluno (passa de ACEITE para EM_USO).
    public function reqMaterialEntregar($id)
    {
        $this->requireAdmin();
        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        if ($req && ($req['estado_pedido'] ?? '') === 'ACEITE') {
            $reqMat->marcarEmUso((int)$id);
            $this->notificarEstado($req, 'material', 'Em uso');
        }
        $this->redirect('admin/requisicoesMateriais');
    }

    public function reqMaterialDevolver($id)
    {
        $this->requireAdmin();
        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $estado = $_POST['estado_devolucao'] ?? 'OK';
            if (in_array($estado, ['OK', 'DANIFICADO', 'PERDIDO'], true)) {
                $reqMat->devolver((int)$id, $estado);
                $this->notificarEstado($req, 'material', 'Devolvido');
            }
            $this->redirect('admin/requisicoesMateriais');
            return;
        }
        $this->viewAdmin('administracao/requisicoes_materiais/devolver', [
            'requisicao' => $req,
        ], 'Marcar devolvido', 'req_materiais');
    }

    public function reqMaterialCreate()
    {
        $this->requireAdmin();
        $materialModel = new Material();
        $utilizadorModel = new Utilizador();
        $exemplares = $materialModel->itensDisponiveis();
        $utilizadores = $utilizadorModel->all();
        $this->viewAdmin('administracao/requisicoes_materiais/create', [
            'exemplares' => $exemplares,
            'utilizadores' => $utilizadores,
        ], 'Nova Requisição Material', 'req_materiais');
    }

    public function reqMaterialStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/requisicoesMateriais');
            return;
        }

        $idUtilizador = (int)($_POST['id_utilizador'] ?? 0);
        $idExemplar = (int)($_POST['id_exemplar'] ?? 0);
        $observacao = !empty($_POST['observacao']) ? trim($_POST['observacao']) : null;
        $dataLevantamento = $_POST['data_levantamento'] ?? null;
        $dataDevolucao = $_POST['data_devolucao'] ?? null;

        if (!$idUtilizador || !$idExemplar) {
            $materialModel = new Material();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_materiais/create', [
                'exemplares' => $materialModel->itensDisponiveis(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => 'Utilizador e exemplar são obrigatórios.',
            ], 'Nova Requisição Material', 'req_materiais');
            return;
        }

        // Converter formato datetime-local para DATETIME usando strtotime (como no user-side)
        if (!empty($dataLevantamento)) {
            $dataLevantamento = date('Y-m-d H:i:s', strtotime($dataLevantamento));
        }
        if (!empty($dataDevolucao)) {
            $dataDevolucao = date('Y-m-d H:i:s', strtotime($dataDevolucao));
        }

        $reqMat = new RequisicaoMaterial();
        try {
            $novoId = $reqMat->criar($idUtilizador, $idExemplar, $observacao, $dataLevantamento, $dataDevolucao);
            // Email de confirmação ("pedido registado") ao utilizador da requisição.
            if ($novoId) {
                $u = (new Utilizador())->find((int)$idUtilizador);
                if ($u && !empty($u['email'])) {
                    try { Mailer::sendStatusUpdate($u['email'], $u['nome'] ?? 'Utilizador', 'material', (int)$novoId, 'PENDENTE'); } catch (\Throwable $e) {}
                }
            }
            // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Requisição de material criada pelo admin');
        $this->redirect('admin/requisicoesMateriais');
        } catch (PDOException $e) {
            $materialModel = new Material();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_materiais/create', [
                'exemplares' => $materialModel->itensDisponiveis(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => $this->dbErrorMessage($e),
            ], 'Nova Requisição Material', 'req_materiais');
        }
    }

    public function reqMaterialEdit($id)
    {
        $this->requireAdmin();
        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }
        $materialModel = new Material();
        $utilizadorModel = new Utilizador();
        $exemplares = $materialModel->todosItens();
        $utilizadores = $utilizadorModel->all();
        $this->viewAdmin('administracao/requisicoes_materiais/edit', [
            'requisicao' => $req,
            'exemplares' => $exemplares,
            'utilizadores' => $utilizadores,
        ], 'Editar Requisição Material', 'req_materiais');
    }

    public function reqMaterialUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/requisicoesMateriais');
            return;
        }

        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        if (!$req) {
            $this->redirect('admin/requisicoesMateriais');
            return;
        }

        $dataLevantamento = $_POST['data_levantamento'] ?? null;
        $dataDevolucao = $_POST['data_devolucao'] ?? null;

        // Converter formato datetime-local para DATETIME usando strtotime (como no user-side)
        if (!empty($dataLevantamento)) {
            $dataLevantamento = date('Y-m-d H:i:s', strtotime($dataLevantamento));
        }
        if (!empty($dataDevolucao)) {
            $dataDevolucao = date('Y-m-d H:i:s', strtotime($dataDevolucao));
        }

        $data = [
            'observacao' => !empty($_POST['observacao']) ? trim($_POST['observacao']) : null,
            'data_levantamento' => $dataLevantamento,
            'data_devolucao' => $dataDevolucao,
            'estado_pedido' => $_POST['estado_pedido'] ?? $req['estado_pedido'],
            'estado_devolucao' => $_POST['estado_devolucao'] ?? ($req['estado_devolucao'] ?? 'OK'),
            'estado_entrega' => (int)($_POST['estado_entrega'] ?? ($req['estado_entrega'] ?? 0)),
        ];

        if (!in_array($data['estado_pedido'], ['PENDENTE', 'ACEITE', 'REJEITADO', 'EM_USO', 'CONCLUIDO'], true)) {
            $data['estado_pedido'] = $req['estado_pedido'];
        }
        if (!in_array($data['estado_devolucao'], ['OK', 'DANIFICADO', 'PERDIDO'], true)) {
            $data['estado_devolucao'] = 'OK';
        }

        try {
            $reqMat->update((int)$id, $data);
            $this->redirect('admin/reqMaterialView/' . $id);
        } catch (PDOException $e) {
            $materialModel = new Material();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_materiais/edit', [
                'requisicao' => $req,
                'exemplares' => $materialModel->todosItens(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => $this->dbErrorMessage($e),
            ], 'Editar Requisição Material', 'req_materiais');
        }
    }

    public function reqMaterialDelete($id)
    {
        $this->requireAdmin();
        
        $reqMat = new RequisicaoMaterial();
        $req = $reqMat->find((int)$id);
        
        if (!$req) {
            $this->redirect('admin/requisicoesMateriais');
            return;
        }

        $this->viewAdmin('administracao/requisicoes_materiais/delete', ['requisicao' => $req], 'Eliminar Requisição Material', 'req_materiais');
    }

    public function reqMaterialDestroy($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/requisicoesMateriais');
            return;
        }

        $reqMat = new RequisicaoMaterial();
        try {
            $reqMat->delete((int)$id);
        } catch (PDOException $e) {
            $req = $reqMat->find((int)$id);
            $this->viewAdmin('administracao/requisicoes_materiais/delete', [
                'requisicao' => $req,
                'error' => $this->dbErrorMessage($e),
            ], 'Eliminar Requisição Material', 'req_materiais');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Requisição de material eliminada (id ' . (int)$id . ')');
        $this->redirect('admin/requisicoesMateriais');
    }

    // ---------- Requisições Salas ----------
    public function requisicoesSalas()
    {
        $this->requireAdmin();
        $reqSala = new RequisicaoSala();
        // Igual às requisições de material: filtros/paginação só no histórico.
        $historicoTodos = $reqSala->historico();
        $filtrados = $this->aplicarFiltros($historicoTodos, ['sala_numero', 'bloco'], ['estado' => 'estado_sala', 'utilizador' => 'utilizador_nome']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/requisicoes_salas/index', [
            'pendentes'  => $reqSala->pendentes(),
            'aceites'    => $reqSala->aceites(),
            'historico'  => $paginacao['itens'],
            'paginacao'  => $paginacao,
            'estadosReq' => $this->opcoesDe($historicoTodos, 'estado_sala'),
            // Utilizadores que aparecem no histórico, para o dropdown "Utilizador".
            'utilizadoresReq' => $this->opcoesDe($historicoTodos, 'utilizador_nome'),
        ], 'Requisições Salas', 'req_salas');
    }

    public function reqSalaView($id)
    {
        $this->requireAdmin();
        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }
        $this->viewAdmin('administracao/requisicoes_salas/view', ['requisicao' => $req], 'Requisição Sala', 'req_salas');
    }

    public function reqSalaAceitar($id)
    {
        $this->requireAdmin();
        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        if ($req && ($req['estado_sala'] ?? '') === 'PENDENTE') {
            $reqSala->aceitar((int)$id);
            $this->notificarEstado($req, 'sala', 'Aceite');
        }
        $this->redirect('admin/requisicoesSalas');
    }

    public function reqSalaRejeitar($id)
    {
        $this->requireAdmin();
        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        if ($req && ($req['estado_sala'] ?? '') === 'PENDENTE') {
            $reqSala->rejeitar((int)$id);
            $this->notificarEstado($req, 'sala', 'Recusada');
        }
        $this->redirect('admin/requisicoesSalas');
    }

    // Marcar a sala como ENTREGUE (check-in feito): passa de ACEITE para EM_USO.
    public function reqSalaEntregar($id)
    {
        $this->requireAdmin();
        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        if ($req && ($req['estado_sala'] ?? '') === 'ACEITE') {
            $reqSala->marcarEntrega((int)$id);
            $this->notificarEstado($req, 'sala', 'Em uso');
        }
        $this->redirect('admin/requisicoesSalas');
    }

    // Marcar a sala como DEVOLVIDA ao clube: passa a CONCLUIDO e regista o estado.
    public function reqSalaDevolver($id)
    {
        $this->requireAdmin();
        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $estado = $_POST['estado_devolucao'] ?? 'NORMAL';
            if (in_array($estado, ['NORMAL', 'DESARRUMADA_SUJA', 'DANIFICADA'], true)) {
                $reqSala->finalizar((int)$id, $estado);
                $this->notificarEstado($req, 'sala', 'Devolvido');
            }
            $this->redirect('admin/requisicoesSalas');
            return;
        }
        $this->viewAdmin('administracao/requisicoes_salas/devolver', [
            'requisicao' => $req,
        ], 'Marcar devolvido', 'req_salas');
    }

    public function reqSalaCreate()
    {
        $this->requireAdmin();
        $salaModel = new Sala();
        $utilizadorModel = new Utilizador();
        $this->viewAdmin('administracao/requisicoes_salas/create', [
            'salas' => $salaModel->todas(),
            'utilizadores' => $utilizadorModel->all(),
        ], 'Nova Requisição Sala', 'req_salas');
    }

    public function reqSalaStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/requisicoesSalas');
            return;
        }

        $idUtilizador = (int)($_POST['id_utilizador'] ?? 0);
        $idSala = (int)($_POST['id_sala'] ?? 0);
        $dataInicio = $_POST['data_inicio'] ?? '';
        $dataFim = $_POST['data_fim'] ?? '';
        $observacao = !empty($_POST['observacao']) ? trim($_POST['observacao']) : null;

        if (!$idUtilizador || !$idSala || $dataInicio === '' || $dataFim === '') {
            $salaModel = new Sala();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_salas/create', [
                'salas' => $salaModel->todas(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => 'Utilizador, sala e datas são obrigatórios.',
            ], 'Nova Requisição Sala', 'req_salas');
            return;
        }

        // Converter formato datetime-local para DATETIME usando strtotime (como no user-side)
        if ($dataInicio) {
            $dataInicio = date('Y-m-d H:i:s', strtotime($dataInicio));
        }
        if ($dataFim) {
            $dataFim = date('Y-m-d H:i:s', strtotime($dataFim));
        }

        $reqSala = new RequisicaoSala();
        
        // Verificar conflitos
        if ($reqSala->verificarConflito($idSala, $dataInicio, $dataFim)) {
            $salaModel = new Sala();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_salas/create', [
                'salas' => $salaModel->todas(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => 'Já existe uma requisição aceite para esta sala no período indicado.',
            ], 'Nova Requisição Sala', 'req_salas');
            return;
        }

        try {
            $novoId = $reqSala->criar($idUtilizador, $idSala, $dataInicio, $dataFim, $observacao);
            // Email de confirmação ("pedido registado") ao utilizador da requisição.
            if ($novoId) {
                $u = (new Utilizador())->find((int)$idUtilizador);
                if ($u && !empty($u['email'])) {
                    try { Mailer::sendStatusUpdate($u['email'], $u['nome'] ?? 'Utilizador', 'sala', (int)$novoId, 'PENDENTE'); } catch (\Throwable $e) {}
                }
            }
            // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Requisição de sala criada pelo admin');
        $this->redirect('admin/requisicoesSalas');
        } catch (PDOException $e) {
            $salaModel = new Sala();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_salas/create', [
                'salas' => $salaModel->todas(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => $this->dbErrorMessage($e),
            ], 'Nova Requisição Sala', 'req_salas');
        }
    }

    public function reqSalaEdit($id)
    {
        $this->requireAdmin();
        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        if (!$req) {
            http_response_code(404);
            echo 'Requisição não encontrada.';
            return;
        }
        $salaModel = new Sala();
        $utilizadorModel = new Utilizador();
        $this->viewAdmin('administracao/requisicoes_salas/edit', [
            'requisicao' => $req,
            'salas' => $salaModel->todas(),
            'utilizadores' => $utilizadorModel->all(),
        ], 'Editar Requisição Sala', 'req_salas');
    }

    public function reqSalaUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/requisicoesSalas');
            return;
        }

        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        if (!$req) {
            $this->redirect('admin/requisicoesSalas');
            return;
        }

        $idSala = (int)($_POST['id_sala'] ?? $req['id_sala']);
        $dataInicio = $_POST['data_inicio'] ?? '';
        $dataFim = $_POST['data_fim'] ?? '';

        if ($dataInicio === '' || $dataFim === '') {
            $salaModel = new Sala();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_salas/edit', [
                'requisicao' => $req,
                'salas' => $salaModel->todas(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => 'Datas são obrigatórias.',
            ], 'Editar Requisição Sala', 'req_salas');
            return;
        }

        // Converter formato datetime-local para DATETIME usando strtotime (como no user-side)
        if ($dataInicio) {
            $dataInicio = date('Y-m-d H:i:s', strtotime($dataInicio));
        }
        if ($dataFim) {
            $dataFim = date('Y-m-d H:i:s', strtotime($dataFim));
        }

        if ($reqSala->verificarConflito($idSala, $dataInicio, $dataFim, (int)$id)) {
            $salaModel = new Sala();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_salas/edit', [
                'requisicao' => $req,
                'salas' => $salaModel->todas(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => 'Já existe uma requisição aceite para esta sala no período indicado.',
            ], 'Editar Requisição Sala', 'req_salas');
            return;
        }

        try {
            $reqSala->update((int)$id, [
                'id_utilizador' => (int)($_POST['id_utilizador'] ?? $req['id_utilizador']),
                'id_sala' => $idSala,
                'data_inicio' => $dataInicio,
                'data_fim' => $dataFim,
                'estado_sala' => $_POST['estado_sala'] ?? $req['estado_sala'],
                'estado_devolucao' => $_POST['estado_devolucao'] ?? ($req['estado_devolucao'] ?? 'NORMAL'),
                'estado_entrega' => (int)($_POST['estado_entrega'] ?? ($req['estado_entrega'] ?? 0)),
                'observacao' => !empty($_POST['observacao']) ? trim($_POST['observacao']) : null,
            ]);
            $this->redirect('admin/reqSalaView/' . $id);
        } catch (PDOException $e) {
            $salaModel = new Sala();
            $utilizadorModel = new Utilizador();
            $this->viewAdmin('administracao/requisicoes_salas/edit', [
                'requisicao' => $req,
                'salas' => $salaModel->todas(),
                'utilizadores' => $utilizadorModel->all(),
                'error' => $this->dbErrorMessage($e),
            ], 'Editar Requisição Sala', 'req_salas');
        }
    }

    public function reqSalaDelete($id)
    {
        $this->requireAdmin();
        
        $reqSala = new RequisicaoSala();
        $req = $reqSala->find((int)$id);
        
        if (!$req) {
            $this->redirect('admin/requisicoesSalas');
            return;
        }

        $this->viewAdmin('administracao/requisicoes_salas/delete', ['requisicao' => $req], 'Eliminar Requisição Sala', 'req_salas');
    }

    public function reqSalaDestroy($id)
    {
        $this->requireAdmin();
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/requisicoesSalas');
            return;
        }

        $reqSala = new RequisicaoSala();
        try {
            $reqSala->delete((int)$id);
        } catch (PDOException $e) {
            $req = $reqSala->find((int)$id);
            $this->viewAdmin('administracao/requisicoes_salas/delete', [
                'requisicao' => $req,
                'error' => $this->dbErrorMessage($e),
            ], 'Eliminar Requisição Sala', 'req_salas');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Requisição de sala eliminada (id ' . (int)$id . ')');
        $this->redirect('admin/requisicoesSalas');
    }

    // ---------- Calendário (só admin) ----------
    public function calendario()
    {
        $this->requireAdmin();
        $this->viewAdmin('administracao/calendario/index', [], 'Calendário', 'calendario');
    }

    // ---------- Eventos (Portfólio) ----------
    public function eventos()
    {
        $this->requireAdmin();
        $model = new PortfolioEvento();
        $todos = $model->todos();

        // Pesquisa pelo título do evento + páginas de 8.
        $filtrados = $this->aplicarFiltros($todos, ['titulo']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/eventos/index', [
            'eventos'   => $paginacao['itens'],
            'paginacao' => $paginacao,
        ], 'Eventos', 'eventos');
    }

    // ---------- CRUD Eventos ----------
    public function eventoCreate()
    {
        $this->requireAdmin();
        $this->viewAdmin('administracao/eventos/create', ['evento' => null], 'Novo Evento', 'eventos');
    }

    public function eventoStore()
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/eventos');
            return;
        }
        $imagemBlob = isset($_FILES['imagem_url']) ? $this->readImageBlob($_FILES['imagem_url']) : null;
        $data = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'url' => !empty($_POST['url']) ? trim($_POST['url']) : null,
            'ordem' => (int)($_POST['ordem'] ?? 0),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'imagem_url' => $imagemBlob,
        ];
        try {
            $model = new PortfolioEvento();
            $model->create($data);
        } catch (PDOException $e) {
            $this->viewAdmin('administracao/eventos/create', [
                'evento' => (object)$data,
                'error' => $this->dbErrorMessage($e),
            ], 'Novo Evento', 'eventos');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('CRIACAO', 'Evento criado: ' . ($_POST['titulo'] ?? '?'));
        $this->redirect('admin/eventos');
    }

    public function eventoEdit($id)
    {
        $this->requireAdmin();
        $model = new PortfolioEvento();
        $evento = $model->find((int)$id);
        if (!$evento) {
            http_response_code(404);
            echo 'Evento não encontrado.';
            return;
        }
        $evento['imagem_src'] = $model->getImagemSrc($evento['imagem_url'] ?? null);
        $this->viewAdmin('administracao/eventos/edit', ['evento' => $evento], 'Editar Evento', 'eventos');
    }

    public function eventoUpdate($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/eventos');
            return;
        }
        $model = new PortfolioEvento();
        $evento = $model->find((int)$id);
        if (!$evento) {
            $this->redirect('admin/eventos');
            return;
        }
        $imagemBlob = isset($_FILES['imagem_url']) && $_FILES['imagem_url']['size'] > 0 ? $this->readImageBlob($_FILES['imagem_url']) : null;
        $imagemFinal = $imagemBlob ?? $evento['imagem_url'] ?? null;
        $data = [
            'titulo' => trim($_POST['titulo'] ?? ''),
            'descricao' => trim($_POST['descricao'] ?? ''),
            'url' => !empty($_POST['url']) ? trim($_POST['url']) : null,
            'ordem' => (int)($_POST['ordem'] ?? 0),
            'ativo' => isset($_POST['ativo']) ? 1 : 0,
            'imagem_url' => $imagemFinal,
        ];
        try {
            $model->updateById((int)$id, $data);
        } catch (PDOException $e) {
            $evento['imagem_src'] = $model->getImagemSrc($evento['imagem_url'] ?? null);
            $this->viewAdmin('administracao/eventos/edit', [
                'evento' => array_merge($evento, $data),
                'error' => $this->dbErrorMessage($e),
            ], 'Editar Evento', 'eventos');
            return;
        }
        $this->redirect('admin/eventos');
    }

    public function eventoView($id)
    {
        $this->requireAdmin();
        $model = new PortfolioEvento();
        $evento = $model->find((int)$id);
        if (!$evento) {
            http_response_code(404);
            echo 'Evento não encontrado.';
            return;
        }
        $evento['imagem_src'] = $model->getImagemSrc($evento['imagem_url'] ?? null);
        $this->viewAdmin('administracao/eventos/view', ['evento' => $evento], 'Detalhes do Evento', 'eventos');
    }

    public function eventoDelete($id)
    {
        $this->requireAdmin();
        $model = new PortfolioEvento();
        $evento = $model->find((int)$id);
        if (!$evento) {
            http_response_code(404);
            echo 'Evento não encontrado.';
            return;
        }
        $this->viewAdmin('administracao/eventos/delete', ['evento' => $evento], 'Eliminar Evento', 'eventos');
    }

    public function eventoDestroy($id)
    {
        $this->requireAdmin();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('admin/eventos');
            return;
        }
        try {
            $model = new PortfolioEvento();
            $model->deleteById((int)$id);
        } catch (PDOException $e) {
            $model = new PortfolioEvento();
            $evento = $model->find((int)$id);
            $this->viewAdmin('administracao/eventos/delete', [
                'evento' => $evento,
                'error' => $this->dbErrorMessage($e),
            ], 'Eliminar Evento', 'eventos');
            return;
        }
        // Auditoria: fica registado quem fez esta operação e quando.
        $this->registarAcao('ELIMINACAO', 'Evento eliminado (id ' . (int)$id . ')');
        $this->redirect('admin/eventos');
    }

    // Ficheiro do teu Controlador PHP (ex: ReqMateriaisController.php)

public function apiHorariosOcupados() {
        header('Content-Type: application/json');

        $id_exemplar = isset($_GET['id_exemplar']) ? (int)$_GET['id_exemplar'] : 0;
        $data = isset($_GET['data']) ? $_GET['data'] : ''; 

        if (!$id_exemplar || !$data) {
            echo json_encode(['ocupados' => []]);
            exit;
        }

        $reqMat = new RequisicaoMaterial();
        $db = $reqMat->getDb();
        
        $stmt = $db->prepare("
            SELECT data_levantamento 
            FROM requisicao_exemplar 
            WHERE id_exemplar = ? 
              AND DATE(data_levantamento) = ? 
              AND estado_pedido IN ('PENDENTE', 'ACEITE')
        ");
        $stmt->execute([$id_exemplar, $data]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $ocupados = [];
        foreach ($resultados as $row) {
            $hora = date('H:i', strtotime($row['data_levantamento']));
            $ocupados[] = $hora;
        }

        echo json_encode(['ocupados' => $ocupados]);
        exit;
    }

    // ---------- LOGS ----------

    // Aba de logs gerais: entradas no sistema (login/logout), registos e auditoria.
    public function logs()
    {
        $this->requireAdmin();
        $logModel = new Log();
        // Vou buscar até 2000 registos (em vez de 300) porque agora há filtros
        // e paginação — a pessoa consegue navegar sem a página ficar pesada.
        $todos = $logModel->acessos(2000);

        // Pesquisa pela descrição/nome/email + dropdown do tipo de log, páginas de 8.
        $filtrados = $this->aplicarFiltros($todos, ['descricao', 'utilizador_nome', 'utilizador_email'], ['tipo' => 'tipo']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/logs/index', [
            'acessos'    => $paginacao['itens'],
            'paginacao'  => $paginacao,
            'tiposLog'   => $this->opcoesDe($todos, 'tipo'),
            'totalErros' => $logModel->contarErros(),
        ], 'Logs & Auditoria', 'logs');
    }

    // Aba de logs de erros (erros vindos da base de dados / procedures / triggers).
    public function logsErros()
    {
        $this->requireAdmin();
        $logModel = new Log();
        $todos = $logModel->erros(2000);

        // Pesquisa pela mensagem/origem + dropdown da origem do erro, páginas de 8.
        $filtrados = $this->aplicarFiltros($todos, ['mensagem', 'origem', 'utilizador_nome'], ['origem' => 'origem']);
        $paginacao = $this->paginar($filtrados, 8);

        $this->viewAdmin('administracao/logs/erros', [
            'erros'         => $paginacao['itens'],
            'paginacao'     => $paginacao,
            'origensErro'   => $this->opcoesDe($todos, 'origem'),
            'totalAcessos'  => $logModel->contarAcessos(),
        ], 'Logs de Erros', 'logs_erros');
    }



    // -----------------------------------------------------------------
    //  IMAGENS DE EXEMPLO (fotografias reais, relacionadas com cada registo)
    // -----------------------------------------------------------------
    // Percorre eventos, materiais, categorias e salas SEM imagem e vai buscar
    // uma fotografia real à internet QUE TENHA A VER com o nome do registo:
    // um material "Arduino Uno" recebe uma foto de Arduino, uma sala
    // "Laboratório" recebe um laboratório, e assim por diante.
    // Correr uma vez (como admin) em: /admin/seedImagens
    // Para trocar por fotos verdadeiras depois, basta editar o registo.
    public function seedImagens()
    {
        $this->requireAdmin();
        @set_time_limit(180); // podem ser bastantes downloads

        $db = (new Material())->getDb();

        // Dicionário: se o nome do registo contém a palavra da esquerda,
        // procuro fotos com o termo (em inglês) da direita.
        $dicionario = [
            'arduino'     => 'arduino',
            'esp32'       => 'microcontroller',
            'esp8266'     => 'microcontroller',
            'raspberry'   => 'raspberrypi',
            'micro:bit'   => 'microcontroller',
            'sensor'      => 'sensor',
            'motor'       => 'electricmotor',
            'servo'       => 'servomotor',
            'led'         => 'led',
            'bateria'     => 'battery',
            'pilha'       => 'battery',
            'cabo'        => 'cables',
            'fio'         => 'wires',
            'camara'      => 'camera',
            'câmara'      => 'camera',
            'camera'      => 'camera',
            'impressora'  => '3dprinter',
            '3d'          => '3dprinter',
            'lego'        => 'lego',
            'drone'       => 'drone',
            'robo'        => 'robot',
            'robô'        => 'robot',
            'robot'       => 'robot',
            'kit'         => 'electronickit',
            'breadboard'  => 'breadboard',
            'protoboard'  => 'breadboard',
            'resist'      => 'resistor',
            'ferramenta'  => 'tools',
            'chave'       => 'tools',
            'solda'       => 'soldering',
            'ecra'        => 'display',
            'ecrã'        => 'display',
            'monitor'     => 'monitor',
            'portatil'    => 'laptop',
            'portátil'    => 'laptop',
            'computador'  => 'computer',
            'rato'        => 'computermouse',
            'teclado'     => 'keyboard',
            // eventos
            'competi'     => 'robotcompetition',
            'torneio'     => 'robotcompetition',
            'feira'       => 'scienceexhibition',
            'workshop'    => 'workshop',
            'formacao'    => 'classroomtraining',
            'formação'    => 'classroomtraining',
            'apresenta'   => 'presentation',
            'visita'      => 'fieldtrip',
            // salas
            'laborat'     => 'laboratory',
            'informatica' => 'computerlab',
            'informática' => 'computerlab',
            'redes'       => 'serverroom',
            'servidor'    => 'serverroom',
            'auditor'     => 'auditorium',
            'armaz'       => 'warehouse',
            'arrecada'    => 'warehouse',
            'estudio'     => 'recordingstudio',
            'estúdio'     => 'recordingstudio',
            'gravacao'    => 'recordingstudio',
            'gravação'    => 'recordingstudio',
            'multimedia'  => 'recordingstudio',
            'reuni'       => 'meetingroom',
            'oficina'     => 'mechanicworkshop',
            'mecanica'    => 'mechanicworkshop',
            'mecânica'    => 'mechanicworkshop',
            'biblioteca'  => 'library',
        ];

        // Descobre o melhor termo de pesquisa para um registo.
        $termoPara = function (string $nome, string $temaPorDefeito) use ($dicionario): string {
            $n = mb_strtolower($nome);
            foreach ($dicionario as $palavra => $termo) {
                if (mb_strpos($n, $palavra) !== false) {
                    return $termo;
                }
            }
            // Sem correspondência no dicionário: tento a 1.ª palavra "útil" do
            // nome (sem acentos); se não der, uso o tema por defeito da tabela.
            $ascii = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $nome) ?: '';
            if (preg_match('/[a-zA-Z]{4,}/', $ascii, $m)) {
                return strtolower($m[0]) . ',' . $temaPorDefeito;
            }
            return $temaPorDefeito;
        };

        // Descarrega uma foto (LoremFlickr devolve fotos reais do Flickr por tema).
        $descarregar = function (string $termo, int $larg, int $alt, int $semente): ?string {
            $url = "https://loremflickr.com/{$larg}/{$alt}/" . rawurlencode($termo) . "?random={$semente}";
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT        => 15,
                CURLOPT_USERAGENT      => 'RoboticaXL-Seeder',
            ]);
            $dados = curl_exec($ch);
            $codigo = (int)curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if ($dados === false || $codigo !== 200 || strlen($dados) < 1000) {
                return null;
            }
            return @imagecreatefromstring($dados) ? $dados : null; // confirmo que é imagem
        };

        // Tabela, coluna da imagem, coluna do nome, tema por defeito, tamanho.
        $alvos = [
            ['evento',    'imagem_url', 'titulo',     'robotics',    800, 450],
            ['material',  'imagem',     'designacao', 'electronics', 640, 480],
            ['categoria', 'imagem',     'categoria',  'technology',  480, 480],
            ['sala',      'imagem',     'descricao',  'classroom',   800, 450],
        ];

        $resumo = [];
        foreach ($alvos as [$tabela, $colImg, $colNome, $tema, $larg, $alt]) {
            $feitos = 0;
            $rows = $db->query("SELECT id, COALESCE(`$colNome`, '') AS nome FROM `$tabela` WHERE `$colImg` IS NULL OR LENGTH(`$colImg`) = 0")->fetchAll(PDO::FETCH_ASSOC);
            foreach ($rows as $r) {
                $termo = $termoPara((string)$r['nome'], $tema);
                $foto  = $descarregar($termo, $larg, $alt, random_int(1, 999999));
                if ($foto === null) {
                    // 2.ª tentativa só com o tema por defeito (termo raro sem fotos).
                    $foto = $descarregar($tema, $larg, $alt, random_int(1, 999999));
                }
                if ($foto === null) {
                    continue;
                }
                $stmt = $db->prepare("UPDATE `$tabela` SET `$colImg` = ? WHERE id = ?");
                $stmt->bindValue(1, $foto, PDO::PARAM_LOB);
                $stmt->bindValue(2, (int)$r['id'], PDO::PARAM_INT);
                $stmt->execute();
                $feitos++;
            }
            $resumo[] = $tabela . ': ' . $feitos . ' de ' . count($rows);
        }

        $this->setFlash('success', 'Imagens de exemplo aplicadas — ' . implode(' | ', $resumo));
        $this->redirect('admin/index');
    }

}
