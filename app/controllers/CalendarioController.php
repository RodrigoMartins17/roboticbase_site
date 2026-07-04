<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/RequisicaoMaterial.php';
require_once __DIR__ . '/../models/RequisicaoSala.php';

// Controller do calendário, onde se veem os pedidos ao longo dos dias.
class CalendarioController extends Controller
{
    // Mostra o calendário geral (com os pedidos de material e de sala).
    public function geral()
    {
        Auth::requireLogin();

        // Vou buscar os eventos dos dois tipos (material e sala) para um intervalo
        // largo à volta de hoje, e junto tudo numa lista só para o calendário desenhar.
        $inicio = date('Y-m-d', strtotime('-45 days'));
        $fim    = date('Y-m-d', strtotime('+60 days'));

        // No lado do utilizador, TODA a gente (mesmo o responsável/professor) vê
        // apenas as SUAS requisições. Ver tudo e fazer alterações é só no painel
        // de administração.
        $user = Auth::user();
        $filtroUtilizador = (int)($user['id'] ?? 0);

        $eventos = [];
        try {
            $eventos = array_merge(
                (new RequisicaoMaterial())->getEventosCalendario($inicio, $fim, $filtroUtilizador),
                (new RequisicaoSala())->getEventosCalendario($inicio, $fim, $filtroUtilizador)
            );
        } catch (\Throwable $e) {
            // Se algo correr mal a ir buscar eventos, mostro o calendário na mesma (vazio).
            $eventos = [];
        }

        $this->view('calendario/index', [
            'eventos' => $eventos,
            'pageTitle' => 'Calendário'
        ]);
    }
}
