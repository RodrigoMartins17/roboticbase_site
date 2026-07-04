<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';

// O Dashboard é a página principal depois de a pessoa fazer login.
// Mostra a mensagem de boas-vindas, os professores e os eventos do clube.
class DashboardController extends Controller
{
    public function index()
    {
        // Esta página é só para quem tem login, por isso obrigo a estar autenticado.
        Auth::requireLogin();
        $user = Auth::user();

        // Vou buscar os models que preciso para esta página.
        require_once __DIR__ . '/../models/Utilizador.php';
        require_once __DIR__ . '/../models/PortfolioEvento.php';

        // Lista dos professores/responsáveis, para mostrar a equipa.
        $utilizadorModel = new Utilizador();
        $professores = $utilizadorModel->professoresResponsaveis();

        // Lista dos eventos do clube (o "portfólio").
        $portfolioModel = new PortfolioEvento();
        $eventos = $portfolioModel->todos();

        // O dashboard usa agora o mesmo header/rodapé partilhados (como as outras páginas).
        $this->view('dashboard/index', [
            'user' => $user,
            'professores' => $professores,
            'eventos' => $eventos,
            'pageTitle' => 'Início'
        ]);
    }

    // Atalho para entrar no painel de administração.
    public function administracao()
    {
        Auth::requireLogin();
        // Só o admin e o responsável é que podem entrar aqui.
        if (!Auth::isAdmin() && !Auth::isResponsavel()) {
            http_response_code(403); // 403 = proibido
            echo "Sem permissão.";
            return;
        }
        $this->redirect('admin/index');
    }
}
