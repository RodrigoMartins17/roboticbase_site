<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Categoria.php';
require_once __DIR__ . '/../models/PortfolioEvento.php';

class CategoriaController extends Controller
{
    private function getImagemSrc(?string $imagem): ?string
    {
        return (new PortfolioEvento())->getImagemSrc($imagem);
    }

    public function index()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        $model = new Categoria();
        $categorias = $model->all();
        $this->viewAdmin('administracao/categorias/index', ['categorias' => $categorias], 'Categorias', 'categorias');
    }

    public function categoriaView($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        $model = new Categoria();
        $categoria = $model->find((int)$id);
        if (!$categoria) {
            http_response_code(404);
            echo "Categoria não encontrada.";
            return;
        }
        $categoria['imagem_src'] = $this->getImagemSrc($categoria['imagem'] ?? null);
        $materiais = method_exists($model, 'materiais') ? $model->materiais((int)$id) : [];
        $this->viewAdmin('administracao/categorias/view', [
            'categoria' => $categoria,
            'materiais' => $materiais
        ], 'Detalhes da Categoria', 'categorias');
    }

    public function create()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        $this->viewAdmin('administracao/categorias/create', ['categoria' => null], 'Nova Categoria', 'categorias');
    }

    public function store()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $this->normalizeText($_POST['categoria'] ?? '', 255);
            $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
            if (!$this->isNonEmptyString($nome, 2)) {
                $this->viewAdmin('administracao/categorias/create', [
                    'categoria' => null,
                    'error' => 'Nome da categoria invalido (minimo 2 caracteres).'
                ], 'Nova Categoria', 'categorias');
                return;
            }
            try {
                $model = new Categoria();
                $model->create($nome, $imagemBlob);
            } catch (PDOException $e) {
                if ($this->hasUploadedFile($_FILES['imagem'] ?? null) && $imagemBlob === null) {
                    $this->viewAdmin('administracao/categorias/create', [
                        'categoria' => null,
                        'error' => 'Imagem inválida. Use JPG, PNG, GIF ou WebP.'
                    ], 'Nova Categoria', 'categorias');
                    return;
                }
                $this->viewAdmin('administracao/categorias/create', [
                    'categoria' => null,
                    'error' => $this->dbErrorMessage($e)
                ], 'Nova Categoria', 'categorias');
                return;
            }
        }

        $this->redirect('categoria/index');
    }

    public function edit($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        $model = new Categoria();
        $categoria = $model->find((int)$id);
        if (!$categoria) {
            http_response_code(404);
            echo "Categoria não encontrada.";
            return;
        }
        $categoria['imagem_src'] = $this->getImagemSrc($categoria['imagem'] ?? null);
        $this->viewAdmin('administracao/categorias/edit', ['categoria' => $categoria], 'Editar Categoria', 'categorias');
    }

    public function update($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $nome = $this->normalizeText($_POST['categoria'] ?? '', 255);
            $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
            if (!$this->isNonEmptyString($nome, 2)) {
                $model = new Categoria();
                $categoria = $model->find((int)$id);
                if ($categoria) {
                    $categoria['imagem_src'] = $this->getImagemSrc($categoria['imagem'] ?? null);
                }
                $this->viewAdmin('administracao/categorias/edit', [
                    'categoria' => $categoria,
                    'error' => 'Nome da categoria invalido (minimo 2 caracteres).'
                ], 'Editar Categoria', 'categorias');
                return;
            }
            try {
                $model = new Categoria();
                $current = $model->find((int)$id);
                $imagemFinal = $imagemBlob ?? ($current['imagem'] ?? null);
                $model->update((int)$id, $nome, $imagemFinal);
            } catch (PDOException $e) {
                if ($this->hasUploadedFile($_FILES['imagem'] ?? null) && $imagemBlob === null) {
                    $model = new Categoria();
                    $categoria = $model->find((int)$id);
                    if ($categoria) {
                        $categoria['imagem_src'] = $this->getImagemSrc($categoria['imagem'] ?? null);
                    }
                    $this->viewAdmin('administracao/categorias/edit', [
                        'categoria' => $categoria,
                        'error' => 'Imagem inválida. Use JPG, PNG, GIF ou WebP.'
                    ], 'Editar Categoria', 'categorias');
                    return;
                }
                $model = new Categoria();
                $categoria = $model->find((int)$id);
                if ($categoria) {
                    $categoria['imagem_src'] = $this->getImagemSrc($categoria['imagem'] ?? null);
                }
                $this->viewAdmin('administracao/categorias/edit', [
                    'categoria' => $categoria,
                    'error' => $this->dbErrorMessage($e)
                ], 'Editar Categoria', 'categorias');
                return;
            }
        }

        $this->redirect('categoria/index');
    }

    public function delete($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        $model = new Categoria();
        $model->delete((int)$id);
        $this->redirect('categoria/index');
    }

}

