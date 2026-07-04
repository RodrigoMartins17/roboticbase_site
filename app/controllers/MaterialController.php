<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Material.php';
require_once __DIR__ . '/../models/Categoria.php';

// Este controller trata dos MATERIAIS do clube.
//
// IMPORTANTE: só o método index() é que está mesmo a ser usado no site (é a
// página onde os alunos veem o inventário). Os métodos de gestão que vêm a
// seguir (create, store, edit, update, delete, itens, etc.) foram a primeira
// versão do CRUD, mas hoje em dia essa gestão faz-se toda pelo painel de
// administração. Ficam aqui porque ainda não os apaguei, mas apontam para
// views ('materiais/form', 'materiais/itens'...) que já não existem.
class MaterialController extends Controller
{
    // Página do inventário de materiais que os alunos veem.
    public function index()
    {
        Auth::requireLogin();
        $model = new Material();
        // Vou buscar todos os modelos de material (ex: "Arduino Uno") já com a imagem.
        $modelos = $model->attachImagemSrc($model->todosModelos());
        // E os exemplares que estão disponíveis (as unidades físicas de cada material).
        $itensDisponiveis = $model->itensDisponiveis();

        // Organizo os exemplares por material, para depois na página saber
        // quantas unidades disponíveis tem cada material.
        $itensPorModelo = [];
        foreach ($itensDisponiveis as $item) {
            $modeloId = (int)$item['id_material'];
            if (!isset($itensPorModelo[$modeloId])) {
                $itensPorModelo[$modeloId] = [];
            }
            $itensPorModelo[$modeloId][] = $item;
        }

        $this->view('materiais/index', [
            'modelos' => $modelos,
            'itensPorModelo' => $itensPorModelo,
            'pageTitle' => 'Materiais'
        ]);
    }

    // --- A partir daqui é a antiga gestão de materiais (hoje feita na administração). ---
    // Não está a ser usada no site; deixo os métodos como estavam.

    public function create()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $catModel = new Categoria();
        $categorias = $catModel->all();
        $this->view('materiais/form', ['categorias' => $categorias, 'material' => null]);
    }

    public function store()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('material/index');
        }

        $model = new Material();
        $catModel = new Categoria();
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;

        $designacao = $this->normalizeText($_POST['designacao'] ?? '', 100);
        $descricao = $this->normalizeText($_POST['descricao'] ?? '', 2000);
        $numItens = (int)($_POST['num_itens'] ?? 1);
        $baseReferencia = $this->normalizeText($_POST['num_referencia'] ?? '', 80);

        $categoriaIds = [];
        if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
            $categoriaIds = array_values(array_unique(array_filter(array_map('intval', $_POST['categorias']), fn($v) => $v > 0)));
        } elseif (!empty($_POST['categoria_id']) && $this->isPositiveInt($_POST['categoria_id'])) {
            $categoriaIds = [(int)$_POST['categoria_id']];
        }

        if (!$this->isNonEmptyString($designacao, 2)) {
            $this->view('materiais/form', [
                'categorias' => $catModel->all(),
                'material' => null,
                'error' => 'A designacao do material deve ter pelo menos 2 caracteres.'
            ]);
            return;
        }

        if ($numItens < 1 || $numItens > 100) {
            $this->view('materiais/form', [
                'categorias' => $catModel->all(),
                'material' => null,
                'error' => 'Quantidade inicial de exemplares invalida (1-100).'
            ]);
            return;
        }

        if (empty($categoriaIds)) {
            $this->view('materiais/form', [
                'categorias' => $catModel->all(),
                'material' => null,
                'error' => 'Selecione pelo menos uma categoria.'
            ]);
            return;
        }

        if ($baseReferencia === '') {
            $baseReferencia = 'REF-' . date('Ymd-His');
        }

        try {
            $model->createModelo([
                'designacao' => $designacao,
                'descricao' => $descricao,
                'imagem' => $imagemBlob,
            ]);

            $modeloId = (int)$model->getDb()->lastInsertId();
            foreach ($categoriaIds as $categoriaId) {
                $model->adicionarCategoria($modeloId, $categoriaId);
            }

            for ($i = 0; $i < $numItens; $i++) {
                $numReferencia = $baseReferencia . ($i > 0 ? '-' . ($i + 1) : '');
                $model->createItem([
                    'num_referencia' => $numReferencia,
                    'id_material' => $modeloId,
                    'estado' => 'DISPONIVEL',
                    'observacao' => null,
                ]);
            }
        } catch (PDOException $e) {
            if ($this->hasUploadedFile($_FILES['imagem'] ?? null) && $imagemBlob === null) {
                $this->view('materiais/form', [
                    'categorias' => $catModel->all(),
                    'material' => null,
                    'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.'
                ]);
                return;
            }
            $this->view('materiais/form', [
                'categorias' => $catModel->all(),
                'material' => null,
                'error' => $this->dbErrorMessage($e)
            ]);
            return;
        }

        $this->redirect('material/index');
    }

    public function edit($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Material();
        $catModel = new Categoria();
        $material = $model->modeloComCategorias((int)$id);
        if ($material) {
            $material['imagem_src'] = $model->getImagemSrc($material['imagem'] ?? null);
        }
        $categorias = $catModel->all();
        $itens = $model->itensPorModelo((int)$id);

        $this->view('materiais/form', [
            'material' => $material,
            'categorias' => $categorias,
            'itens' => $itens
        ]);
    }

    public function update($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('material/index');
        }

        $model = new Material();
        $catModel = new Categoria();
        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $current = $model->findModelo((int)$id);
        $imagemFinal = $imagemBlob ?? ($current['imagem'] ?? null);

        $designacao = $this->normalizeText($_POST['designacao'] ?? '', 100);
        $descricao = $this->normalizeText($_POST['descricao'] ?? '', 2000);

        $categoriaIds = [];
        if (!empty($_POST['categorias']) && is_array($_POST['categorias'])) {
            $categoriaIds = array_values(array_unique(array_filter(array_map('intval', $_POST['categorias']), fn($v) => $v > 0)));
        } elseif (!empty($_POST['categoria_id']) && $this->isPositiveInt($_POST['categoria_id'])) {
            $categoriaIds = [(int)$_POST['categoria_id']];
        }

        if (!$this->isNonEmptyString($designacao, 2)) {
            $material = $model->modeloComCategorias((int)$id);
            if ($material) {
                $material['imagem_src'] = $model->getImagemSrc($material['imagem'] ?? null);
            }
            $this->view('materiais/form', [
                'material' => $material,
                'categorias' => $catModel->all(),
                'itens' => $model->itensPorModelo((int)$id),
                'error' => 'A designacao do material deve ter pelo menos 2 caracteres.'
            ]);
            return;
        }

        try {
            $model->updateModelo((int)$id, [
                'designacao' => $designacao,
                'descricao' => $descricao,
                'imagem' => $imagemFinal,
            ]);
            if (!empty($categoriaIds)) {
                $model->atualizarCategorias((int)$id, $categoriaIds);
            }
        } catch (PDOException $e) {
            if ($this->hasUploadedFile($_FILES['imagem'] ?? null) && $imagemBlob === null) {
                $material = $model->modeloComCategorias((int)$id);
                if ($material) {
                    $material['imagem_src'] = $model->getImagemSrc($material['imagem'] ?? null);
                }
                $this->view('materiais/form', [
                    'material' => $material,
                    'categorias' => $catModel->all(),
                    'itens' => $model->itensPorModelo((int)$id),
                    'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.'
                ]);
                return;
            }
            $material = $model->modeloComCategorias((int)$id);
            if ($material) {
                $material['imagem_src'] = $model->getImagemSrc($material['imagem'] ?? null);
            }
            $this->view('materiais/form', [
                'material' => $material,
                'categorias' => $catModel->all(),
                'itens' => $model->itensPorModelo((int)$id),
                'error' => $this->dbErrorMessage($e)
            ]);
            return;
        }

        $this->redirect('material/index');
    }

    public function delete($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Material();
        $model->deleteModelo((int)$id);
        $this->redirect('material/index');
    }

    public function itens($modeloId)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Material();
        $modelo = $model->findModelo((int)$modeloId);
        $itens = $model->itensPorModelo((int)$modeloId);

        $this->view('materiais/itens', [
            'modelo' => $modelo,
            'itens' => $itens
        ]);
    }

    public function criarItem($modeloId)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Material();
        $modelo = $model->findModelo((int)$modeloId);
        if (!$modelo) {
            http_response_code(404);
            echo 'Modelo nao encontrado.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numReferencia = $this->normalizeText($_POST['num_referencia'] ?? '', 80);
            if ($numReferencia === '') {
                $numReferencia = 'REF-' . uniqid();
            }
            $estado = $_POST['estado'] ?? 'DISPONIVEL';
            if (!$this->isValidEnumValue($estado, ['DISPONIVEL', 'EMPRESTADO', 'DANIFICADO', 'PERDIDO', 'MANUTENCAO'])) {
                $this->view('materiais/form_item', [
                    'modelo' => $modelo,
                    'item' => null,
                    'error' => 'Estado do exemplar invalido.'
                ]);
                return;
            }

            $data = [
                'num_referencia' => $numReferencia,
                'id_material' => (int)$modeloId,
                'estado' => $estado,
                'observacao' => $this->normalizeText($_POST['observacao'] ?? '', 2000) ?: null,
            ];
            try {
                $model->createItem($data);
            } catch (PDOException $e) {
                $this->view('materiais/form_item', [
                    'modelo' => $modelo,
                    'item' => null,
                    'error' => $this->dbErrorMessage($e)
                ]);
                return;
            }
            $this->redirect('material/itens/' . (int)$modeloId);
        }

        $this->view('materiais/form_item', ['modelo' => $modelo, 'item' => null]);
    }

    public function atualizarItem($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Material();
        $item = $model->findItem((int)$id);
        if (!$item) {
            http_response_code(404);
            echo 'Item nao encontrado.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $numReferencia = $this->normalizeText($_POST['num_referencia'] ?? '', 80);
            if ($numReferencia === '') {
                $numReferencia = $item['num_referencia'] ?? 'REF-' . uniqid();
            }
            $estado = $_POST['estado'] ?? 'DISPONIVEL';
            if (!$this->isValidEnumValue($estado, ['DISPONIVEL', 'EMPRESTADO', 'DANIFICADO', 'PERDIDO', 'MANUTENCAO'])) {
                $modelo = $model->findModelo((int)$item['id_material']);
                $this->view('materiais/form_item', [
                    'modelo' => $modelo,
                    'item' => $item,
                    'error' => 'Estado do exemplar invalido.'
                ]);
                return;
            }

            $data = [
                'num_referencia' => $numReferencia,
                'id_material' => $item['id_material'],
                'estado' => $estado,
                'observacao' => $this->normalizeText($_POST['observacao'] ?? '', 2000) ?: null,
            ];
            try {
                $model->updateItem((int)$id, $data);
            } catch (PDOException $e) {
                $modelo = $model->findModelo((int)$item['id_material']);
                $this->view('materiais/form_item', [
                    'modelo' => $modelo,
                    'item' => $item,
                    'error' => $this->dbErrorMessage($e)
                ]);
                return;
            }
            $this->redirect('material/itens/' . $item['id_material']);
            return;
        }

        $modelo = $model->findModelo((int)$item['id_material']);
        $this->view('materiais/form_item', ['modelo' => $modelo, 'item' => $item]);
    }

    public function eliminarItem($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Material();
        $item = $model->findItem((int)$id);
        if (!$item) {
            $this->redirect('material/index');
        }

        $modeloId = (int)$item['id_material'];
        $model->deleteItem((int)$id);
        $this->redirect('material/itens/' . $modeloId);
    }
}

