<?php
// ============================================================================
//  AVISOS 24h ANTES — correr periodicamente (idealmente de hora a hora).
//  ----------------------------------------------------------------------------
//  Envia um email ao utilizador quando faltam ~24h para:
//    - ir BUSCAR o material/sala (data de levantamento / início da reserva)
//    - DEVOLVER o material/sala (data de devolução / fim da reserva)
//  Guarda o que já foi enviado (tabela aviso_requisicao) para não repetir.
//
//  Como correr:
//    - Na Consola/Task Scheduler:  php cron_avisos.php
//    - No browser (para testar):   http://localhost/roboticbase_site/public/cron_avisos.php?key=roboticaxl
// ============================================================================

require_once __DIR__ . '/../app/config/config.php';
require_once __DIR__ . '/../app/core/Mailer.php';

// Chave simples para o browser não deixar qualquer pessoa disparar emails.
// (Na linha de comandos/Task Scheduler não é preciso chave.)
$CHAVE = 'roboticaxl';
$viaCLI = (php_sapi_name() === 'cli');
if (!$viaCLI && (($_GET['key'] ?? '') !== $CHAVE)) {
    http_response_code(403);
    exit('Acesso negado.');
}

header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO(
        'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
        DB_USER, DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (Exception $e) {
    exit('Erro de ligação à BD: ' . $e->getMessage());
}

// Tabela para não enviar o mesmo aviso duas vezes (criada automaticamente).
$pdo->exec("CREATE TABLE IF NOT EXISTS `aviso_requisicao` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tipo_req` VARCHAR(10) NOT NULL,
    `id_req` INT NOT NULL,
    `tipo_aviso` VARCHAR(20) NOT NULL,
    `enviado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY `uq_aviso` (`tipo_req`,`id_req`,`tipo_aviso`)
)");

// As 4 situações a verificar. Cada uma devolve: id, nome, email, item, quando.
$consultas = [
    // Material — ir buscar (levantamento)
    ['material', 'LEVANTAMENTO',
     "SELECT re.id, u.nome, u.email, m.designacao AS item, re.data_levantamento AS quando
      FROM requisicao_exemplar re
      JOIN utilizador u ON u.id = re.id_utilizador
      JOIN exemplar e   ON e.id = re.id_exemplar
      JOIN material m   ON m.id = e.id_material
      LEFT JOIN aviso_requisicao a ON a.tipo_req='material' AND a.id_req=re.id AND a.tipo_aviso='LEVANTAMENTO'
      WHERE re.estado_pedido='ACEITE' AND re.data_levantamento IS NOT NULL
        AND re.data_levantamento > NOW() AND re.data_levantamento <= NOW() + INTERVAL 24 HOUR
        AND a.id IS NULL"],
    // Material — devolver (devolução)
    ['material', 'DEVOLUCAO',
     "SELECT re.id, u.nome, u.email, m.designacao AS item, re.data_devolucao AS quando
      FROM requisicao_exemplar re
      JOIN utilizador u ON u.id = re.id_utilizador
      JOIN exemplar e   ON e.id = re.id_exemplar
      JOIN material m   ON m.id = e.id_material
      LEFT JOIN aviso_requisicao a ON a.tipo_req='material' AND a.id_req=re.id AND a.tipo_aviso='DEVOLUCAO'
      WHERE re.estado_pedido='EM_USO' AND re.data_devolucao IS NOT NULL
        AND re.data_devolucao > NOW() AND re.data_devolucao <= NOW() + INTERVAL 24 HOUR
        AND a.id IS NULL"],
    // Sala — ir buscar (início da reserva)
    ['sala', 'LEVANTAMENTO',
     "SELECT rs.id, u.nome, u.email, CONCAT(s.bloco, s.andar, '.', LPAD(s.numero,2,'0')) AS item, rs.data_inicio AS quando
      FROM requisicao_sala rs
      JOIN utilizador u ON u.id = rs.id_utilizador
      JOIN sala s       ON s.id = rs.id_sala
      LEFT JOIN aviso_requisicao a ON a.tipo_req='sala' AND a.id_req=rs.id AND a.tipo_aviso='LEVANTAMENTO'
      WHERE rs.estado_sala='ACEITE' AND rs.data_inicio IS NOT NULL
        AND rs.data_inicio > NOW() AND rs.data_inicio <= NOW() + INTERVAL 24 HOUR
        AND a.id IS NULL"],
    // Sala — devolver (fim da reserva)
    ['sala', 'DEVOLUCAO',
     "SELECT rs.id, u.nome, u.email, CONCAT(s.bloco, s.andar, '.', LPAD(s.numero,2,'0')) AS item, rs.data_fim AS quando
      FROM requisicao_sala rs
      JOIN utilizador u ON u.id = rs.id_utilizador
      JOIN sala s       ON s.id = rs.id_sala
      LEFT JOIN aviso_requisicao a ON a.tipo_req='sala' AND a.id_req=rs.id AND a.tipo_aviso='DEVOLUCAO'
      WHERE rs.estado_sala='EM_USO' AND rs.data_fim IS NOT NULL
        AND rs.data_fim > NOW() AND rs.data_fim <= NOW() + INTERVAL 24 HOUR
        AND a.id IS NULL"],
];

$marcar = $pdo->prepare("INSERT IGNORE INTO aviso_requisicao (tipo_req, id_req, tipo_aviso) VALUES (?, ?, ?)");
$enviados = 0;
$falhas = 0;

foreach ($consultas as [$tipoReq, $tipoAviso, $sql]) {
    foreach ($pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC) as $r) {
        if (empty($r['email'])) { continue; }
        $ok = false;
        try {
            $ok = Mailer::sendWarning12h($r['email'], $r['nome'], $tipoReq, (int)$r['id'], $tipoAviso, $r['quando'], $r['item']);
        } catch (\Throwable $e) { $ok = false; }

        if ($ok) {
            $marcar->execute([$tipoReq, (int)$r['id'], $tipoAviso]);
            $enviados++;
            echo "[OK] {$tipoReq} #{$r['id']} {$tipoAviso} -> {$r['email']} ({$r['item']} @ {$r['quando']})\n";
        } else {
            $falhas++;
            echo "[FALHOU] {$tipoReq} #{$r['id']} {$tipoAviso} -> {$r['email']}\n";
        }
    }
}

echo "\nConcluído. Avisos enviados: {$enviados}. Falhas: {$falhas}.\n";
