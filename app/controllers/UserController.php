<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Utilizador.php';

class UserController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Utilizador();
        $users = $model->all();
        $this->view('utilizadores/index', ['users' => $users]);
    }

    public function create()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $this->view('utilizadores/form', ['user' => null]);
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
            $this->redirect('user/index');
        }

        $password = (string)($_POST['palavra_passe'] ?? '');
        if ($password === '') {
            $password = 'Robotica!2026';
        }
        if (strlen($password) < 6) {
            $this->view('utilizadores/form', ['user' => null, 'error' => 'Palavra-passe invalida (minimo 6 caracteres).']);
            return;
        }

        $tipo = $_POST['tipo'] ?? 'ALUNO';
        if ($tipo === 'PROFESSOR' && isset($_POST['responsavel'])) {
            $tipo = 'RESPONSAVEL';
        }
        if (!$this->isValidEnumValue($tipo, ['ALUNO', 'PROFESSOR', 'RESPONSAVEL', 'ADMIN'])) {
            $this->view('utilizadores/form', ['user' => null, 'error' => 'Tipo de utilizador invalido.']);
            return;
        }

        $nome = $this->normalizeText($_POST['nome'] ?? '', 100);
        $email = $this->normalizeText($_POST['email'] ?? '', 255);
        $telefone = $this->normalizeText($_POST['telefone'] ?? '', 20);
        $linkedin = $this->normalizeText($_POST['linkedin'] ?? '', 255);
        $turma = $this->normalizeText($_POST['turma'] ?? '', 10);
        $dataNascimento = (string)($_POST['data_nascimento'] ?? date('Y-m-d'));

        if (!$this->isNonEmptyString($nome, 2) || !$this->isValidEmail($email) || !$this->isValidDate($dataNascimento)) {
            $this->view('utilizadores/form', ['user' => null, 'error' => 'Dados de utilizador invalidos.']);
            return;
        }
        if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
            $this->view('utilizadores/form', ['user' => null, 'error' => 'URL do LinkedIn invalido.']);
            return;
        }

        $fotoBlob = isset($_FILES['foto_perfil']) ? $this->readImageBlob($_FILES['foto_perfil']) : null;

        $data = [
            'nome' => $nome,
            'email' => $email,
            'password_hash' => password_hash($password, PASSWORD_DEFAULT),
            'tipo' => $tipo,
            'telefone' => $telefone !== '' ? $telefone : '',
            'linkedin' => $linkedin !== '' ? $linkedin : null,
            'turma' => $turma !== '' ? $turma : null,
            'data_nascimento' => $dataNascimento,
            'foto' => $fotoBlob,
            'email_verificado' => 1,
            'email_verificacao_token' => null,
        ];

        $model = new Utilizador();
        try {
            $model->create($data);
        } catch (PDOException $e) {
            if ($this->hasUploadedFile($_FILES['foto_perfil'] ?? null) && $fotoBlob === null) {
                $this->view('utilizadores/form', ['user' => null, 'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.']);
                return;
            }
            $this->view('utilizadores/form', ['user' => null, 'error' => $this->dbErrorMessage($e)]);
            return;
        }

        $this->redirect('user/index');
    }

    public function edit($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Utilizador();
        $user = $model->find((int)$id);
        if ($user) {
            unset($user['password_hash']);
        }
        $this->view('utilizadores/form', ['user' => $user]);
    }

    public function editAdmin($id)
    {
        $this->edit($id);
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
            $this->redirect('user/index');
        }

        $model = new Utilizador();
        $user = $model->find((int)$id);
        if (!$user) {
            http_response_code(404);
            echo 'Utilizador nao encontrado.';
            return;
        }

        $tipo = $_POST['tipo'] ?? 'ALUNO';
        if ($tipo === 'PROFESSOR' && isset($_POST['responsavel'])) {
            $tipo = 'RESPONSAVEL';
        }
        if (!$this->isValidEnumValue($tipo, ['ALUNO', 'PROFESSOR', 'RESPONSAVEL', 'ADMIN'])) {
            $this->view('utilizadores/form', ['user' => $user, 'error' => 'Tipo de utilizador invalido.']);
            return;
        }

        $nome = $this->normalizeText($_POST['nome'] ?? '', 100);
        $email = $this->normalizeText($_POST['email'] ?? '', 255);
        $telefone = $this->normalizeText($_POST['telefone'] ?? '', 20);
        $linkedin = $this->normalizeText($_POST['linkedin'] ?? '', 255);
        $turma = $this->normalizeText($_POST['turma'] ?? '', 10);
        $dataNascimento = (string)($_POST['data_nascimento'] ?? ($user['data_nascimento'] ?? date('Y-m-d')));

        if (!$this->isNonEmptyString($nome, 2) || !$this->isValidEmail($email) || !$this->isValidDate($dataNascimento)) {
            $this->view('utilizadores/form', ['user' => $user, 'error' => 'Dados de utilizador invalidos.']);
            return;
        }
        if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
            $this->view('utilizadores/form', ['user' => $user, 'error' => 'URL do LinkedIn invalido.']);
            return;
        }

        $fotoBlob = isset($_FILES['foto_perfil']) ? $this->readImageBlob($_FILES['foto_perfil']) : null;

        $data = [
            'nome' => $nome,
            'email' => $email,
            'tipo' => $tipo,
            'telefone' => $telefone !== '' ? $telefone : ($user['telefone'] ?? ''),
            'linkedin' => $linkedin !== '' ? $linkedin : null,
            'turma' => $turma !== '' ? $turma : null,
            'data_nascimento' => $dataNascimento,
        ];
        if ($fotoBlob !== null) {
            $data['foto'] = $fotoBlob;
        }

        try {
            $model->updateById((int)$id, $data);
            if (!empty($_POST['palavra_passe'])) {
                if (strlen((string)$_POST['palavra_passe']) < 6) {
                    $this->view('utilizadores/form', ['user' => $user, 'error' => 'Palavra-passe invalida (minimo 6 caracteres).']);
                    return;
                }
                $passwordHash = password_hash((string)$_POST['palavra_passe'], PASSWORD_DEFAULT);
                $model->updatePassword((int)$id, $passwordHash);
                // Password definida no painel => conta fica verificada.
                $model->markEmailAsVerified((int)$id);
            }
        } catch (PDOException $e) {
            if ($this->hasUploadedFile($_FILES['foto_perfil'] ?? null) && $fotoBlob === null) {
                $fresh = $model->find((int)$id);
                if ($fresh) {
                    unset($fresh['password_hash']);
                }
                $this->view('utilizadores/form', ['user' => $fresh, 'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.']);
                return;
            }
            $fresh = $model->find((int)$id);
            if ($fresh) {
                unset($fresh['password_hash']);
            }
            $this->view('utilizadores/form', ['user' => $fresh, 'error' => $this->dbErrorMessage($e)]);
            return;
        }

        $this->redirect('user/index');
    }

    public function delete($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Utilizador();
        $model->deleteById((int)$id);
        $this->redirect('user/index');
    }

    public function deleteBulk()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $ids = $_POST['user_ids'] ?? [];
            if (is_array($ids) && !empty($ids)) {
                $ids = array_map('intval', $ids);
                $ids = array_filter($ids, fn($id) => $id > 0);
                $currentId = (int)($_SESSION['user']['id'] ?? 0);
                $ids = array_values(array_diff($ids, [$currentId]));
                if (!empty($ids)) {
                    $model = new Utilizador();
                    $model->deleteByIds($ids);
                }
            }
        }

        $this->redirect('user/index');
    }

    public function responsaveis()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Utilizador();
        $professores = $model->professores();
        $responsaveis = $model->professoresResponsaveis();
        $idsResponsaveis = array_column($responsaveis, 'id');

        foreach ($professores as &$prof) {
            $prof['is_responsavel'] = in_array($prof['id'], $idsResponsaveis, true);
        }

        $this->view('utilizadores/responsaveis', ['professores' => $professores]);
    }

    public function tornarResponsavel($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Utilizador();
        $model->tornarResponsavel((int)$id);
        $this->redirect('user/responsaveis');
    }

    public function removerResponsavel($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo 'Sem permissao.';
            return;
        }

        $model = new Utilizador();
        $model->removerResponsavel((int)$id);
        $this->redirect('user/responsaveis');
    }
}

