<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../core/Mailer.php';
require_once __DIR__ . '/../models/Utilizador.php';
require_once __DIR__ . '/../models/Log.php';

// Este controller trata das contas: iniciar sessão (login), criar conta (registo),
// validar o email, ver/editar o perfil e terminar sessão (logout).
class AuthController extends Controller
{
    // LOGIN. Se o formulário foi submetido, tento entrar; senão mostro a página.
    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Não deixo campos vazios.
            if (empty($email) || empty($password)) {
                $this->viewRaw('auth/login', ['error' => 'Por favor, preencha todos os campos.', 'old_email' => $email]);
                return;
            }

            // Procuro o utilizador pelo email.
            $userModel = new Utilizador();
            $user = $userModel->findByEmail($email);

            // Se não existe nenhum com esse email, aviso.
            if (!$user) {
                $this->viewRaw('auth/login', ['error' => 'Email nao encontrado.', 'old_email' => $email]);
                return;
            }

            // A conta tem de ter o email validado antes de poder entrar.
            if (isset($user['email_verificado']) && (int)$user['email_verificado'] !== 1) {
                $this->viewRaw('auth/login', ['error' => 'Conta ainda nao validada. Verifica o teu email.', 'old_email' => $email]);
                return;
            }

            // Se por algum motivo a conta não tiver password guardada, paro.
            if (!isset($user['password_hash']) || empty($user['password_hash'])) {
                $this->viewRaw('auth/login', ['error' => 'Erro: utilizador sem senha definida.']);
                return;
            }

            // Comparo a password escrita com a que está guardada (encriptada).
            $senhaValida = false;
            if (password_verify($password, $user['password_hash'])) {
                $senhaValida = true;
            } elseif ($user['password_hash'] === '123' && $password === '123') {
                // Atalho só para testes durante o desenvolvimento (password "123").
                $senhaValida = true;
            }

            if ($senhaValida) {
                // Login certo: tiro a password dos dados (por segurança) e guardo o
                // utilizador na sessão. A partir daqui ele fica "logado".
                unset($user['password_hash']);
                $_SESSION['user'] = $user;
                // Registo a entrada no sistema (log de acessos).
                (new Log())->registar((int)$user['id'], 'LOGIN', 'Início de sessão (' . $email . ')');
                $this->redirect('dashboard/index');
            } else {
                (new Log())->registar(isset($user['id']) ? (int)$user['id'] : null, 'LOGIN_FALHADO', 'Tentativa de login falhada - senha incorreta (' . $email . ')');
                $this->viewRaw('auth/login', ['error' => 'Senha incorreta.', 'old_email' => $email]);
                return;
            }
        } else {
            // Se não foi submetido nada, só mostro a página de login.
            $this->viewRaw('auth/login');
        }
    }

    // REGISTO de uma conta nova (fica sempre como ALUNO).
    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Recolho tudo o que a pessoa escreveu e limpo os textos.
            $nome = $this->normalizeText($_POST['nome'] ?? '', 100);
            $email = $this->normalizeText($_POST['email'] ?? '', 255);
            $password = $_POST['password'] ?? '';
            $passwordConfirm = $_POST['password_confirm'] ?? '';
            $linkedin = $this->normalizeText($_POST['linkedin'] ?? '', 255);
            $telefone = $this->normalizeText($_POST['telefone'] ?? '', 20);
            $turma = $this->normalizeText($_POST['turma'] ?? '', 20);
            $dataNascimento = $_POST['data_nascimento'] ?? null;

            // A partir daqui é tudo validação: confirmo que os dados fazem sentido.
            if (empty($nome) || empty($email) || empty($password) || empty($passwordConfirm)) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'Por favor, preencha todos os campos obrigatorios.']);
                return;
            }
            if (!$this->isValidEmail($email)) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'Email invalido.']);
                return;
            }
            if (empty($telefone) || empty($dataNascimento)) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'Telefone e data de nascimento sao obrigatorios.']);
                return;
            }
            if (!$this->isValidDate((string)$dataNascimento)) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'Data de nascimento invalida.']);
                return;
            }
            if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'URL do LinkedIn invalida.']);
                return;
            }
            if (strlen($password) < 6) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'A palavra-passe deve ter pelo menos 6 caracteres.']);
                return;
            }
            // As duas passwords têm de ser iguais.
            if ($password !== $passwordConfirm) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'As palavras-passe nao coincidem.']);
                return;
            }

            // Não deixo dois utilizadores com o mesmo email.
            $userModel = new Utilizador();
            $existing = $userModel->findByEmail($email);
            if ($existing) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'Email ja registrado.']);
                return;
            }

            // Se enviou foto de perfil, confirmo que é mesmo uma imagem válida.
            $fotoBlob = isset($_FILES['foto_perfil']) ? $this->readImageBlob($_FILES['foto_perfil']) : null;
            if ($this->hasUploadedFile($_FILES['foto_perfil'] ?? null) && $fotoBlob === null) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.']);
                return;
            }

            // Crio um "token" aleatório para o link de validação do email,
            // e encripto a password (nunca se guarda a password às claras!).
            $token = bin2hex(random_bytes(32));
            $passwordHash = password_hash($password, PASSWORD_DEFAULT);
            $data = [
                'nome'            => $nome,
                'email'           => $email,
                'password_hash'   => $passwordHash,
                'tipo'            => 'ALUNO',
                'telefone'        => !empty($telefone) ? $telefone : '',
                'linkedin'        => !empty($linkedin) ? $linkedin : null,
                'turma'           => $turma !== '' ? $turma : null,
                'data_nascimento' => !empty($dataNascimento) ? $dataNascimento : date('Y-m-d'),
                'foto'            => $fotoBlob,
                'email_verificado' => 0, // começa por validar
                'email_verificacao_token' => $token,
            ];

            try {
                $userModel->create($data);
            } catch (PDOException $e) {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => $this->dbErrorMessage($e)]);
                return;
            }

            // Conta criada: envio o email de validação e mando a pessoa para o login.
            $created = $userModel->findByEmail($email);
            if ($created) {
                Mailer::sendValidationEmail($email, $nome, $token);
                (new Log())->registar((int)($created['id'] ?? 0) ?: null, 'REGISTO', 'Nova conta criada (' . $email . ')');
                $this->viewRaw('auth/login', ['success' => 'Conta criada. Verifica o teu email e valida a conta antes de entrar.']);
            } else {
                $this->viewRaw('auth/register', ['old' => $_POST, 'error' => 'Erro ao criar conta.']);
            }
        } else {
            // Sem POST, só mostro o formulário de registo.
            $this->viewRaw('auth/register');
        }
    }

    // VALIDAÇÃO do email: a pessoa clica no link do email e cai aqui com um token.
    public function verifyEmail()
    {
        $token = $_GET['token'] ?? '';
        if (empty($token)) {
            $this->viewRaw('auth/login', ['error' => 'Token de validacao invalido.']);
            return;
        }

        // Procuro o utilizador que tem este token.
        $model = new Utilizador();
        $user = $model->findByVerificationToken($token);
        if (!$user) {
            $this->viewRaw('auth/login', ['error' => 'Token expirado ou invalido.']);
            return;
        }

        // Marco o email como validado e mando para o login.
        $model->markEmailAsVerified((int)$user['id']);
        $this->viewRaw('auth/login', ['success' => 'Conta validada com sucesso. Ja podes iniciar sessao.']);
    }

    // Gera um token de reposição, guarda-o e envia o email com o link.
    // Envolvo tudo num try/catch para nunca rebentar a página (ex: se as colunas
    // reset_token/reset_expira não existirem na BD); o erro fica no log de erros.
    private function enviarResetPassword(array $user): void
    {
        try {
            $token = bin2hex(random_bytes(32));
            $expira = date('Y-m-d H:i:s', strtotime('+1 hour'));
            $model = new Utilizador();
            $model->definirResetToken((int)$user['id'], $token, $expira);
            Mailer::sendPasswordReset($user['email'], $user['nome'] ?? 'Utilizador', $token);
            (new Log())->registar((int)$user['id'], 'ACAO', 'Pedido de alteração de palavra-passe (' . $user['email'] . ')');
        } catch (\Throwable $e) {
            try { (new Log())->registarErro('AuthController::enviarResetPassword', $e->getMessage()); } catch (\Throwable $x) {}
        }
    }

    // "ESQUECI-ME DA PALAVRA-PASSE" — a partir do login (sem sessão).
    public function esqueciPassword()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $this->normalizeText($_POST['email'] ?? '', 255);
            $model = new Utilizador();
            $user = $email ? $model->findByEmail($email) : null;
            if ($user && !empty($user['email'])) {
                $this->enviarResetPassword($user);
            }
            // Mensagem genérica de propósito (não revelo se o email existe ou não).
            $this->viewRaw('auth/esqueci', ['success' => 'Se existir uma conta com esse email, enviámos um link para alterares a palavra-passe.']);
            return;
        }
        $this->viewRaw('auth/esqueci');
    }

    // ALTERAR PASSWORD a partir do PERFIL (com sessão): envia o email ao próprio.
    public function pedirNovaPassword()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $model = new Utilizador();
        $dbUser = $model->find((int)$user['id']);
        if ($dbUser && !empty($dbUser['email'])) {
            $this->enviarResetPassword($dbUser);
        }
        $this->setFlash('success', 'Enviámos um email para ' . ($user['email'] ?? 'o teu email') . ' com o link para alterares a palavra-passe.');
        $this->redirect('auth/profile');
    }

    // Formulário para definir a NOVA palavra-passe (aberto pelo link do email).
    public function resetPassword()
    {
        $token = $_GET['token'] ?? ($_POST['token'] ?? '');
        $model = new Utilizador();
        $user = $token ? $model->findByResetToken($token) : null;

        // Sem token válido (inexistente ou expirado) não deixo continuar.
        if (!$user) {
            $this->viewRaw('auth/login', ['error' => 'O link de alteração de palavra-passe é inválido ou expirou.']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirm = $_POST['password_confirm'] ?? '';
            if (strlen($password) < 6) {
                $this->viewRaw('auth/reset_password', ['token' => $token, 'error' => 'A palavra-passe deve ter pelo menos 6 caracteres.']);
                return;
            }
            if ($password !== $confirm) {
                $this->viewRaw('auth/reset_password', ['token' => $token, 'error' => 'As palavras-passe não coincidem.']);
                return;
            }
            $model->updatePassword((int)$user['id'], password_hash($password, PASSWORD_DEFAULT));
            $model->limparResetToken((int)$user['id']); // o token só serve uma vez
            (new Log())->registar((int)$user['id'], 'ALTERACAO', 'Palavra-passe alterada com sucesso (' . ($user['email'] ?? '') . ')');
            $this->viewRaw('auth/login', ['success' => 'Palavra-passe alterada com sucesso. Já podes iniciar sessão.']);
            return;
        }

        $this->viewRaw('auth/reset_password', ['token' => $token]);
    }

    // Mostra a página de PERFIL do utilizador que está com login.
    public function profile()
    {
        Auth::requireLogin();
        $user = Auth::user();
        $model = new Utilizador();
        // Vou buscar os dados atualizados à base de dados.
        $dbUser = $model->find((int)$user['id']);
        // Tiro a password antes de a enviar para a página (segurança).
        if ($dbUser) {
            unset($dbUser['password_hash']);
        }
        $this->view('auth/profile', ['user' => $dbUser, 'pageTitle' => 'O meu perfil']);
    }

    // Guarda as alterações feitas no perfil.
    public function updateProfile()
    {
        Auth::requireLogin();
        $current = Auth::user();
        $model = new Utilizador();

        // Recolho e limpo os dados do formulário.
        $nome = $this->normalizeText($_POST['nome'] ?? '', 100);
        $telefone = $this->normalizeText($_POST['telefone'] ?? '', 20);
        $linkedin = $this->normalizeText($_POST['linkedin'] ?? '', 255);
        $turma = $this->normalizeText($_POST['turma'] ?? '', 20);
        $dataNascimento = $_POST['data_nascimento'] ?? null;

        // Validações simples.
        if (!$this->isNonEmptyString($nome, 2)) {
            $this->viewRaw('auth/profile', ['user' => $current, 'error' => 'Nome e obrigatorio.']);
            return;
        }
        if ($dataNascimento && !$this->isValidDate((string)$dataNascimento)) {
            $this->viewRaw('auth/profile', ['user' => $current, 'error' => 'Data de nascimento invalida.']);
            return;
        }
        if ($linkedin !== '' && !filter_var($linkedin, FILTER_VALIDATE_URL)) {
            $this->viewRaw('auth/profile', ['user' => $current, 'error' => 'URL do LinkedIn invalida.']);
            return;
        }

        // Se trocou a foto, valido-a.
        $fotoBlob = isset($_FILES['foto_perfil']) ? $this->readImageBlob($_FILES['foto_perfil']) : null;
        if ($this->hasUploadedFile($_FILES['foto_perfil'] ?? null) && $fotoBlob === null) {
            $this->viewRaw('auth/profile', ['user' => $current, 'error' => 'Imagem invalida. Use JPG, PNG, GIF ou WebP.']);
            return;
        }

        // Para os campos que ficaram vazios, mantenho o valor que já lá estava.
        $currentUser = $model->find((int)$current['id']);
        $data = [
            'nome' => $nome,
            'telefone' => !empty($telefone) ? $telefone : ($currentUser['telefone'] ?? ''),
            'linkedin' => !empty($linkedin) ? $linkedin : null,
            'turma' => !empty($turma) ? $turma : null,
            'data_nascimento' => !empty($dataNascimento) ? $dataNascimento : ($currentUser['data_nascimento'] ?? date('Y-m-d')),
        ];
        // Só mudo a foto se enviaram uma nova.
        if ($fotoBlob !== null) {
            $data['foto'] = $fotoBlob;
        }

        try {
            $model->updateProfile((int)$current['id'], $data);
        } catch (PDOException $e) {
            $this->viewRaw('auth/profile', ['user' => $current, 'error' => $this->dbErrorMessage($e)]);
            return;
        }

        // Atualizo também os dados guardados na sessão, para aparecerem logo certos.
        $updated = $model->find((int)$current['id']);
        if ($updated) {
            unset($updated['password_hash']);
            $_SESSION['user'] = $updated;
        }

        $this->redirect('auth/profile');
    }

    // LOGOUT: destruo a sessão (a pessoa deixa de estar autenticada) e volto ao login.
    public function logout()
    {
        // Registo a saída antes de destruir a sessão.
        $u = Auth::user();
        if ($u) {
            (new Log())->registar((int)$u['id'], 'LOGOUT', 'Terminou sessão (' . ($u['email'] ?? '') . ')');
        }
        session_destroy();
        $this->redirect('auth/login');
    }
}
