<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Material.php';

class InovacaoController extends Controller
{
    public function index()
    {
        Auth::requireLogin();
        $materialModel = new Material();
        $modelos = $materialModel->todosModelos();
        $itensDisponiveis = $materialModel->itensDisponiveis();

        $stock = [];
        foreach ($itensDisponiveis as $item) {
            $id = (int)$item['id_material'];
            $stock[$id] = ($stock[$id] ?? 0) + 1;
        }

        $catalogo = [];
        foreach ($modelos as $m) {
            $catalogo[] = [
                'id' => (int)$m['id'],
                'designacao' => $m['designacao'],
                'categorias' => $m['categorias'] ?? 'GERAL',
                'disponivel' => $stock[(int)$m['id']] ?? 0,
            ];
        }

        $this->view('inovacao/index', ['catalogo' => $catalogo]);
    }
}
