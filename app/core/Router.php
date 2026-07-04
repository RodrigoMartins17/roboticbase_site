<?php
// O Router é como o "porteiro" do site: recebe o endereço que a pessoa
// escreveu no browser e decide para onde a mandar (que controller e que método).
// Exemplo: se o URL for "materiais/index", ele chama o MaterialController
// e corre o método index() lá dentro.
class Router
{
    public function run()
    {
        // O endereço vem no parâmetro "url" (é o .htaccess que trata disso).
        // Se vier vazio, assumo que a pessoa quer a página inicial.
        $url = $_GET['url'] ?? 'home/index';
        $url = trim($url, '/');

        // Parto o endereço nos "/" para separar as partes.
        // Ex: "materiais/index/5" fica ['materiais', 'index', '5'].
        $parts = explode('/', $url);

        // A 1ª parte é o nome do controller. Ponho a 1ª letra em maiúscula
        // e junto "Controller" ao fim, para ficar igual ao nome do ficheiro.
        $controllerName = !empty($parts[0]) ? ucfirst($parts[0]) . 'Controller' : 'AuthController';
        // A 2ª parte é o método (se não houver, uso "index").
        $method = $parts[1] ?? 'index';
        // O resto são parâmetros extra (por exemplo o id de um pedido).
        $params = array_slice($parts, 2);

        $controllerFile = __DIR__ . '/../controllers/' . $controllerName . '.php';

        // Se o ficheiro do controller não existir, mostro erro 404 (não encontrado).
        if (!file_exists($controllerFile)) {
            http_response_code(404);
            echo "Controller não encontrado.";
            exit;
        }

        require_once $controllerFile;

        // Segurança extra: confirmo que a classe existe mesmo dentro do ficheiro.
        if (!class_exists($controllerName)) {
            http_response_code(500);
            echo "Classe do controller não encontrada.";
            exit;
        }

        $controller = new $controllerName();

        // Se o método pedido não existir no controller, também dou 404.
        if (!method_exists($controller, $method)) {
            http_response_code(404);
            echo "Método não encontrado.";
            exit;
        }

        // Finalmente chamo o método certo, passando-lhe os parâmetros do URL.
        call_user_func_array([$controller, $method], $params);
    }
}
