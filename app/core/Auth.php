<?php
// Esta classe trata de tudo o que tem a ver com o utilizador que está "logado".
// Guardo os dados do utilizador na sessão ($_SESSION) quando ele faz login,
// e depois uso estes métodos para saber quem ele é e o que pode fazer.
class Auth
{
    // Devolve os dados do utilizador que está na sessão.
    // Se ninguém tiver feito login, devolve null (vazio).
    public static function user()
    {
        return $_SESSION['user'] ?? null;
    }

    // Serve só para responder à pergunta: "há alguém com sessão iniciada?"
    public static function check()
    {
        return isset($_SESSION['user']);
    }

    // Daqui para baixo é tudo para verificar o TIPO de utilizador.
    // Cada pessoa tem um tipo (ADMIN, PROFESSOR, RESPONSAVEL ou ALUNO)
    // e conforme o tipo pode ou não fazer certas coisas.

    // É o administrador? (quem manda em tudo)
    public static function isAdmin()
    {
        return self::check() && $_SESSION['user']['tipo'] === 'ADMIN';
    }

    // É professor? Aqui considero também o responsável como professor,
    // porque os dois têm praticamente as mesmas permissões.
    public static function isProf()
    {
        return self::check() && ($_SESSION['user']['tipo'] === 'PROFESSOR' || $_SESSION['user']['tipo'] === 'RESPONSAVEL');
    }

    // É o professor responsável pelo clube?
    public static function isResponsavel()
    {
        return self::check() && $_SESSION['user']['tipo'] === 'RESPONSAVEL';
    }

    // É um aluno normal?
    public static function isAluno()
    {
        return self::check() && $_SESSION['user']['tipo'] === 'ALUNO';
    }

    // Uso isto no início das páginas que só podem ser vistas com login.
    // Se a pessoa não tiver sessão, mando-a para a página de login e paro tudo.
    public static function requireLogin()
    {
        if (!self::check()) {
            header('Location: ' . BASE_URL . 'auth/login');
            exit;
        }
    }
}
