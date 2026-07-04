<?php
require_once __DIR__ . '/../core/Controller.php';
require_once __DIR__ . '/../core/Auth.php';
require_once __DIR__ . '/../models/Sala.php';

// Controller das SALAS. Tal como no dos materiais, só o index() é que está
// mesmo a ser usado (a página onde se veem as salas). A gestão (criar/editar/
// apagar salas) passou para o painel de administração, por isso os métodos a
// seguir apontam para a view 'salas/form', que já não existe.
class SalaController extends Controller
{
    // Página que mostra a lista de salas do clube.
    public function index()
    {
        Auth::requireLogin();
        // As salas são só para professores/responsáveis/admin. Um aluno que tente
        // abrir esta página pelo URL é mandado de volta para a página inicial.
        if (Auth::isAluno()) {
            $this->redirect('dashboard/index');
        }
        $model = new Sala();
        $salas = $model->todas(); // vai buscar todas as salas à base de dados
        $this->view('salas/index', ['salas' => $salas, 'pageTitle' => 'Salas']);
    }

    // --- Antiga gestão de salas (hoje feita na administração, não usada aqui). ---
    public function create()
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }
        $this->view('salas/form', ['sala' => null]);
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
            $numero = $this->normalizeText($_POST['numero'] ?? '', 15);
            $bloco = strtoupper($this->normalizeText($_POST['bloco'] ?? '', 10));
            $andar = (int)($_POST['andar'] ?? 0);
            $capacidade = (int)($_POST['capacidade'] ?? 0);
            $descricao = $this->normalizeText($_POST['descricao'] ?? '', 2000) ?: null;

            if (!$this->isNonEmptyString($numero, 1) || $capacidade < 1 || $andar < 0 || $andar > 2 || !$this->isNonEmptyString($bloco, 1)) {
                $this->view('salas/form', [
                    'sala' => null,
                    'error' => 'Dados invalidos da sala. Verifique numero, bloco, andar e capacidade.'
                ]);
                return;
            }

            $data = [
                'numero'     => $numero,
                'andar'      => $andar,
                'bloco'      => $bloco,
                'capacidade' => $capacidade,
                'descricao'  => $descricao,
            ];
            $model = new Sala();
            try {
                $model->create($data);
            } catch (PDOException $e) {
                $this->view('salas/form', [
                    'sala' => null,
                    'error' => $this->dbErrorMessage($e)
                ]);
                return;
            }
        }

        $this->redirect('sala/index');
    }

    public function edit($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin() && !Auth::isProf()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        $model = new Sala();
        $sala = $model->find((int)$id);
        $this->view('salas/form', ['sala' => $sala]);
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
            $numero = $this->normalizeText($_POST['numero'] ?? '', 15);
            $bloco = strtoupper($this->normalizeText($_POST['bloco'] ?? '', 10));
            $andar = (int)($_POST['andar'] ?? 0);
            $capacidade = (int)($_POST['capacidade'] ?? 0);
            $descricao = $this->normalizeText($_POST['descricao'] ?? '', 2000) ?: null;

            if (!$this->isNonEmptyString($numero, 1) || $capacidade < 1 || $andar < 0 || $andar > 2 || !$this->isNonEmptyString($bloco, 1)) {
                $model = new Sala();
                $sala = $model->find((int)$id);
                $this->view('salas/form', [
                    'sala' => $sala,
                    'error' => 'Dados invalidos da sala. Verifique numero, bloco, andar e capacidade.'
                ]);
                return;
            }

            $data = [
                'numero'     => $numero,
                'andar'      => $andar,
                'bloco'      => $bloco,
                'capacidade' => $capacidade,
                'descricao'  => $descricao,
            ];
            $model = new Sala();
            try {
                $model->updateById((int)$id, $data);
            } catch (PDOException $e) {
                $sala = $model->find((int)$id);
                $this->view('salas/form', [
                    'sala' => $sala,
                    'error' => $this->dbErrorMessage($e)
                ]);
                return;
            }
        }

        $this->redirect('sala/index');
    }

    public function delete($id)
    {
        Auth::requireLogin();
        if (!Auth::isAdmin()) {
            http_response_code(403);
            echo "Sem permissão.";
            return;
        }

        $model = new Sala();
        $model->deleteById((int)$id);

        $this->redirect('sala/index');
    }
}
