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

        // Antes de guardar, encolho a imagem: fotos de telemóvel têm vários MB
        // e não precisamos disso para um avatar/miniatura. Reduzo para no máximo
        // 800px e comprimo em JPEG — fica leve, a página carrega mais depressa
        // e cabe folgadamente na base de dados.
        $comprimida = $this->compressImage($tmpPath, $imageInfo);
        if ($comprimida !== null) {
            return $comprimida;
        }

        // Se a compressão falhar (ex: GD sem suporte ao formato), devolvo o original.
        return file_get_contents($tmpPath) ?: null;
    }

    // Reduz uma imagem para caber num quadrado de 800x800 (mantendo proporções)
    // e devolve-a comprimida em JPEG. Devolve null se não conseguir.
    protected function compressImage(string $path, array $imageInfo, int $maxLado = 800, int $qualidade = 82): ?string
    {
        if (!function_exists('imagecreatetruecolor')) {
            return null; // extensão GD não disponível
        }

        [$larg, $alt] = $imageInfo;
        if ($larg < 1 || $alt < 1) {
            return null;
        }

        // Abro a imagem consoante o formato real dela.
        $origem = match ($imageInfo[2]) {
            IMAGETYPE_JPEG => @imagecreatefromjpeg($path),
            IMAGETYPE_PNG  => @imagecreatefrompng($path),
            IMAGETYPE_GIF  => @imagecreatefromgif($path),
            IMAGETYPE_WEBP => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($path) : false,
            default        => false,
        };
        if (!$origem) {
            return null;
        }

        // Calculo o novo tamanho (só encolho, nunca amplio).
        $escala = min(1, $maxLado / max($larg, $alt));
        $novaLarg = max(1, (int)round($larg * $escala));
        $novaAlt  = max(1, (int)round($alt * $escala));

        $destino = imagecreatetruecolor($novaLarg, $novaAlt);
        // Fundo branco para imagens com transparência (o JPEG não tem transparência).
        $branco = imagecolorallocate($destino, 255, 255, 255);
        imagefill($destino, 0, 0, $branco);
        imagecopyresampled($destino, $origem, 0, 0, 0, 0, $novaLarg, $novaAlt, $larg, $alt);
        imagedestroy($origem);

        ob_start();
        $ok = imagejpeg($destino, null, $qualidade);
        $dados = ob_get_clean();
        imagedestroy($destino);

        return ($ok && $dados !== false && $dados !== '') ? $dados : null;
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

    // -----------------------------------------------------------------
    //  FILTROS E PAGINAÇÃO das listas da administração
    // -----------------------------------------------------------------
    // Como as listas do clube são pequenas (dezenas/centenas de registos),
    // vou buscar tudo à base de dados e filtro/pagino aqui em PHP.
    // Assim uso EXATAMENTE o mesmo código em todas as páginas da administração
    // em vez de reescrever SQL diferente para cada tabela.

    // Filtra uma lista de registos conforme o que está no URL ($_GET):
    // - 'q' é a pesquisa por texto: comparo com os campos indicados em $camposTexto
    //   (ex: nome e email), sem ligar a maiúsculas/minúsculas.
    // - $camposExatos são os filtros tipo "dropdown" (ex: tipo=ALUNO): o valor
    //   do registo tem de ser IGUAL ao escolhido. Se estiver vazio, não filtro.
    protected function aplicarFiltros(array $itens, array $camposTexto, array $camposExatos = []): array
    {
        $q = mb_strtolower(trim((string)($_GET['q'] ?? '')));

        return array_values(array_filter($itens, function ($item) use ($q, $camposTexto, $camposExatos) {
            // 1) Pesquisa por texto: basta UM dos campos conter o que se escreveu.
            if ($q !== '') {
                $encontrou = false;
                foreach ($camposTexto as $campo) {
                    if (mb_stripos((string)($item[$campo] ?? ''), $q) !== false) {
                        $encontrou = true;
                        break;
                    }
                }
                if (!$encontrou) {
                    return false; // não bate com a pesquisa → sai da lista
                }
            }

            // 2) Filtros exatos (dropdowns): todos os escolhidos têm de bater certo.
            foreach ($camposExatos as $param => $campo) {
                $valor = trim((string)($_GET[$param] ?? ''));
                if ($valor !== '' && (string)($item[$campo] ?? '') !== $valor) {
                    return false;
                }
            }

            return true; // passou em tudo → fica na lista
        }));
    }

    // Divide a lista em páginas e devolve só a página pedida no URL (?pagina=N).
    // Devolve também a informação que o paginador da view precisa para desenhar
    // os botões: página atual, total de páginas e total de registos.
    protected function paginar(array $itens, int $porPagina = 12): array
    {
        $total        = count($itens);
        $totalPaginas = max(1, (int)ceil($total / $porPagina));

        // Leio a página do URL e prendo-a entre 1 e a última (para não dar asneira
        // se alguém escrever ?pagina=999 ou ?pagina=-3 à mão).
        $pagina = (int)($_GET['pagina'] ?? 1);
        $pagina = max(1, min($pagina, $totalPaginas));

        return [
            'itens'        => array_slice($itens, ($pagina - 1) * $porPagina, $porPagina),
            'pagina'       => $pagina,
            'totalPaginas' => $totalPaginas,
            'total'        => $total,
            'porPagina'    => $porPagina,
        ];
    }

    // Vai buscar os valores DIFERENTES de um campo (ex: todos os tipos de log
    // que existem) para encher as opções de um dropdown de filtro.
    // Assim as opções nascem dos dados reais e nunca ficam desatualizadas.
    protected function opcoesDe(array $itens, string $campo): array
    {
        $valores = array_filter(array_unique(array_map(
            fn($item) => (string)($item[$campo] ?? ''),
            $itens
        )), fn($v) => $v !== '');
        sort($valores);
        return $valores;
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
