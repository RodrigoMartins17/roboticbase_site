<?php
// Esta é a classe "mãe" de todos os controllers.
// Aqui ponho as ferramentas que TODOS os controllers usam muitas vezes:
// validar dados que vêm dos formulários, mostrar mensagens, carregar as views, etc.
// Assim não tenho de repetir este código em cada controller.
class Controller
{
    // Limpa um texto vindo de um formulário: tira espaços a mais no início/fim
    // e corta o texto se for maior do que o permitido (para não rebentar a BD).
    protected function normalizeText(?string $value, int $maxLen = 255): string
    {
        $value = trim((string)($value ?? ''));
        if (mb_strlen($value) > $maxLen) {
            return mb_substr($value, 0, $maxLen);
        }
        return $value;
    }

    // Verifica se um texto não está vazio (tem pelo menos X caracteres).
    protected function isNonEmptyString(?string $value, int $minLen = 1): bool
    {
        return mb_strlen(trim((string)$value)) >= $minLen;
    }

    // Verifica se o valor é um número inteiro maior que zero (ex: um id válido).
    protected function isPositiveInt($value): bool
    {
        return filter_var($value, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]) !== false;
    }

    // Verifica se o valor está dentro de uma lista de opções permitidas.
    // Uso isto para os estados, por exemplo: só aceito 'aceitar', 'rejeitar'...
    protected function isValidEnumValue(string $value, array $allowed): bool
    {
        return in_array($value, $allowed, true);
    }

    // Confirma se uma data está mesmo no formato certo (ano-mês-dia).
    protected function isValidDate(string $value): bool
    {
        $date = \DateTime::createFromFormat('Y-m-d', $value);
        return $date && $date->format('Y-m-d') === $value;
    }

    // Guarda uma mensagem temporária na sessão (as chamadas "flash messages").
    // Serve para depois de uma ação mostrar um aviso tipo "Guardado com sucesso!".
    protected function setFlash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    // Vai buscar essa mensagem e APAGA-a logo a seguir,
    // para ela só aparecer uma vez e não ficar a repetir-se.
    protected function getFlash(): ?array
    {
        if (empty($_SESSION['flash'])) {
            return null;
        }
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }

    // Verifica se um email tem um formato válido (tem @, etc.).
    protected function isValidEmail(string $email): bool
    {
        return (bool) filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Verifica se uma data COM horas é válida.
    protected function isValidDateTime(string $value): bool
    {
        return strtotime($value) !== false;
    }

    // Quando a base de dados dá erro, esta função tenta transformar a mensagem
    // técnica e feia numa frase que a pessoa percebe.
    protected function dbErrorMessage(\PDOException $e): string
    {
        // Registo o erro da base de dados no log de erros (via stored procedure).
        try {
            require_once __DIR__ . '/../models/Log.php';
            $sqlstate = $e->errorInfo[0] ?? (string)$e->getCode();
            $codigo   = isset($e->errorInfo[1]) ? (string)$e->errorInfo[1] : null;
            (new Log())->registarErro('Base de dados (controller)', $e->getMessage(), (string)$sqlstate, $codigo, $_SESSION['user']['id'] ?? null);
        } catch (\Throwable $x) {
        }

        $message = $e->getMessage();
        if (preg_match('/SQLSTATE\\[45000\\].*?: (.+)$/', $message, $match)) {
            return $match[1];
        }
        // Este erro acontece quando se tenta guardar algo repetido (ex: email já existe).
        if (strpos($message, 'Duplicate entry') !== false) {
            return 'Já existe um registo com esses dados.';
        }
        return 'Erro ao guardar dados. Tente novamente.';
    }

    // Lê uma imagem que a pessoa fez upload e devolve os dados dela.
    // Também confirma que é mesmo uma imagem e de um tipo permitido (jpg, png...).
    protected function readImageBlob(array $file): ?string
    {
        // Se houve algum erro no upload, não faço nada.
        if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
            return null;
        }

        // O ficheiro fica primeiro numa pasta temporária. Confirmo que existe.
        $tmpPath = $file['tmp_name'] ?? '';
        if ($tmpPath === '' || !is_file($tmpPath)) {
            return null;
        }

        // getimagesize() confirma que o ficheiro é MESMO uma imagem.
        $imageInfo = getimagesize($tmpPath);
        if ($imageInfo === false) {
            return null;
        }

        // Vejo a extensão real e só deixo passar os tipos de imagem que quero.
        $ext = image_type_to_extension($imageInfo[2] ?? 0, false);
        $ext = $ext ? strtolower($ext) : '';
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        if (!in_array($ext, $allowed, true)) {
            return null;
        }

        // Se chegou aqui, está tudo bem: devolvo o conteúdo da imagem.
        return file_get_contents($tmpPath) ?: null;
    }

    // Diz-me se a pessoa enviou mesmo um ficheiro no formulário ou se deixou vazio.
    protected function hasUploadedFile(?array $file): bool
    {
        if (!$file || !isset($file['error'])) {
            return false;
        }
        return (int)$file['error'] !== UPLOAD_ERR_NO_FILE;
    }

    // Mostra uma página "normal" (do site público), com o cabeçalho e o rodapé à volta.
    // O $data são as variáveis que quero enviar para a view usar.
    protected function view(string $view, array $data = [])
    {
        $data['flash'] = $this->getFlash();
        // extract() transforma cada item do array numa variável (ex: $eventos).
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            echo "View não encontrada: $viewFile";
            return;
        }

        // Junto o cabeçalho + a página + o rodapé, por esta ordem.
        require __DIR__ . '/../views/layouts/header.php';
        require $viewFile;
        require __DIR__ . '/../views/layouts/footer.php';
    }

    // Igual ao view() mas SEM cabeçalho/rodapé automáticos.
    // Uso quando a própria página já tem o HTML todo dela (como as minhas páginas com design próprio).
    protected function viewRaw(string $view, array $data = [])
    {
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';

        if (!file_exists($viewFile)) {
            echo "View não encontrada: $viewFile";
            return;
        }

        require $viewFile;
    }

    // Manda a pessoa para outra página (redirecionamento) e para o código aqui.
    protected function redirect(string $path)
    {
        header('Location: ' . BASE_URL . ltrim($path, '/'));
        exit;
    }

    // Mostra uma página dentro do painel de administração (com a barra lateral).
    // Esta parte é a da administração, que ficou como estava.
    protected function viewAdmin(string $view, array $data = [], string $breadcrumb = '', string $adminSection = '')
    {
        $data['admin_section'] = $adminSection;
        extract($data);
        $viewFile = __DIR__ . '/../views/' . $view . '.php';
        if (!file_exists($viewFile)) {
            echo "View não encontrada: $viewFile";
            return;
        }
        $content_view = $viewFile;
        $admin_breadcrumb = $breadcrumb;
        require __DIR__ . '/../views/layouts/admin.php';
    }
}
