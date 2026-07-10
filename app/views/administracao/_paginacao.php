<?php
// ---------------------------------------------------------------------------
// PAGINADOR (partilhado por todas as listas da administração)
// ---------------------------------------------------------------------------
// A view inclui este ficheiro por baixo da tabela e passa-lhe a variável
// $paginacao (que vem do helper paginar() do Controller):
//   'pagina' → página atual   'totalPaginas' → nº de páginas   'total' → nº registos
// Os links mantêm os filtros que estão no URL — mudo SÓ o parâmetro "pagina",
// para a pessoa não perder a pesquisa quando muda de página.
// Função que constrói URLs mantendo os filtros atuais mas trocando um parâmetro.
$urlCom = function (array $trocas): string {
    $params = $_GET;
    foreach ($trocas as $k => $v) {
        if ($v === null) { unset($params[$k]); } else { $params[$k] = $v; }
    }
    unset($params['url']); // parâmetro interno do Router, não vai no link
    $q = http_build_query($params);
    return $q === '' ? '?' : '?' . $q;
};

// Modo "ver todos" ativo: mostro o total e o botão para voltar à paginação.
if (!empty($paginacao['verTodos'])) {
    echo '<div style="display:flex;align-items:center;gap:12px;color:#94a3b8;font-size:0.85rem;padding:12px 4px;">'
        . 'A mostrar todos os ' . (int)$paginacao['total'] . ' registo(s)'
        . '<a href="' . htmlspecialchars($urlCom(['todos' => null, 'pagina' => null])) . '" style="color:#2563eb;font-weight:600;text-decoration:none;">Ver paginado</a>'
        . '</div>';
    return;
}

if (empty($paginacao) || $paginacao['totalPaginas'] <= 1) {
    // Com uma página só não vale a pena desenhar botões — mostro apenas o total.
    if (!empty($paginacao) && $paginacao['total'] > 0) {
        echo '<div style="color:#94a3b8;font-size:0.85rem;padding:12px 4px;">'
            . (int)$paginacao['total'] . ' registo(s)</div>';
    }
    return;
}

// Função pequenina que constrói o URL de uma página mantendo os filtros atuais.
$urlPagina = function (int $n): string {
    $params = $_GET;              // copio os filtros que já lá estão (q, tipo, ...)
    $params['pagina'] = $n;       // e troco só o número da página
    unset($params['url']);        // o "url" é o parâmetro interno do Router, não vai no link
    return '?' . http_build_query($params);
};

$atual = (int)$paginacao['pagina'];
$ultima = (int)$paginacao['totalPaginas'];

// Para não desenhar 50 botões, mostro só uma "janela" de 2 páginas para cada
// lado da atual (1 … 4 5 [6] 7 8 … 20).
$inicio = max(1, $atual - 2);
$fim    = min($ultima, $atual + 2);
?>
<style>
    .rb-pag { display: flex; flex-wrap: wrap; align-items: center; gap: 6px; padding: 14px 4px; }
    .rb-pag a, .rb-pag span.rb-pag-atual, .rb-pag span.rb-pag-gap { min-width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; font-size: 0.9rem; font-weight: 600; text-decoration: none; padding: 0 10px; }
    .rb-pag a { background: #fff; border: 1px solid #e2e8f0; color: #334155; }
    .rb-pag a:hover { border-color: #2563eb; color: #2563eb; }
    .rb-pag span.rb-pag-atual { background: #2563eb; color: #fff; }
    .rb-pag span.rb-pag-gap { color: #94a3b8; }
    .rb-pag .rb-pag-info { margin-left: auto; color: #94a3b8; font-size: 0.85rem; }
</style>

<nav class="rb-pag" aria-label="Paginação">
    <?php if ($atual > 1): ?>
        <a href="<?php echo $urlPagina($atual - 1); ?>" aria-label="Página anterior">&laquo;</a>
    <?php endif; ?>

    <?php if ($inicio > 1): ?>
        <a href="<?php echo $urlPagina(1); ?>">1</a>
        <?php if ($inicio > 2): ?><span class="rb-pag-gap">…</span><?php endif; ?>
    <?php endif; ?>

    <?php for ($n = $inicio; $n <= $fim; $n++): ?>
        <?php if ($n === $atual): ?>
            <span class="rb-pag-atual"><?php echo $n; ?></span>
        <?php else: ?>
            <a href="<?php echo $urlPagina($n); ?>"><?php echo $n; ?></a>
        <?php endif; ?>
    <?php endfor; ?>

    <?php if ($fim < $ultima): ?>
        <?php if ($fim < $ultima - 1): ?><span class="rb-pag-gap">…</span><?php endif; ?>
        <a href="<?php echo $urlPagina($ultima); ?>"><?php echo $ultima; ?></a>
    <?php endif; ?>

    <?php if ($atual < $ultima): ?>
        <a href="<?php echo $urlPagina($atual + 1); ?>" aria-label="Página seguinte">&raquo;</a>
    <?php endif; ?>

    <span class="rb-pag-info">
        Página <?php echo $atual; ?> de <?php echo $ultima; ?> · <?php echo (int)$paginacao['total']; ?> registo(s)
        &nbsp;·&nbsp;
        <!-- Desliga a paginação e mostra a lista inteira -->
        <a href="<?php echo htmlspecialchars($urlCom(['todos' => 1, 'pagina' => null])); ?>" style="color:#2563eb;font-weight:600;text-decoration:none;">Ver todos</a>
    </span>
</nav>
