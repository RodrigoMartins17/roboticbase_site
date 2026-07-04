<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../models/PortfolioEvento.php';
require_once __DIR__ . '/../models/Utilizador.php';

// O HomeController trata da página inicial do site (a que aparece a quem ainda
// não fez login). É a "montra" do clube: mostra os eventos e a equipa.
class HomeController extends Controller
{
    public function index()
    {
        // Vou buscar todos os eventos do clube à base de dados.
        $portfolioModel = new PortfolioEvento();
        $eventos = $portfolioModel->todos();

        // E também os professores, para a secção da equipa.
        $utilizadorModel = new Utilizador();
        $professores = $utilizadorModel->professores();

        // A home mostra o MESMO conteúdo do dashboard (mesma view). O header sabe
        // que não há sessão e mostra o botão "Entrar" em vez do avatar.
        $this->view('dashboard/index', [
            'eventos' => $eventos,
            'professores' => $professores,
            'pageTitle' => 'Clube de Robótica'
        ]);
    }
}
