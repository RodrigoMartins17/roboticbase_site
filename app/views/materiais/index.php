<?php
// MATERIAIS — inventário em cartões. Clicar num cartão abre um pop-up (modal)
// com a informação do material e o botão de requisitar (como acontece nos eventos).
// FRAGMENTO (moldura vem do header/footer). Recebo $modelos e $itensPorModelo.

// Descubro as categorias existentes para o filtro (sem repetir e ordenadas).
$categorias = [];
foreach ($modelos as $m) {
    foreach (preg_split('/,\s*/', $m['categorias'] ?? 'Geral', -1, PREG_SPLIT_NO_EMPTY) as $c) {
        $categorias[$c] = true;
    }
}
$categorias = array_keys($categorias);
sort($categorias);

// Devolve uma imagem para o material: usa a imagem real da BD se existir;
// senão escolhe uma imagem conforme o tipo (por palavras no nome).
function imagemMaterial($nome, $src = '') {
    if (!empty($src)) return $src; // imagem real da BD
    $n = strtolower($nome);
    $base = BASE_URL . 'uploads/cat/';
    $mapa = [
        'camara'    => 'camera.svg',
        'camera'    => 'camera.svg',
        'arduino'   => 'arduino.svg',
        'raspberry' => 'raspberry.svg',
        'sensor'    => 'sensor.svg',
        'esp32'     => 'esp32.svg',
        'motor'     => 'motor.svg',
        'ferro'     => 'soldar.svg',
        'soldar'    => 'soldar.svg',
    ];
    foreach ($mapa as $palavra => $ficheiro) {
        if (strpos($n, $palavra) !== false) return $base . $ficheiro;
    }
    return $base . 'material.svg'; // imagem por defeito (guardada no projeto)
}
?>

<div class="container">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;">
        <div>
            <span class="eyebrow"><i class="fas fa-microchip"></i> Inventário</span>
            <h1 class="page-title">Materiais</h1>
            <p class="page-sub"><?php echo count($modelos); ?> materiais disponíveis. Clica num para ver os detalhes.</p>
        </div>
        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>requisicaoMaterial/index"><i class="fas fa-clipboard-list"></i> As minhas requisições</a>
    </div>

    <!-- Barra de pesquisa e filtro -->
    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__body" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
            <div style="position:relative;flex:1;min-width:220px;">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);"></i>
                <input class="input" id="pesquisa" placeholder="Procurar material..." style="padding-left:36px;" onkeyup="filtrar()">
            </div>
            <select class="input" id="filtroCat" style="max-width:220px;" onchange="filtrar()">
                <option value="todos">Todas as categorias</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?php echo htmlspecialchars($c); ?>"><?php echo htmlspecialchars($c); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="input" id="filtroEstado" style="max-width:200px;" onchange="filtrar()">
                <option value="todos">Todos os estados</option>
                <option value="disp">Disponível</option>
                <option value="esgotado">Esgotado</option>
            </select>
        </div>
    </div>

    <!-- Grelha de materiais (cartões clicáveis) -->
    <div class="grid grid--3" id="grelha">
        <?php foreach ($modelos as $m):
            $id = (int)$m['id'];
            $disponiveis = count($itensPorModelo[$id] ?? []);
            $temStock = $disponiveis > 0;
            $cat = $m['categorias'] ?? 'Geral';
            $img = imagemMaterial($m['designacao'] ?? '', $m['imagem_src'] ?? '');
        ?>
            <div class="card glow-card click-card item-material"
                 data-nome="<?php echo htmlspecialchars(strtolower($m['designacao'] ?? '')); ?>"
                 data-cat="<?php echo htmlspecialchars($cat); ?>"
                 data-titulo="<?php echo htmlspecialchars($m['designacao'] ?? 'Material'); ?>"
                 data-desc="<?php echo htmlspecialchars($m['descricao'] ?? 'Sem descrição disponível para este material.'); ?>"
                 data-img="<?php echo htmlspecialchars($img); ?>"
                 data-disp="<?php echo $disponiveis; ?>"
                 data-id="<?php echo $id; ?>"
                 onclick="abrirMaterial(this)">
                <span class="accent-bar"></span>
                <div style="height:170px;background:var(--surface-2);border-radius:var(--r) var(--r) 0 0;overflow:hidden;">
                    <img src="<?php echo htmlspecialchars($img); ?>" alt="<?php echo htmlspecialchars($m['designacao'] ?? ''); ?>" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="card__body">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;">
                        <h3 style="margin:0;font-size:1.05rem;font-weight:700;color:#fff;"><?php echo htmlspecialchars($m['designacao'] ?? 'Material'); ?></h3>
                        <span class="badge <?php echo $temStock ? 'badge-green' : 'badge-red'; ?>" style="flex-shrink:0;">
                            <span class="dot <?php echo $temStock ? 'dot-green' : 'dot-red'; ?>"></span>
                            <?php echo $temStock ? $disponiveis . ' disp.' : 'Esgotado'; ?>
                        </span>
                    </div>
                    <p class="muted" style="font-size:0.82rem;margin:0.35rem 0 0;"><?php echo htmlspecialchars($cat); ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="empty" id="semResultados" style="display:none;">
        <i class="fas fa-box-open"></i>
        <p>Nenhum material corresponde à pesquisa.</p>
    </div>
</div>

<!-- POP-UP com a informação do material -->
<div class="modal-overlay" id="materialModal" onclick="fecharFora(event)">
    <div class="modal-box">
        <button class="modal-box__close" onclick="fecharMaterial()" title="Fechar">&times;</button>
        <div class="modal-box__media" id="mImgWrap"></div>
        <div class="modal-box__body">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;">
                <h3 id="mTitulo">Material</h3>
                <span class="badge" id="mBadge" style="flex-shrink:0;"></span>
            </div>
            <p class="muted" id="mCat" style="font-size:0.85rem;margin:0.3rem 0 0;"></p>
            <p class="modal-box__desc" id="mDesc"></p>
            <a class="btn btn-primary btn-block" id="mBtn" href="#"><i class="fas fa-plus"></i> Requisitar</a>
        </div>
    </div>
</div>

<script>
    // Pesquisa e filtro por categoria (no browser).
    function filtrar() {
        var texto = document.getElementById('pesquisa').value.toLowerCase().trim();
        var cat = document.getElementById('filtroCat').value;
        var estado = document.getElementById('filtroEstado').value;
        var visiveis = 0;
        document.querySelectorAll('.item-material').forEach(function (c) {
            var disponivel = parseInt(c.dataset.disp, 10) > 0;
            var okEstado = (estado === 'todos') || (estado === 'disp' && disponivel) || (estado === 'esgotado' && !disponivel);
            var ok = c.dataset.nome.includes(texto) && (cat === 'todos' || c.dataset.cat.includes(cat)) && okEstado;
            c.style.display = ok ? '' : 'none';
            if (ok) visiveis++;
        });
        document.getElementById('semResultados').style.display = visiveis === 0 ? 'block' : 'none';
    }

    // Abre o pop-up com a informação do material clicado.
    function abrirMaterial(card) {
        var disp = parseInt(card.dataset.disp, 10);
        var img = card.dataset.img;

        document.getElementById('mTitulo').innerText = card.dataset.titulo;
        document.getElementById('mCat').innerText = card.dataset.cat;
        document.getElementById('mDesc').innerText = card.dataset.desc;

        // Imagem (ou ícone se não houver).
        document.getElementById('mImgWrap').innerHTML = img
            ? '<img src="' + img + '" alt="">'
            : '<i class="fas fa-microchip"></i>';

        // Etiqueta de disponibilidade.
        var badge = document.getElementById('mBadge');
        if (disp > 0) {
            badge.className = 'badge badge-green';
            badge.innerHTML = '<span class="dot dot-green"></span> ' + disp + ' disponíveis';
        } else {
            badge.className = 'badge badge-red';
            badge.innerHTML = '<span class="dot dot-red"></span> Esgotado';
        }

        // Botão de requisitar (só se houver stock).
        var btn = document.getElementById('mBtn');
        if (disp > 0) {
            btn.style.display = 'flex';
            btn.href = '<?php echo BASE_URL; ?>requisicaoMaterial/criar?material_id=' + card.dataset.id;
        } else {
            btn.style.display = 'none';
        }

        document.getElementById('materialModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function fecharMaterial() {
        document.getElementById('materialModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    function fecharFora(e) {
        if (e.target === document.getElementById('materialModal')) fecharMaterial();
    }

    // Efeito "spotlight": o brilho segue o rato dentro de cada cartão.
    document.querySelectorAll('.glow-card').forEach(function (c) {
        c.addEventListener('mousemove', function (e) {
            var r = c.getBoundingClientRect();
            c.style.setProperty('--mx', (e.clientX - r.left) + 'px');
            c.style.setProperty('--my', (e.clientY - r.top) + 'px');
        });
    });
</script>
