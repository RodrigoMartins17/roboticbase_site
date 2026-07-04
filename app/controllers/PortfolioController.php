<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/PortfolioEvento.php';

class PortfolioController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $model = new PortfolioEvento();
        $eventos = $model->todos();
        $this->view('portfolio/index', ['eventos' => $eventos]);
    }

    public function show($id)
    {
        Auth::requireLogin();
        $model = new PortfolioEvento();
        $evento = $model->find((int)$id);

        if (!$evento) {
            http_response_code(404);
            echo 'Evento nao encontrado.';
            return;
        }

        $this->view('portfolio/view', ['evento' => $evento]);
    }

    public function create()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $this->view('portfolio/form', ['evento' => null]);
    }

    public function store()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('portfolio/index');
        }

        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $titulo = $this->normalizeText($_POST['titulo'] ?? '', 255);
        $descricao = $this->normalizeText($_POST['descricao'] ?? '', 4000) ?: null;
        $url = $this->normalizeText($_POST['url'] ?? '', 2000);
        $ordem = (int)($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (!$this->isNonEmptyString($titulo, 3)) {
            $this->view('portfolio/form', ['evento' => null, 'error' => 'Titulo invalido (minimo 3 caracteres).']);
            return;
        }
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->view('portfolio/form', ['evento' => null, 'error' => 'URL do evento invalida.']);
            return;
        }

        $data = [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'imagem_url' => $imagemBlob,
            'url' => $url !== '' ? $url : null,
            'ordem' => $ordem,
            'ativo' => $ativo,
        ];

        $model = new PortfolioEvento();
        try {
            $model->create($data);
        } catch (PDOException $e) {
            if ($this->hasUploadedFile($_FILES['imagem'] ?? null) && $imagemBlob === null) {
                $this->view('portfolio/form', ['evento' => null, 'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.']);
                return;
            }
            $this->view('portfolio/form', ['evento' => null, 'error' => $this->dbErrorMessage($e)]);
            return;
        }

        $this->redirect('portfolio/index');
    }

    public function edit($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new PortfolioEvento();
        $evento = $model->find((int)$id);

        if (!$evento) {
            http_response_code(404);
            echo 'Evento nao encontrado.';
            return;
        }

        $this->view('portfolio/form', ['evento' => $evento]);
    }

    public function update($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('portfolio/index');
        }

        $model = new PortfolioEvento();
        $existing = $model->find((int)$id);
        if (!$existing) {
            http_response_code(404);
            echo 'Evento nao encontrado.';
            return;
        }

        $imagemBlob = isset($_FILES['imagem']) ? $this->readImageBlob($_FILES['imagem']) : null;
        $titulo = $this->normalizeText($_POST['titulo'] ?? '', 255);
        $descricao = $this->normalizeText($_POST['descricao'] ?? '', 4000) ?: null;
        $url = $this->normalizeText($_POST['url'] ?? '', 2000);
        $ordem = (int)($_POST['ordem'] ?? 0);
        $ativo = isset($_POST['ativo']) ? 1 : 0;

        if (!$this->isNonEmptyString($titulo, 3)) {
            $this->view('portfolio/form', ['evento' => $existing, 'error' => 'Titulo invalido (minimo 3 caracteres).']);
            return;
        }
        if ($url !== '' && !filter_var($url, FILTER_VALIDATE_URL)) {
            $this->view('portfolio/form', ['evento' => $existing, 'error' => 'URL do evento invalida.']);
            return;
        }

        $data = [
            'titulo' => $titulo,
            'descricao' => $descricao,
            'imagem_url' => $imagemBlob ?? ($existing['imagem_url'] ?? null),
            'url' => $url !== '' ? $url : null,
            'ordem' => $ordem,
            'ativo' => $ativo,
        ];

        try {
            $model->updateById((int)$id, $data);
        } catch (PDOException $e) {
            if ($this->hasUploadedFile($_FILES['imagem'] ?? null) && $imagemBlob === null) {
                $evento = $model->find((int)$id);
                $this->view('portfolio/form', ['evento' => $evento, 'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.']);
                return;
            }
            $evento = $model->find((int)$id);
            $this->view('portfolio/form', ['evento' => $evento, 'error' => $this->dbErrorMessage($e)]);
            return;
        }

        $this->redirect('portfolio/index');
    }

    public function delete($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new PortfolioEvento();
        $model->deleteById((int)$id);

        $this->redirect('portfolio/index');
    }
}

