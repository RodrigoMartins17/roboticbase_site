<?php
// SALAS — cartões clicáveis que abrem um pop-up com a informação da sala e o
// botão de reservar (como nos eventos). FRAGMENTO (moldura vem do header/footer).
// Só professores/admin podem reservar (os alunos não).
$podeReservar = !Auth::isAluno();

$blocos = array_values(array_unique(array_filter(array_map(fn($s) => $s['bloco'] ?? '', $salas))));
sort($blocos);

// Escolhe uma imagem para a sala conforme a descrição (laboratório, auditório, etc.).
function imagemSala($descricao) {
    $d = strtolower($descricao);
    $base = BASE_URL . 'uploads/cat/';
    $mapa = [
        'auditorio'   => 'auditorio.svg',
        'laboratorio' => 'laboratorio.svg',
        'informatica' => 'informatica.svg',
        'redes'       => 'redes.svg',
        'gravacao'    => 'estudio.svg',
        'estudio'     => 'estudio.svg',
        'oficina'     => 'oficina.svg',
        'robotica'    => 'robotica.svg',
        'reunio'      => 'reunioes.svg',
        'arrecad'     => 'arrecadacao.svg',
    ];
    foreach ($mapa as $palavra => $ficheiro) {
        if (strpos($d, $palavra) !== false) return $base . $ficheiro;
    }
    return $base . 'sala.svg'; // imagem por defeito (guardada no projeto)
}
?>

<div class="container">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;">
        <div>
            <span class="eyebrow"><i class="fas fa-door-open"></i> Espaços</span>
            <h1 class="page-title">Salas</h1>
            <p class="page-sub"><?php echo count($salas); ?> salas no clube. Clica numa para ver os detalhes.</p>
        </div>
        <a class="btn btn-outline" href="<?php echo BASE_URL; ?>requisicaoSala/index"><i class="fas fa-clipboard-list"></i> As minhas requisições</a>
    </div>

    <div class="card" style="margin-bottom:1.5rem;">
        <div class="card__body" style="display:flex;gap:0.75rem;flex-wrap:wrap;align-items:center;">
            <div style="position:relative;flex:1;min-width:220px;">
                <i class="fas fa-search" style="position:absolute;left:12px;top:50%;transform:translateY(-50%);color:var(--text-dim);"></i>
                <input class="input" id="pesquisa" placeholder="Procurar sala..." style="padding-left:36px;" onkeyup="filtrar()">
            </div>
            <select class="input" id="filtroBloco" style="max-width:200px;" onchange="filtrar()">
                <option value="todos">Todos os blocos</option>
                <?php foreach ($blocos as $bl): ?>
                    <option value="<?php echo htmlspecialchars($bl); ?>">Bloco <?php echo htmlspecialchars($bl); ?></option>
                <?php endforeach; ?>
            </select>
            <select class="input" id="filtroEstado" style="max-width:200px;" onchange="filtrar()">
                <option value="todos">Todos os estados</option>
                <option value="disp">Disponível</option>
                <option value="indisp">Indisponível</option>
            </select>
        </div>
    </div>

    <div class="grid grid--3" id="grelha">
        <?php foreach ($salas as $s):
            // Nome da sala no formato BLOCO+ANDAR.NÚMERO (ex: A1.02).
            $nome = ($s['bloco'] ?? '') . ($s['andar'] ?? '') . '.' . ($s['numero'] ?? '');
            // Se a sala tem fotografia na base de dados uso-a; senão mantém-se o
            // desenho por tema (laboratório, auditório...) que já existia.
            $imgSala = !empty($s['imagem'])
                ? 'data:image/jpeg;base64,' . base64_encode($s['imagem'])
                : imagemSala($s['descricao'] ?? '');
            // A sala está "disponível" quando o estado é DISPONIVEL (senão está em
            // manutenção, ocupada ou danificada = indisponível).
            $estadoSala = $s['estado'] ?? 'DISPONIVEL';
            $disponivel = ($estadoSala === 'DISPONIVEL');
        ?>
            <div class="card glow-card click-card item-sala"
                 data-nome="<?php echo htmlspecialchars(strtolower($nome . ' ' . ($s['descricao'] ?? ''))); ?>"
                 data-bloco="<?php echo htmlspecialchars($s['bloco'] ?? ''); ?>"
                 data-estado="<?php echo $disponivel ? 'disp' : 'indisp'; ?>"
                 data-titulo="<?php echo htmlspecialchars($nome); ?>"
                 data-desc="<?php echo htmlspecialchars($s['descricao'] ?? 'Sala do clube.'); ?>"
                 data-andar="<?php echo htmlspecialchars((string)($s['andar'] ?? '—')); ?>"
                 data-cap="<?php echo htmlspecialchars((string)($s['capacidade'] ?? '—')); ?>"
                 data-img="<?php echo htmlspecialchars($imgSala); ?>"
                 data-id="<?php echo (int)$s['id']; ?>"
                 onclick="abrirSala(this)">
                <span class="accent-bar"></span>
                <!-- Imagem da sala em cima (como nos materiais) -->
                <div style="height:170px;background:var(--surface-2);border-radius:var(--r) var(--r) 0 0;overflow:hidden;">
                    <img src="<?php echo htmlspecialchars($imgSala); ?>" alt="<?php echo htmlspecialchars($nome); ?>" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <div class="card__body">
                    <div style="display:flex;justify-content:space-between;align-items:flex-start;gap:0.5rem;">
                        <h3 style="margin:0;font-size:1.05rem;font-weight:700;color:#fff;"><?php echo htmlspecialchars($nome); ?></h3>
                        <span class="badge <?php echo $disponivel ? 'badge-green' : 'badge-red'; ?>" style="flex-shrink:0;">
                            <span class="dot <?php echo $disponivel ? 'dot-green' : 'dot-red'; ?>"></span>
                            <?php echo $disponivel ? 'Disponível' : 'Indisponível'; ?>
                        </span>
                    </div>
                    <div style="display:flex;justify-content:space-between;align-items:center;gap:0.5rem;margin-top:0.4rem;">
                        <p class="muted" style="font-size:0.82rem;margin:0;"><?php echo htmlspecialchars($s['descricao'] ?? 'Sala do clube'); ?></p>
                        <span class="badge badge-gray" style="flex-shrink:0;"><i class="fas fa-users"></i> <?php echo htmlspecialchars((string)($s['capacidade'] ?? '—')); ?> lug.</span>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="empty" id="semResultados" style="display:none;">
        <i class="fas fa-door-closed"></i>
        <p>Nenhuma sala corresponde à pesquisa.</p>
    </div>
</div>

<!-- POP-UP com a informação da sala -->
<div class="modal-overlay" id="salaModal" onclick="fecharFora(event)">
    <div class="modal-box">
        <button class="modal-box__close" onclick="fecharSala()" title="Fechar">&times;</button>
        <div class="modal-box__media"><img id="sImg" src="" alt="" style="width:100%;height:100%;object-fit:cover;"></div>
        <div class="modal-box__body">
            <h3 id="sTitulo">Sala</h3>
            <p class="modal-box__desc" id="sDesc"></p>
            <div style="display:flex;gap:0.5rem;flex-wrap:wrap;margin-bottom:1.25rem;">
                <span class="badge badge-gray"><i class="fas fa-layer-group"></i> Andar <span id="sAndar"></span></span>
                <span class="badge badge-gray"><i class="fas fa-users"></i> <span id="sCap"></span> lugares</span>
            </div>
            <?php if ($podeReservar): ?>
                <a class="btn btn-primary btn-block" id="sBtn" href="#"><i class="fas fa-calendar-plus"></i> Reservar</a>
            <?php else: ?>
                <div class="alert alert-info" style="margin:0;"><i class="fas fa-info-circle"></i> Só professores e administradores podem reservar salas.</div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Pesquisa e filtro por bloco (no browser).
    function filtrar() {
        var texto = document.getElementById('pesquisa').value.toLowerCase().trim();
        var bloco = document.getElementById('filtroBloco').value;
        var estado = document.getElementById('filtroEstado').value;
        var visiveis = 0;
        document.querySelectorAll('.item-sala').forEach(function (c) {
            var okEstado = (estado === 'todos') || (c.dataset.estado === estado);
            var ok = c.dataset.nome.includes(texto) && (bloco === 'todos' || c.dataset.bloco === bloco) && okEstado;
            c.style.display = ok ? '' : 'none';
            if (ok) visiveis++;
        });
        document.getElementById('semResultados').style.display = visiveis === 0 ? 'block' : 'none';
    }

    // Abre o pop-up com a informação da sala clicada.
    function abrirSala(card) {
        document.getElementById('sImg').src = card.dataset.img || '';
        document.getElementById('sTitulo').innerText = card.dataset.titulo;
        document.getElementById('sDesc').innerText = card.dataset.desc;
        document.getElementById('sAndar').innerText = card.dataset.andar;
        document.getElementById('sCap').innerText = card.dataset.cap;
        var btn = document.getElementById('sBtn');
        if (btn) btn.href = '<?php echo BASE_URL; ?>requisicaoSala/criar?sala_id=' + card.dataset.id;
        document.getElementById('salaModal').classList.add('active');
        document.body.style.overflow = 'hidden';
    }
    function fecharSala() {
        document.getElementById('salaModal').classList.remove('active');
        document.body.style.overflow = 'auto';
    }
    function fecharFora(e) {
        if (e.target === document.getElementById('salaModal')) fecharSala();
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
