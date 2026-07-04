<?php
// CALENDÁRIO (vista semanal). FRAGMENTO (moldura vem do header/footer).
// Recebo $eventos (lista de pedidos de material e sala já preparada no controller).
// Passo esses eventos para o JavaScript, que desenha a semana e coloca cada evento
// no dia certo. Botões para andar entre semanas.
$eventos = $eventos ?? [];
?>

<div class="container">
    <div class="page-header" style="display:flex;justify-content:space-between;align-items:flex-end;flex-wrap:wrap;gap:1rem;">
        <div>
            <span class="eyebrow"><i class="fas fa-calendar-days"></i> Agenda</span>
            <h1 class="page-title">Calendário</h1>
        </div>
        <div style="display:flex;align-items:center;gap:0.5rem;">
            <button class="btn btn-outline" onclick="mudarSemana(-1)"><i class="fas fa-chevron-left"></i></button>
            <button class="btn btn-outline" onclick="irHoje()">Hoje</button>
            <button class="btn btn-outline" onclick="mudarSemana(1)"><i class="fas fa-chevron-right"></i></button>
        </div>
    </div>

    <div class="card" style="margin-bottom:1rem;">
        <div class="card__body" style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:0.75rem;">
            <strong id="rangeSemana" style="font-size:1.05rem;color:#fff;"></strong>
            <div style="display:flex;gap:1rem;font-size:0.82rem;" class="muted">
                <span><span class="dot" style="background:var(--blue);"></span> Material</span>
                <span><span class="dot" style="background:#a855f7;"></span> Sala</span>
            </div>
        </div>
    </div>

    <!-- Grelha da semana (7 colunas) -->
    <div id="grelhaSemana" style="display:grid;grid-template-columns:repeat(7,1fr);gap:0.6rem;"></div>

    <div class="empty" id="semEventos" style="display:none;margin-top:1rem;">
        <i class="fas fa-calendar-xmark"></i><p>Sem pedidos nesta semana.</p>
    </div>
</div>

<style>
    .cal-day { background:var(--surface); border:1px solid var(--border); border-radius:var(--r); overflow:hidden; min-height:120px; }
    .cal-day.hoje { border-color:var(--blue); box-shadow:0 0 0 1px var(--blue) inset; }
    .cal-day__head { padding:0.5rem; text-align:center; border-bottom:1px solid var(--border); background:var(--surface-2); }
    .cal-day__name { font-size:0.7rem; text-transform:uppercase; letter-spacing:0.05em; color:var(--text-dim); font-weight:700; }
    .cal-day__num { font-size:1.15rem; font-weight:800; color:#fff; }
    .cal-day__body { padding:0.5rem; display:flex; flex-direction:column; gap:0.4rem; }
    .cal-ev { border-radius:8px; padding:0.4rem 0.5rem; font-size:0.75rem; color:#fff; border-left:3px solid rgba(255,255,255,0.4); }
    .cal-ev.material { background:rgba(59,130,246,0.18); border-left-color:var(--blue); }
    .cal-ev.sala { background:rgba(168,85,247,0.18); border-left-color:#a855f7; }
    .cal-ev .h { font-weight:700; }
    .cal-ev .t { display:block; opacity:0.9; }
    @media (max-width: 800px) { #grelhaSemana { grid-template-columns:1fr !important; } }
</style>

<script>
    // Eventos vindos do PHP (material + sala).
    var EVENTOS = <?php echo json_encode($eventos, JSON_UNESCAPED_UNICODE); ?>;
    var BASE = '<?php echo BASE_URL; ?>';
    var DIAS = ['Dom','Seg','Ter','Qua','Qui','Sex','Sáb'];
    var MESES = ['jan','fev','mar','abr','mai','jun','jul','ago','set','out','nov','dez'];
    var refDate = new Date();

    // Devolve a segunda-feira da semana da data dada.
    function segundaDe(d) {
        var x = new Date(d);
        var dia = x.getDay();
        var diff = x.getDate() - dia + (dia === 0 ? -6 : 1);
        return new Date(x.setDate(diff));
    }
    function fmtDia(d) { return d.getFullYear() + '-' + String(d.getMonth()+1).padStart(2,'0') + '-' + String(d.getDate()).padStart(2,'0'); }

    function desenhar() {
        var seg = segundaDe(refDate);
        var grelha = document.getElementById('grelhaSemana');
        grelha.innerHTML = '';
        var hojeStr = fmtDia(new Date());
        var totalSemana = 0;

        for (var i = 0; i < 7; i++) {
            var dia = new Date(seg); dia.setDate(seg.getDate() + i);
            var diaStr = fmtDia(dia);

            // Eventos deste dia, ordenados por hora.
            var doDia = EVENTOS.filter(function(e){ return (e.data || '').substring(0,10) === diaStr; })
                               .sort(function(a,b){ return (a.hora||'').localeCompare(b.hora||''); });
            totalSemana += doDia.length;

            var chips = doDia.map(function(e){
                var tipo = (e.tipo_req === 'sala') ? 'sala' : 'material';
                // Cada evento leva à página de acompanhamento (detalhe) da requisição.
                var url = BASE + (tipo === 'sala' ? 'requisicaoSala/detalhe/' : 'requisicaoMaterial/detalhe/') + (e.id_req || '');
                return '<a href="' + url + '" class="cal-ev ' + tipo + '" style="display:block;text-decoration:none;">'
                     + '<span class="h">' + (e.hora || '') + '</span> '
                     + '<span class="t">' + (e.titulo || '') + '</span></a>';
            }).join('');

            grelha.innerHTML +=
                '<div class="cal-day' + (diaStr === hojeStr ? ' hoje' : '') + '">'
              + '  <div class="cal-day__head"><div class="cal-day__name">' + DIAS[dia.getDay()] + '</div>'
              + '  <div class="cal-day__num">' + dia.getDate() + '</div></div>'
              + '  <div class="cal-day__body">' + chips + '</div>'
              + '</div>';
        }

        // Texto do intervalo (ex: 1 – 7 jul).
        var fim = new Date(seg); fim.setDate(seg.getDate() + 6);
        document.getElementById('rangeSemana').innerText =
            seg.getDate() + ' ' + MESES[seg.getMonth()] + ' – ' + fim.getDate() + ' ' + MESES[fim.getMonth()] + ' ' + fim.getFullYear();
        document.getElementById('semEventos').style.display = totalSemana === 0 ? 'block' : 'none';
    }

    function mudarSemana(n) { refDate.setDate(refDate.getDate() + n*7); desenhar(); }
    function irHoje() { refDate = new Date(); desenhar(); }
    desenhar();
</script>
