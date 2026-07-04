-- ============================================================================
--  DADOS DE EXEMPLO — Junho e Julho de 2026
--  ----------------------------------------------------------------------------
--  Requisições de MATERIAL e de SALA espalhadas pelos dois meses, com todos os
--  estados do fluxo (PENDENTE, ACEITE, EM_USO, CONCLUIDO, REJEITADO) para o
--  calendário do admin ficar com conteúdo em ambos os meses.
--
--  Como usar: corre este script DEPOIS de teres a base de dados importada.
--  Não apaga nada — só acrescenta linhas novas (o id é automático).
--  Respeita as regras dos triggers (levantamento >= pedido, devolução >=
--  levantamento, fim > início).
-- ============================================================================

USE `clube_robotica`;

-- ----------------------------------------------------------------------------
-- REQUISIÇÕES DE MATERIAL  (requisicao_exemplar)
--   colunas: id_utilizador, id_exemplar, data_pedido, estado_pedido,
--            data_levantamento, data_devolucao, estado_devolucao,
--            estado_entrega, observacao
-- ----------------------------------------------------------------------------
INSERT INTO `requisicao_exemplar`
    (`id_utilizador`,`id_exemplar`,`data_pedido`,`estado_pedido`,`data_levantamento`,`data_devolucao`,`estado_devolucao`,`estado_entrega`,`observacao`) VALUES
-- ---- JUNHO: concluídos (devolvidos) ----
(4,  1, '2026-06-03 09:00:00','CONCLUIDO','2026-06-03 10:00:00','2026-06-08 16:00:00','OK',        1,'Projeto Arduino da turma 12A'),
(5, 16, '2026-06-05 11:00:00','CONCLUIDO','2026-06-05 14:00:00','2026-06-10 09:30:00','OK',        1,'Sensor para robô seguidor de linha'),
(6, 21, '2026-06-09 08:30:00','CONCLUIDO','2026-06-09 09:00:00','2026-06-12 17:00:00','OK',        1,'Módulo seguidor de linha'),
(7, 46, '2026-06-11 10:00:00','CONCLUIDO','2026-06-11 15:00:00','2026-06-18 10:00:00','DANIFICADO', 1,'Filamento acabou a meio da impressão'),
-- ---- JUNHO: aceite (à espera de levantamento) e rejeitado ----
(8, 27, '2026-06-15 09:00:00','ACEITE',   '2026-06-22 09:00:00', NULL,                'OK',        0,'Câmara para projeto de visão computacional'),
(9, 13, '2026-06-20 14:00:00','REJEITADO', NULL,                 NULL,                'OK',        0,'Aluno com material em atraso'),
-- ---- JUNHO -> JULHO: em uso (entregue, devolução prevista em julho) ----
(10,31, '2026-06-25 10:00:00','EM_USO',   '2026-06-26 10:00:00','2026-07-06 17:00:00','OK',        1,'Servos para braço robótico'),
(11,36, '2026-06-28 11:00:00','EM_USO',   '2026-06-29 09:00:00','2026-07-10 12:00:00','OK',        1,'Motores para carro telecomandado'),
-- ---- JULHO: concluído recente ----
(4,  5, '2026-06-29 09:00:00','CONCLUIDO','2026-06-29 10:00:00','2026-07-01 16:00:00','OK',        1,'Teste rápido de Arduino na aula'),
-- ---- JULHO: aceites (levantamento marcado) ----
(5, 14, '2026-07-01 09:00:00','ACEITE',   '2026-07-07 09:00:00', NULL,                'OK',        0,'ESP32 para projeto de IoT'),
(6, 47, '2026-07-02 10:00:00','ACEITE',   '2026-07-09 10:00:00', NULL,                'OK',        0,'Filamento preto para impressão 3D'),
(9, 22, '2026-07-20 10:00:00','ACEITE',   '2026-07-22 10:00:00', NULL,                'OK',        0,'Sensor de linha para a competição'),
-- ---- JULHO: pendentes (à espera de aprovação) ----
(7, 17, '2026-07-01 15:00:00','PENDENTE',  NULL,                 NULL,                'OK',        0,'Sensor ultrassónico para projeto'),
(8, 56, '2026-07-02 08:30:00','PENDENTE',  NULL,                 NULL,                'OK',        0,'Multímetro para a bancada'),
(12,61, '2026-07-02 13:00:00','PENDENTE',  NULL,                 NULL,                'OK',        0,'Kit LEGO para o workshop'),
(13,42, '2026-07-14 09:00:00','PENDENTE',  NULL,                 NULL,                'OK',        0,'Impressora 3D para a PAP');

-- ----------------------------------------------------------------------------
-- REQUISIÇÕES DE SALA  (requisicao_sala)
--   colunas: id_sala, id_utilizador, data (pedido), estado_sala,
--            data_inicio, data_fim, estado_devolucao, estado_entrega, observacao
-- ----------------------------------------------------------------------------
INSERT INTO `requisicao_sala`
    (`id_sala`,`id_utilizador`,`data`,`estado_sala`,`data_inicio`,`data_fim`,`estado_devolucao`,`estado_entrega`,`observacao`) VALUES
-- ---- JUNHO: concluídas ----
(3, 2, '2026-06-02 09:00:00','CONCLUIDO','2026-06-04 14:00:00','2026-06-04 16:00:00','NORMAL',          1,'Workshop de soldadura'),
(5, 1, '2026-06-08 10:00:00','CONCLUIDO','2026-06-10 09:00:00','2026-06-10 11:00:00','NORMAL',          1,'Palestra sobre robótica'),
(2, 4, '2026-06-15 08:00:00','CONCLUIDO','2026-06-17 10:00:00','2026-06-17 12:00:00','DESARRUMADA_SUJA',1,'Aula prática de informática'),
-- ---- JUNHO: aceite ----
(7, 2, '2026-06-22 09:00:00','ACEITE',   '2026-06-25 15:00:00','2026-06-25 16:30:00','NORMAL',          0,'Reunião de coordenação do clube'),
-- ---- JULHO: em uso, aceites e pendentes ----
(8, 1, '2026-06-30 10:00:00','EM_USO',   '2026-07-03 14:00:00','2026-07-03 17:00:00','NORMAL',          1,'Configuração de servidores'),
(5, 2, '2026-07-01 09:00:00','ACEITE',   '2026-07-08 10:00:00','2026-07-08 12:00:00','NORMAL',          0,'Apresentação de projetos'),
(4, 6, '2026-07-02 11:00:00','PENDENTE', '2026-07-10 09:00:00','2026-07-10 13:00:00','NORMAL',          0,'Impressão 3D de peças'),
(9, 4, '2026-07-02 14:00:00','PENDENTE', '2026-07-15 14:00:00','2026-07-15 16:00:00','NORMAL',          0,'Gravação de vídeo promocional'),
(3, 6, '2026-07-18 09:00:00','ACEITE',   '2026-07-20 09:00:00','2026-07-20 12:00:00','NORMAL',          0,'Oficina de robótica');

-- ============================================================================
--  Fim. Abre o calendário do admin e navega entre a semana atual, a anterior
--  e as de junho para veres os eventos coloridos por estado.
-- ============================================================================
