-- ============================================================
-- MIGRAÇÃO: fluxo "entregue ao aluno" e "devolvido ao clube"
-- Corre isto UMA vez na tua base de dados (phpMyAdmin > SQL) para
-- adicionares os estados EM_USO / CONCLUIDO sem perderes os dados.
-- (XAMPP usa MariaDB, por isso o "IF NOT EXISTS" funciona.)
-- ============================================================

USE `clube_robotica`;

-- Materiais: acrescenta o estado EM_USO ao ciclo do pedido.
ALTER TABLE `requisicao_exemplar`
    MODIFY `estado_pedido` ENUM('PENDENTE','ACEITE','EM_USO','REJEITADO','CONCLUIDO') NOT NULL DEFAULT 'PENDENTE';

ALTER TABLE `requisicao_exemplar`
    ADD COLUMN IF NOT EXISTS `estado_entrega` TINYINT(1) NOT NULL DEFAULT 0;

-- Salas: acrescenta EM_USO e CONCLUIDO (antes só tinha PENDENTE/ACEITE/REJEITADO).
ALTER TABLE `requisicao`
    MODIFY `estado_sala` ENUM('PENDENTE','ACEITE','EM_USO','REJEITADO','CONCLUIDO') NULL DEFAULT 'PENDENTE';

ALTER TABLE `requisicao`
    ADD COLUMN IF NOT EXISTS `estado_entrega` TINYINT(1) NOT NULL DEFAULT 0;
