<?php
// ---------------------------------------------------------------------------
// BARRA DE FILTROS (partilhada por todas as listas da administração)
// ---------------------------------------------------------------------------
// Este ficheiro é "incluído" pelas views das listas. Antes do include, a view
// define estas variáveis:
//   $filtrosAccao   → para onde o formulário envia (ex: 'admin/utilizadores')
//   $filtrosPlaceholder → texto de ajuda da caixa de pesquisa
//   $filtrosSelects → dropdowns extra, no formato:
//                     ['tipo' => ['label' => 'Tipo', 'opcoes' => ['ALUNO', ...]]]
// O formulário usa GET, por isso os filtros ficam no URL — dá para partilhar
// o link, voltar atrás no browser, e a paginação lembra-se deles.
$filtrosSelects = $filtrosSelects ?? [];
$filtrosPlaceholder = $filtrosPlaceholder ?? 'Pesquisar…';
$temFiltrosAtivos = trim((string)($_GET['q'] ?? '')) !== '';
foreach ($filtrosSelects as $param => $cfg) {
    if (trim((string)($_GET[$param] ?? '')) !== '') {
        $temFiltrosAtivos = true;
    }
}
?>
<style>
    .rb-filtros { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; background: #ffffff; border: 1px solid #f1f5f9; border-radius: 16px; padding: 14px 16px; margin-bottom: 1rem; box-shadow: 0 4px 15px -3px rgba(0,0,0,0.05); }
    .rb-filtros input[type="text"], .rb-filtros select { border: 1px solid #e2e8f0; border-radius: 10px; padding: 9px 14px; font-size: 0.9rem; color: #334155; background: #f8fafc; outline: none; }
    .rb-filtros input[type="text"]:focus, .rb-filtros select:focus { border-color: #2563eb; background: #fff; }
    .rb-filtros input[type="text"] { flex: 1 1 220px; min-width: 180px; }
    .rb-filtros .rb-filtros-btn { background: #2563eb; color: #fff; border: none; border-radius: 10px; padding: 9px 18px; font-weight: 600; font-size: 0.9rem; cursor: pointer; }
    .rb-filtros .rb-filtros-btn:hover { background: #1d4ed8; }
    .rb-filtros .rb-filtros-limpar { color: #64748b; font-size: 0.85rem; text-decoration: none; padding: 9px 10px; }
    .rb-filtros .rb-filtros-limpar:hover { color: #ef4444; }
</style>

<form class="rb-filtros" method="get" action="<?php echo BASE_URL . htmlspecialchars($filtrosAccao); ?>">
    <!-- Caixa de pesquisa por texto (o helper aplicarFiltros() usa o parâmetro "q") -->
    <input type="text" name="q" value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>"
           placeholder="<?php echo htmlspecialchars($filtrosPlaceholder); ?>">

    <!-- Dropdowns extra (tipo de utilizador, tipo de log, estado, etc.) -->
    <?php foreach ($filtrosSelects as $param => $cfg): ?>
        <select name="<?php echo htmlspecialchars($param); ?>" onchange="this.form.submit()">
            <option value=""><?php echo htmlspecialchars($cfg['label']); ?>: todos</option>
            <?php foreach (($cfg['opcoes'] ?? []) as $opcao): ?>
                <option value="<?php echo htmlspecialchars($opcao); ?>"
                    <?php echo (($_GET[$param] ?? '') === (string)$opcao) ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($opcao); ?>
                </option>
            <?php endforeach; ?>
        </select>
    <?php endforeach; ?>

    <button type="submit" class="rb-filtros-btn"><i class="fas fa-search me-1"></i> Filtrar</button>

    <?php if ($temFiltrosAtivos): ?>
        <!-- Só mostro o "limpar" quando há mesmo filtros aplicados -->
        <a class="rb-filtros-limpar" href="<?php echo BASE_URL . htmlspecialchars($filtrosAccao); ?>">
            <i class="fas fa-times me-1"></i>Limpar
        </a>
    <?php endif; ?>
</form>
