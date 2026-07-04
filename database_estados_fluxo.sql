-- ============================================================================
--  CLUBE DE ROBÓTICA — Estados do fluxo de requisições (material e sala)
--  ----------------------------------------------------------------------------
--  Fluxo:  PENDENTE -> ACEITE -> EM_USO (entregue/buscar) -> CONCLUIDO (devolvido)
--          (e REJEITADO quando o pedido é recusado)
--
--  Este script é SEGURO: só ajusta a estrutura das colunas de estado.
--  NÃO apaga nem altera os dados existentes.
--  Corre-o uma vez no phpMyAdmin (separador SQL) ou no MySQL/MariaDB.
--  Feito para XAMPP (MariaDB), por isso o "IF NOT EXISTS" nas colunas funciona.
-- ============================================================================

USE `clube_robotica`;


-- ----------------------------------------------------------------------------
-- 1) MATERIAIS  (tabela: requisicao_exemplar)
-- ----------------------------------------------------------------------------

-- Estado do pedido de material com os 5 estados do ciclo.
ALTER TABLE `requisicao_exemplar`
    MODIFY `estado_pedido`
    ENUM('PENDENTE','ACEITE','EM_USO','REJEITADO','CONCLUIDO')
    NOT NULL DEFAULT 'PENDENTE';

-- Marca (0/1) se o material já foi levantado pelo aluno. 0 = ainda não; 1 = sim.
ALTER TABLE `requisicao_exemplar`
    ADD COLUMN IF NOT EXISTS `estado_entrega` TINYINT(1) NOT NULL DEFAULT 0;


-- ----------------------------------------------------------------------------
-- 2) SALAS  (tabela: requisicao)
-- ----------------------------------------------------------------------------

-- Estado da reserva de sala com os 5 estados do ciclo.
ALTER TABLE `requisicao`
    MODIFY `estado_sala`
    ENUM('PENDENTE','ACEITE','EM_USO','REJEITADO','CONCLUIDO')
    NULL DEFAULT 'PENDENTE';

-- Marca (0/1) se a sala já foi entregue (check-in). 0 = ainda não; 1 = sim.
ALTER TABLE `requisicao`
    ADD COLUMN IF NOT EXISTS `estado_entrega` TINYINT(1) NOT NULL DEFAULT 0;


-- ----------------------------------------------------------------------------
-- 3) (OPCIONAL) Coerência dos dados já existentes
--    Se um material/sala tiver data de devolução preenchida mas o estado ainda
--    não for CONCLUIDO, marca-o como concluído. Comenta estas linhas se não
--    quiseres tocar nos registos antigos.
-- ----------------------------------------------------------------------------

UPDATE `requisicao_exemplar`
   SET `estado_pedido` = 'CONCLUIDO'
 WHERE `data_devolucao` IS NOT NULL
   AND `estado_pedido` IN ('ACEITE','EM_USO');

-- ============================================================================
--  Fim. Depois de correr isto, o painel de administração já grava e mostra:
--  Pendente / Aceite / Entregue (Buscar) / Devolvido / Rejeitado.
-- ============================================================================
