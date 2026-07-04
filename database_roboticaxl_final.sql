
SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';


DROP SCHEMA IF EXISTS `clube_robotica` ;


CREATE SCHEMA IF NOT EXISTS `clube_robotica` DEFAULT CHARACTER SET utf8mb4 ;
USE `clube_robotica` ;


CREATE TABLE IF NOT EXISTS `utilizador` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `tipo` ENUM('ALUNO', 'PROFESSOR', 'ADMIN', 'RESPONSAVEL') NOT NULL,
  `nome` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `data_nascimento` DATE NOT NULL,
  `telefone` VARCHAR(20) NOT NULL,
  `linkedin` VARCHAR(255) NULL DEFAULT NULL,
  `turma` VARCHAR(10) NULL DEFAULT NULL,
  `foto` BLOB NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC));



CREATE TABLE IF NOT EXISTS `categoria` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `categoria` VARCHAR(255) NOT NULL,
  `imagem` BLOB NULL DEFAULT NULL,
  PRIMARY KEY (`id`));



CREATE TABLE IF NOT EXISTS `material` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `designacao` VARCHAR(100) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `imagem` BLOB NULL DEFAULT NULL,
  PRIMARY KEY (`id`));



CREATE TABLE IF NOT EXISTS `exemplar` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `num_referencia` VARCHAR(255) NOT NULL,
  `id_material` INT NOT NULL,
  `estado` ENUM('DISPONIVEL', 'EMPRESTADO', 'DANIFICADO', 'PERDIDO', 'MANUTENCAO') NOT NULL DEFAULT 'DISPONIVEL',
  `observacao` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_exemplar_material_idx` (`id_material` ASC),
  CONSTRAINT `fk_exemplar_material`
    FOREIGN KEY (`id_material`)
    REFERENCES `material` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);


CREATE TABLE IF NOT EXISTS `categoria_material` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_categoria` INT NOT NULL,
  `id_material` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_catmat_material_idx` (`id_material` ASC),
  UNIQUE INDEX `idx_unique_categoria_material` (`id_categoria` ASC, `id_material` ASC),
  CONSTRAINT `fk_catmat_categoria`
    FOREIGN KEY (`id_categoria`)
    REFERENCES `categoria` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_catmat_material`
    FOREIGN KEY (`id_material`)
    REFERENCES `material` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);



CREATE TABLE IF NOT EXISTS `sala` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `numero` VARCHAR(15) NOT NULL,
  `andar` INT NOT NULL,
  `bloco` VARCHAR(10) NOT NULL,
  `capacidade` INT NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`));



CREATE TABLE IF NOT EXISTS `exemplar_sala` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_exemplar` INT NOT NULL,
  `id_sala` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_exsala_sala_idx` (`id_sala` ASC),
  UNIQUE INDEX `idx_unique_exemplar_sala` (`id_sala` ASC, `id_exemplar` ASC),
  INDEX `fk_exsala_exemplar_idx` (`id_exemplar` ASC),
  CONSTRAINT `fk_exsala_exemplar`
    FOREIGN KEY (`id_exemplar`)
    REFERENCES `exemplar` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_exsala_sala`
    FOREIGN KEY (`id_sala`)
    REFERENCES `sala` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);



CREATE TABLE IF NOT EXISTS `requisicao_exemplar` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_utilizador` INT NOT NULL,
  `id_exemplar` INT NOT NULL,
  `data_pedido` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_pedido` ENUM('PENDENTE', 'ACEITE', 'EM_USO', 'REJEITADO', 'CONCLUIDO') NOT NULL DEFAULT 'PENDENTE',
  `data_levantamento` DATETIME NULL DEFAULT NULL,
  `data_devolucao` DATETIME NULL DEFAULT NULL,
  `estado_devolucao` ENUM('OK', 'DANIFICADO', 'PERDIDO') NOT NULL DEFAULT 'OK',
  `estado_entrega` TINYINT(1) NOT NULL DEFAULT 0,
  `observacao` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_reqex_utilizador_idx` (`id_utilizador` ASC),
  INDEX `fk_reqex_exemplar_idx` (`id_exemplar` ASC),
  CONSTRAINT `fk_reqex_utilizador`
    FOREIGN KEY (`id_utilizador`)
    REFERENCES `utilizador` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reqex_exemplar`
    FOREIGN KEY (`id_exemplar`)
    REFERENCES `exemplar` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);



CREATE TABLE IF NOT EXISTS `requisicao` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_sala` INT NOT NULL,
  `id_utilizador` INT NOT NULL,
  `data` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `estado_sala` ENUM('PENDENTE', 'ACEITE', 'EM_USO', 'REJEITADO', 'CONCLUIDO') NULL DEFAULT 'PENDENTE',
  `data_inicio` DATETIME NOT NULL,
  `data_fim` DATETIME NOT NULL,
  `estado_devolucao` ENUM('NORMAL', 'DESARRUMADA_SUJA', 'DANIFICADA') NOT NULL DEFAULT 'NORMAL',
  `estado_entrega` TINYINT(1) NOT NULL DEFAULT 0,
  `observacao` TEXT NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_reqsala_utilizador_idx` (`id_utilizador` ASC),
  INDEX `fk_reqsala_sala_idx` (`id_sala` ASC),
  CONSTRAINT `fk_reqsala_utilizador`
    FOREIGN KEY (`id_utilizador`)
    REFERENCES `utilizador` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_reqsala_sala`
    FOREIGN KEY (`id_sala`)
    REFERENCES `sala` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION);




CREATE TABLE IF NOT EXISTS `evento` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `titulo` VARCHAR(255) NOT NULL,
  `descricao` TEXT NULL DEFAULT NULL,
  `imagem_url` BLOB NULL DEFAULT NULL,
  `url` TEXT NULL DEFAULT NULL,
  `ordem` INT NOT NULL DEFAULT 0,
  `ativo` TINYINT NOT NULL DEFAULT 1,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`));

USE `clube_robotica` ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_utilizador (
    IN p_nome VARCHAR(100), 
    IN p_email VARCHAR(255),
    IN p_data_nascimento DATE,
    IN p_telefone VARCHAR(20),
    IN p_linkedin VARCHAR(255)
)
BEGIN
    IF p_nome NOT REGEXP '^([A-ZAÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ][a-zaáàâãäéèêëíìîïóòôõöúùûüç]+| [A-ZAÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ][a-zaáàâãäéèêëíìîïóòôõöúùûüç]+)+((de)|(dos)|(da)|(das)|(do)|(e))?([A-ZAÁÀÂÃÄÉÈÊËÍÌÎÏÓÒÔÕÖÚÙÛÜÇ][a-zaáàâãäéèêëíìîïóòôõöúùûüç]+)*$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45001, MESSAGE_TEXT = 'Nome do utilizador inválido';
    END IF;

    IF p_email NOT REGEXP '^[A-Za-z0-9._%+-]+@[A-Za-z0-9.-]+\\.[A-Za-z]{2,}$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45002, MESSAGE_TEXT = 'Email inválido';
    END IF;

    IF p_data_nascimento > CURDATE() OR p_data_nascimento < '1900-01-01' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45003, MESSAGE_TEXT = 'Data de nascimento inválida';
    END IF;
    
    IF p_telefone NOT REGEXP '^(\\+351)?((2[0-9]{8})|(9[1236][0-9]{7}))$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45004, MESSAGE_TEXT = 'Telefone inválido';
    END IF;

    IF p_linkedin IS NOT NULL AND p_linkedin != '' AND p_linkedin NOT REGEXP '^(https?:\\/\\/)?([\\da-z\\.-]+)\\.([a-z\\.]{2,6})([\\/\\w \\.-]*)*\\/?$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45005, MESSAGE_TEXT = 'URL do LinkedIn inválido';
    END IF;
END$$

DELIMITER ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_categoria (
    IN p_categoria VARCHAR(255)
)
BEGIN
    IF LENGTH(TRIM(p_categoria)) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45006, MESSAGE_TEXT = 'O nome da categoria é muito curto ou vazio';
    END IF;
END$$

DELIMITER ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_material (
    IN p_designacao VARCHAR(100)
)
BEGIN
    IF LENGTH(TRIM(p_designacao)) < 2 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45007, MESSAGE_TEXT = 'A designação do material é inválida';
    END IF;
END$$

DELIMITER ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_exemplar (
    IN p_num_referencia VARCHAR(255),
    IN p_id_material INT
)
BEGIN
    IF LENGTH(TRIM(p_num_referencia)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45008, MESSAGE_TEXT = 'Número de referência obrigatório';
    END IF;
    
    IF p_id_material <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45009, MESSAGE_TEXT = 'ID do material inválido';
    END IF;
END$$

DELIMITER ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_categoria_material (
    IN p_id_categoria INT,
    IN p_id_material INT
)
BEGIN
    IF p_id_categoria <= 0 OR p_id_material <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45010, MESSAGE_TEXT = 'IDs de ligação inválidos';
    END IF;
END$$

DELIMITER ;

DELIMITER $$

USE `clube_robotica`$$

DROP PROCEDURE IF EXISTS sp_valida_sala$$

CREATE PROCEDURE sp_valida_sala (
    IN p_numero VARCHAR(15),
    IN p_capacidade INT,
	IN p_andar INT,
    IN p_bloco VARCHAR(10)
)
BEGIN
    IF LENGTH(TRIM(p_numero)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45011, MESSAGE_TEXT = 'Número da sala obrigatório';
    ELSEIF CHAR_LENGTH(TRIM(p_numero)) != 2 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45014, MESSAGE_TEXT = 'O número da sala deve ter exatamente 2 caracteres (ex: 01, 12)';
    END IF;

    IF p_capacidade <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45012, MESSAGE_TEXT = 'A capacidade da sala deve ser maior que zero';
    END IF;
    
       IF LENGTH(TRIM(p_andar)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45013, MESSAGE_TEXT = 'Andar obrigatório';
        ELSEIF p_andar > 2 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45012, MESSAGE_TEXT = 'O andar so pode ir até 2';
	END IF;
        
    IF LENGTH(TRIM(p_bloco)) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45013, MESSAGE_TEXT = 'Bloco obrigatório';
    ELSEIF UPPER(TRIM(p_bloco)) NOT REGEXP '^[A-G]$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45015, MESSAGE_TEXT = 'O bloco deve ser uma única letra de A a G';
    END IF;
END$$

DELIMITER ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_exemplar_sala (
    IN p_id_exemplar INT,
    IN p_id_sala INT
)
BEGIN
    IF p_id_exemplar <= 0 OR p_id_sala <= 0 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45014, MESSAGE_TEXT = 'IDs de ligação Sala-Exemplar inválidos';
    END IF;
END$$

DELIMITER ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_req_exemplar (
    IN p_data_pedido DATETIME,
    IN p_data_levantamento DATETIME,
    IN p_data_devolucao DATETIME
)
BEGIN
    IF p_data_levantamento IS NOT NULL AND p_data_devolucao IS NOT NULL THEN
        IF p_data_devolucao < p_data_levantamento THEN
            SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45015, MESSAGE_TEXT = 'Data de devolução não pode ser anterior ao levantamento';
        END IF;
    END IF;

    IF p_data_levantamento IS NOT NULL AND p_data_levantamento < p_data_pedido THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45016, MESSAGE_TEXT = 'Levantamento não pode ser anterior ao pedido';
    END IF;
END$$

DELIMITER ;



DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_requisicao_sala (
    IN p_data_inicio DATETIME,
    IN p_data_fim DATETIME
)
BEGIN
    IF p_data_fim <= p_data_inicio THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45017, MESSAGE_TEXT = 'A data/hora de fim deve ser posterior ao início';
    END IF;
END$$

DELIMITER ;


DELIMITER $$
USE `clube_robotica`$$
CREATE PROCEDURE sp_valida_evento (
    IN p_titulo VARCHAR(255),
    IN p_url TEXT
)
BEGIN
    IF LENGTH(TRIM(p_titulo)) < 3 THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45018, MESSAGE_TEXT = 'O título do evento deve ter pelo menos 3 caracteres';
    END IF;

    IF p_url IS NOT NULL AND p_url != '' AND p_url NOT REGEXP '^(https?:\\/\\/)?([\\da-z\\.-]+)\\.([a-z\\.]{2,6})([\\/\\w \\.-]*)*\\/?$' THEN
        SIGNAL SQLSTATE '45000' SET MYSQL_ERRNO = 45019, MESSAGE_TEXT = 'URL do evento inválido';
    END IF;
END$$

DELIMITER ;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_exemplar_sala AS
SELECT 
    exemplar_sala.id AS id_associacao,
    sala.numero AS numero_sala,
    sala.bloco,
    sala.andar,
    sala.capacidade,
    exemplar.num_referencia,
    exemplar.estado AS estado_exemplar,
    material.designacao AS nome_material,
    material.descricao AS desc_material
FROM exemplar_sala
JOIN sala ON exemplar_sala.id_sala = sala.id
JOIN exemplar ON exemplar_sala.id_exemplar = exemplar.id
JOIN material ON exemplar.id_material = material.id;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_exemplar AS
SELECT 
    exemplar.id AS id_exemplar,
    exemplar.num_referencia,
    exemplar.estado,
    material.designacao,
    material.descricao
FROM exemplar
JOIN material ON exemplar.id_material = material.id;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_requisicao_exemplar AS
SELECT 
    requisicao_exemplar.id AS id_requisicao,
    requisicao_exemplar.data_pedido,
    requisicao_exemplar.estado_pedido,
    utilizador.nome AS nome_utilizador,
    utilizador.email,
    exemplar.num_referencia,
    material.designacao AS material_requisitado
FROM requisicao_exemplar
JOIN utilizador ON requisicao_exemplar.id_utilizador = utilizador.id
JOIN exemplar ON requisicao_exemplar.id_exemplar = exemplar.id
JOIN material ON exemplar.id_material = material.id;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_categoria_material AS
SELECT 
    categoria_material.id AS id_link,
    categoria.categoria AS nome_categoria,
    material.designacao AS nome_material
FROM categoria_material
JOIN categoria ON categoria_material.id_categoria = categoria.id
JOIN material ON categoria_material.id_material = material.id;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_requisicao_sala AS
SELECT 
    requisicao.id AS id_requisicao,
    requisicao.data_inicio,
    requisicao.data_fim,
    requisicao.observacao, 
    requisicao.estado_sala,
    sala.numero AS numero_sala,
    sala.bloco,
    sala.andar,
    utilizador.nome AS nome_utilizador,
    utilizador.email
FROM requisicao
JOIN sala ON requisicao.id_sala = sala.id
JOIN utilizador ON requisicao.id_utilizador = utilizador.id;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_catalogo_materiais AS
SELECT 
    material.id AS id_material,
    material.designacao,
    categoria.categoria,
    COUNT(exemplar.id) AS total_exemplares,
    SUM(CASE WHEN exemplar.estado = 'DISPONIVEL' THEN 1 ELSE 0 END) AS qtd_disponivel
FROM material
LEFT JOIN categoria_material ON material.id = categoria_material.id_material
LEFT JOIN categoria ON categoria_material.id_categoria = categoria.id
LEFT JOIN exemplar ON material.id = exemplar.id_material
GROUP BY material.id, material.designacao, categoria.categoria;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_equipamentos_em_emprestimo AS
SELECT 
    requisicao_exemplar.id AS id_requisicao,
    utilizador.nome AS nome_utilizador,
    utilizador.email,
    material.designacao AS material,
    exemplar.num_referencia,
    requisicao_exemplar.data_levantamento,
    DATEDIFF(NOW(), requisicao_exemplar.data_levantamento) AS dias_com_equipamento
FROM requisicao_exemplar
JOIN utilizador ON requisicao_exemplar.id_utilizador = utilizador.id
JOIN exemplar ON requisicao_exemplar.id_exemplar = exemplar.id
JOIN material ON exemplar.id_material = material.id
WHERE requisicao_exemplar.estado_pedido = 'ACEITE' 
  AND requisicao_exemplar.data_levantamento IS NOT NULL 
  AND requisicao_exemplar.data_devolucao IS NULL;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_agenda_salas AS
SELECT 
    requisicao.id AS id_reserva,
    sala.numero AS sala,
    sala.bloco,
    utilizador.nome AS reservado_por,
    requisicao.data_inicio,
    requisicao.data_fim,
    requisicao.estado_sala AS estado_aprovacao
FROM requisicao
JOIN sala ON requisicao.id_sala = sala.id
JOIN utilizador ON requisicao.id_utilizador = utilizador.id
WHERE requisicao.estado_sala != 'REJEITADO';


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_inventario_sala AS
SELECT 
    sala.numero AS sala,
    sala.bloco,
    material.designacao AS equipamento,
    exemplar.num_referencia,
    exemplar.estado AS estado_equipamento
FROM exemplar_sala
JOIN sala ON exemplar_sala.id_sala = sala.id
JOIN exemplar ON exemplar_sala.id_exemplar = exemplar.id
JOIN material ON exemplar.id_material = material.id;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_historico_danos AS
SELECT 
    'EQUIPAMENTO' AS tipo,
    utilizador.nome AS responsavel,
    material.designacao AS item,
    requisicao_exemplar.data_devolucao AS data_ocorrencia,
    requisicao_exemplar.estado_devolucao AS estado_final,
    requisicao_exemplar.observacao
FROM requisicao_exemplar
JOIN utilizador ON requisicao_exemplar.id_utilizador = utilizador.id
JOIN exemplar ON requisicao_exemplar.id_exemplar = exemplar.id
JOIN material ON exemplar.id_material = material.id
WHERE requisicao_exemplar.estado_devolucao IN ('DANIFICADO', 'PERDIDO')
UNION ALL
SELECT 
    'SALA' AS tipo,
    utilizador.nome AS responsavel,
    CONCAT('Sala ', sala.numero) AS item,
    requisicao.data_fim AS data_ocorrencia,
    requisicao.estado_devolucao AS estado_final,
    requisicao.observacao
FROM requisicao
JOIN utilizador ON requisicao.id_utilizador = utilizador.id
JOIN sala ON requisicao.id_sala = sala.id
WHERE requisicao.estado_devolucao IN ('DESARRUMADA_SUJA', 'DANIFICADA');


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_lista_utilizadores_simples AS
SELECT 
    id,
    nome,
    email,
    tipo,
    turma,
    linkedin
FROM utilizador;


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_pedidos_pendentes AS
SELECT 
    'EQUIPAMENTO' AS tipo_pedido,
    requisicao_exemplar.id AS id_pedido,
    utilizador.nome AS solicitante,
    material.designacao AS item_solicitado,
    requisicao_exemplar.data_pedido AS data_solicitacao
FROM requisicao_exemplar
JOIN utilizador ON requisicao_exemplar.id_utilizador = utilizador.id
JOIN exemplar ON requisicao_exemplar.id_exemplar = exemplar.id
JOIN material ON exemplar.id_material = material.id
WHERE requisicao_exemplar.estado_pedido = 'PENDENTE'
UNION ALL
SELECT 
    'SALA' AS tipo_pedido,
    requisicao.id AS id_pedido,
    utilizador.nome AS solicitante,
    CONCAT('Sala ', sala.numero) AS item_solicitado,
    requisicao.data AS data_solicitacao
FROM requisicao
JOIN utilizador ON requisicao.id_utilizador = utilizador.id
JOIN sala ON requisicao.id_sala = sala.id
WHERE requisicao.estado_sala = 'PENDENTE';


USE `clube_robotica`;
CREATE OR REPLACE VIEW vw_eventos AS
SELECT 
    titulo,
    descricao,
    url,
    DATE_FORMAT(created_at, '%d/%m/%Y') AS data_publicacao
FROM evento
WHERE ativo = 1
ORDER BY ordem ASC, created_at DESC;
USE `clube_robotica`;

DELIMITER $$
USE `clube_robotica`$$
CREATE TRIGGER trg_utilizador_before_insert
BEFORE INSERT ON utilizador FOR EACH ROW
BEGIN
    CALL sp_valida_utilizador(NEW.nome, NEW.email, NEW.data_nascimento, NEW.telefone, NEW.linkedin);
END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_utilizador_before_update
BEFORE UPDATE ON utilizador FOR EACH ROW
BEGIN
    CALL sp_valida_utilizador(NEW.nome, NEW.email, NEW.data_nascimento, NEW.telefone, NEW.linkedin);
END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_categoria_before_insert BEFORE INSERT ON categoria FOR EACH ROW
BEGIN CALL sp_valida_categoria(NEW.categoria); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_categoria_before_update BEFORE UPDATE ON categoria FOR EACH ROW
BEGIN CALL sp_valida_categoria(NEW.categoria); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_material_before_insert BEFORE INSERT ON material FOR EACH ROW
BEGIN CALL sp_valida_material(NEW.designacao); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_material_before_update BEFORE UPDATE ON material FOR EACH ROW
BEGIN CALL sp_valida_material(NEW.designacao); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_exemplar_before_insert BEFORE INSERT ON exemplar FOR EACH ROW
BEGIN CALL sp_valida_exemplar(NEW.num_referencia, NEW.id_material); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_exemplar_before_update BEFORE UPDATE ON exemplar FOR EACH ROW
BEGIN CALL sp_valida_exemplar(NEW.num_referencia, NEW.id_material); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_cat_mat_before_insert BEFORE INSERT ON categoria_material FOR EACH ROW
BEGIN CALL sp_valida_categoria_material(NEW.id_categoria, NEW.id_material); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_cat_mat_before_update BEFORE UPDATE ON categoria_material FOR EACH ROW
BEGIN CALL sp_valida_categoria_material(NEW.id_categoria, NEW.id_material); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_sala_before_insert BEFORE INSERT ON sala FOR EACH ROW
BEGIN CALL sp_valida_sala(NEW.numero, NEW.capacidade,NEW.andar, NEW.bloco); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_sala_before_update BEFORE UPDATE ON sala FOR EACH ROW
BEGIN CALL sp_valida_sala(NEW.numero, NEW.capacidade, NEW.bloco,NEW.andar); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_ex_sala_before_insert BEFORE INSERT ON exemplar_sala FOR EACH ROW
BEGIN CALL sp_valida_exemplar_sala(NEW.id_exemplar, NEW.id_sala); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_ex_sala_before_update BEFORE UPDATE ON exemplar_sala FOR EACH ROW
BEGIN CALL sp_valida_exemplar_sala(NEW.id_exemplar, NEW.id_sala); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_req_ex_before_insert BEFORE INSERT ON requisicao_exemplar FOR EACH ROW
BEGIN CALL sp_valida_req_exemplar(NEW.data_pedido, NEW.data_levantamento, NEW.data_devolucao); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_req_ex_before_update BEFORE UPDATE ON requisicao_exemplar FOR EACH ROW
BEGIN CALL sp_valida_req_exemplar(NEW.data_pedido, NEW.data_levantamento, NEW.data_devolucao); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_req_sala_before_insert BEFORE INSERT ON requisicao FOR EACH ROW
BEGIN CALL sp_valida_requisicao_sala(NEW.data_inicio, NEW.data_fim); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_req_sala_before_update BEFORE UPDATE ON requisicao FOR EACH ROW
BEGIN CALL sp_valida_requisicao_sala(NEW.data_inicio, NEW.data_fim); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_evento_before_insert BEFORE INSERT ON evento FOR EACH ROW
BEGIN CALL sp_valida_evento(NEW.titulo, NEW.url); END$$

USE `clube_robotica`$$
CREATE TRIGGER trg_evento_before_update BEFORE UPDATE ON evento FOR EACH ROW
BEGIN CALL sp_valida_evento(NEW.titulo, NEW.url); END$$


DELIMITER ;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;


START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (1, 'RESPONSAVEL', 'Engenheiro Carlos Sousa', 'carlos@roboticaxl.pt', '123', '1980-05-15', '912345678', 'https://linkedin.com/in/carlossousa', '', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (2, 'PROFESSOR', 'Maria De Lurdes', 'maria@roboticaxl.pt', '123', '1975-11-20', '961234567', '', '', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (3, 'ADMIN', 'Administrador Do Sistema', 'admin@roboticaxl.pt', '123', '1990-01-01', '939876543', '', '', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (4, 'ALUNO', 'Joao Da Silva', 'joao@roboticaxl.pt', '123', '2006-03-10', '910000001', '', '12ºA', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (5, 'ALUNO', 'Beatriz Costa', 'beatriz@roboticaxl.pt', '123', '2006-07-22', '920000002', 'https://linkedin.com/in/beatrizcosta', '12ºB', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (6, 'ALUNO', 'Tiago Ferreira', 'tiago@roboticaxl.pt', '123', '2007-01-15', '930000003', '', '11ºA', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (7, 'ALUNO', 'Ana Rodrigues', 'ana@roboticaxl.pt', '123', '2007-05-30', '960000004', '', '11ºC', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (8, 'ALUNO', 'Diogo Santos', 'diogo@roboticaxl.pt', '123', '2008-09-12', '910000005', '', '10ºB', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (9, 'ALUNO', 'Sofia Martins', 'sofia@roboticaxl.pt', '123', '2008-02-14', '920000006', '', '10ºA', NULL);
INSERT INTO `utilizador` (`id`, `tipo`, `nome`, `email`, `password_hash`, `data_nascimento`, `telefone`, `linkedin`, `turma`, `foto`) VALUES (10, 'ALUNO', 'Ricardo Oliveira', 'ricardo@roboticaxl.pt', '123', '2005-12-05', '930000007', 'https://linkedin.com/in/ricardooliveira', '12ºC', NULL);

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (1, 'Microcontroladores', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (2, 'Sensores', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (3, 'Atuadores e Motores', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (4, 'Impressao 3D', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (5, 'Ferramentas Manuais', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (6, 'Componentes Eletronicos', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (7, 'Kits Educativos', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (8, 'Consumiveis', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (9, 'Livros Tecnicos', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (10, 'Equipamento de Protecao', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (11, 'Drones e Aereos', NULL);
INSERT INTO `categoria` (`id`, `categoria`, `imagem`) VALUES (12, 'Energia e Baterias', NULL);

COMMIT;


START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (1, 'Arduino Uno R3', 'Placa de microcontrolador baseada no ATmega328P. Essencial para iniciantes.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (2, 'Raspberry Pi 4 Model B', 'Computador de placa unica com 4GB RAM, ideal para visao computacional.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (3, 'ESP32 DevKit V1', 'Microcontrolador com Wi-Fi e Bluetooth integrado para projetos IoT.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (4, 'Sensor Ultrassonico HC-SR04', 'Sensor de distancia por ultrassons, alcance 2cm a 400cm.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (5, 'Sensor De Linha Infravermelho', 'Modulo seguidor de linha TCRT5000 para robos moveis.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (6, 'Camara Raspberry Pi V2', 'Modulo de camara de 8MP para projetos de visao.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (7, 'Servomotor SG90', 'Micro servo motor 9g para controlo angular preciso.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (8, 'Motor DC Com Redutora', 'Motor amarelo 3-6V com caixa de reducao para rodas de robo.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (9, 'Impressora 3D Ender 3 V2', 'Impressora FDM com volume de 220x220x250mm.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (10, 'Filamento PLA Branco 1.75mm', 'Bobine de 1kg de PLA para impressao 3D.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (11, 'Ferro De Soldar 60W', 'Ferro de soldar com temperatura ajustavel.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (12, 'Multimetro Digital', 'Para medicao de tensao, corrente e resistencia.', NULL);
INSERT INTO `material` (`id`, `designacao`, `descricao`, `imagem`) VALUES (13, 'Kit LEGO Mindstorms EV3', 'Conjunto de robotica educacional avancado com bloco inteligente.', NULL);

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (1, 'ARD-001', 1, 'DISPONIVEL', 'Caixa original com cabo');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (2, 'ARD-002', 1, 'EMPRESTADO', 'Projeto do 12A');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (3, 'ARD-003', 1, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (4, 'ARD-004', 1, 'DANIFICADO', 'Porta USB instavel');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (5, 'ARD-005', 1, 'DISPONIVEL', 'Novo');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (6, 'RPI-001', 2, 'DISPONIVEL', 'Com caixa transparente');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (7, 'RPI-002', 2, 'DISPONIVEL', 'SD Card 32GB');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (8, 'RPI-003', 2, 'MANUTENCAO', 'Atualizacao de firmware');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (9, 'RPI-004', 2, 'EMPRESTADO', 'Professor Carlos');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (10, 'RPI-005', 2, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (11, 'ESP-001', 3, 'DISPONIVEL', 'Pinos soldados');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (12, 'ESP-002', 3, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (13, 'ESP-003', 3, 'PERDIDO', 'Desapareceu do laboratorio');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (14, 'ESP-004', 3, 'DISPONIVEL', 'Versao 30 pinos');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (15, 'ESP-005', 3, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (16, 'SONAR-001', 4, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (17, 'SONAR-002', 4, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (18, 'SONAR-003', 4, 'DANIFICADO', 'Um emissor partido');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (19, 'SONAR-004', 4, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (20, 'SONAR-005', 4, 'EMPRESTADO', 'Grupo de Robotica');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (21, 'LINE-001', 5, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (22, 'LINE-002', 5, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (23, 'LINE-003', 5, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (24, 'LINE-004', 5, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (25, 'LINE-005', 5, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (26, 'CAM-001', 6, 'DISPONIVEL', 'Cabo flat curto');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (27, 'CAM-002', 6, 'DISPONIVEL', 'Cabo flat longo');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (28, 'CAM-003', 6, 'EMPRESTADO', 'Projeto de Visao Computacional');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (29, 'CAM-004', 6, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (30, 'CAM-005', 6, 'DANIFICADO', 'Lente riscada');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (31, 'SRV-001', 7, 'DISPONIVEL', 'Inclui bracos plasticos');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (32, 'SRV-002', 7, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (33, 'SRV-003', 7, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (34, 'SRV-004', 7, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (35, 'SRV-005', 7, 'DANIFICADO', 'Engrenagens moidas');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (36, 'MOT-001', 8, 'DISPONIVEL', 'Soldado com fios');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (37, 'MOT-002', 8, 'DISPONIVEL', 'Soldado com fios');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (38, 'MOT-003', 8, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (39, 'MOT-004', 8, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (40, 'MOT-005', 8, 'EMPRESTADO', 'Carro telecomandado');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (41, 'PRT-001', 9, 'DISPONIVEL', 'Sala 204');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (42, 'PRT-002', 9, 'DISPONIVEL', 'Sala LAB2');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (43, 'PRT-003', 9, 'MANUTENCAO', 'Entupida');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (44, 'PRT-004', 9, 'DISPONIVEL', 'Sala LAB2');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (45, 'PRT-005', 9, 'DISPONIVEL', 'Calibrada recentemente');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (46, 'PLA-001', 10, 'DISPONIVEL', 'Branco Aberto');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (47, 'PLA-002', 10, 'DISPONIVEL', 'Preto Fechado');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (48, 'PLA-003', 10, 'DISPONIVEL', 'Azul Fechado');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (49, 'PLA-004', 10, 'DISPONIVEL', 'Vermelho Aberto');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (50, 'PLA-005', 10, 'DISPONIVEL', 'Branco Fechado');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (51, 'SOLD-001', 11, 'DISPONIVEL', 'Ponta fina');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (52, 'SOLD-002', 11, 'DISPONIVEL', 'Ponta grossa');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (53, 'SOLD-003', 11, 'DANIFICADO', 'Cabo cortado');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (54, 'SOLD-004', 11, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (55, 'SOLD-005', 11, 'EMPRESTADO', 'Workshop');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (56, 'MULTI-001', 12, 'DISPONIVEL', 'Com pontas de prova');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (57, 'MULTI-002', 12, 'DISPONIVEL', 'Sem bateria');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (58, 'MULTI-003', 12, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (59, 'MULTI-004', 12, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (60, 'MULTI-005', 12, 'DISPONIVEL', '');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (61, 'LEGO-001', 13, 'DISPONIVEL', 'Kit Completo #1');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (62, 'LEGO-002', 13, 'EMPRESTADO', 'Competicao Nacional');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (63, 'LEGO-003', 13, 'DISPONIVEL', 'Faltam 2 pecas tecnicas');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (64, 'LEGO-004', 13, 'DISPONIVEL', 'Kit Completo #2');
INSERT INTO `exemplar` (`id`, `num_referencia`, `id_material`, `estado`, `observacao`) VALUES (65, 'LEGO-005', 13, 'MANUTENCAO', 'Verificacao de inventario');

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (1, 1, 1);
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (2, 3, 7); 
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (3, 2, 1); 
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (4, 3, 1);
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (5, 4, 2);
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (6, 5, 2); 
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (7, 6, 2);
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (8, 7, 3);
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (9, 8, 3);
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (10, 2, 4); 
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (11, 7, 4); 
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (12, 2, 5); 
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (13, 2, 6); 
INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (14, 6, 6); 

INSERT INTO `categoria_material` (`id`, `id_categoria`, `id_material`) VALUES (15, 7, 13);

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (1, '12', 1, 'A', 30, 'Sala de Aula Teorica');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (2, '21', 2, 'B', 25, 'Laboratorio de Informatica 1');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (3, '02', 0, 'C', 15, 'Oficina de Robotica e Mecanica');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (4, '17', 0, 'C', 15, 'Laboratorio de Impressao 3D e Prototipagem');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (5, '08', 1, 'A', 150, 'Auditorio Principal');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (6, '05', 0, 'C', 50, 'Arrecadacao Geral');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (7, '25', 1, 'A', 10, 'Sala de Reunioes da Direcao');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (8, '09', 2, 'B', 20, 'Laboratorio de Redes e Servidores');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (9, '12', 1, 'B', 12, 'Estudio de Gravacao e Multimedia');
INSERT INTO `sala` (`id`, `numero`, `andar`, `bloco`, `capacidade`, `descricao`) VALUES (10, '01', 0, 'D', 200, 'Pavilhao Exterior Coberto');

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (1, 1, 3);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (2, 2, 3);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (3, 3, 3);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (4, 4, 3);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (5, 5, 3);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (6, 9, 4);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (7, 10, 4);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (8, 13, 3);
INSERT INTO `exemplar_sala` (`id`, `id_exemplar`, `id_sala`) VALUES (9, 14, 3);

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (1, 4, 1, '2023-10-01 09:00:00', 'CONCLUIDO', '2023-10-01 09:30:00', '2023-10-05 10:00:00', 'OK', 'Tudo impecavel');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (2, 5, 11, '2023-10-02 14:00:00', 'CONCLUIDO', '2023-10-02 14:15:00', '2023-10-02 16:00:00', 'DANIFICADO', 'Ponta do ferro oxidada');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (3, 6, 2, NOW(), 'ACEITE', NOW(), NULL, 'OK', 'Projeto Final de Ano');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (4, 7, 16, NOW(), 'ACEITE', NOW(), NULL, 'OK', 'Levou para casa para treinar para o torneio');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (5, 4, 9, NOW(), 'PENDENTE', NULL, NULL, 'OK', 'Preciso da impressora para o fim de semana');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (6, 8, 4, '2023-11-10 10:00:00', 'CONCLUIDO', '2023-11-10 10:15:00', '2023-11-12 09:00:00', 'OK', 'Devolvido com ligeiro atraso');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (7, 9, 5, '2023-11-15 14:00:00', 'CONCLUIDO', '2023-11-15 14:30:00', '2023-11-16 10:00:00', 'OK', '');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (8, 10, 12, '2024-01-05 09:00:00', 'PENDENTE', '2024-01-05 09:10:00', NULL, 'OK', 'Ainda em uso na sala de aula');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (9, 6, 7, NOW(), 'PENDENTE', NULL, NULL, 'OK', 'Para o projeto de sensores de estacionamento');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (10, 5, 15, NOW(), 'REJEITADO', NULL, NULL, 'OK', 'Aluno com devolucoes em atraso noutros materiais');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (11, 4, 13, NOW(), 'ACEITE', NOW(), NULL, 'DANIFICADO', 'Reportou que o ferro ja estava queimado ao levantar');
INSERT INTO `requisicao_exemplar` (`id`, `id_utilizador`, `id_exemplar`, `data_pedido`, `estado_pedido`, `data_levantamento`, `data_devolucao`, `estado_devolucao`, `observacao`) VALUES (12, 7, 2, NOW(), 'CONCLUIDO', NOW(), NOW(), 'OK', 'Teste rapido na aula');

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (1, 3, 4, NOW(), 'ACEITE', '2024-01-10 14:00:00', '2024-01-10 16:00:00', 'NORMAL', 'Trabalho de grupo');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (2, 4, 5, NOW(), 'ACEITE', '2024-01-12 10:00:00', '2024-01-12 12:00:00', 'DESARRUMADA_SUJA', 'Deixaram filamento no chao');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (3, 3, 1, NOW(), 'PENDENTE', '2026-05-20 09:00:00', '2026-05-20 13:00:00', 'NORMAL', 'Workshop de Soldadura');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (4, 5, 2, NOW(), 'ACEITE', '2026-06-01 10:00:00', '2026-06-01 12:00:00', 'NORMAL', 'Palestra sobre IA');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (5, 1, 6, NOW(), 'ACEITE', '2024-02-10 09:00:00', '2024-02-10 11:00:00', 'NORMAL', 'Apresentacao de projeto de PAP');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (6, 2, 8, NOW(), 'PENDENTE', '2024-02-12 14:00:00', '2024-02-12 16:00:00', 'NORMAL', 'Estudo para exame de redes');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (7, 5, 1, NOW(), 'ACEITE', '2024-02-15 18:00:00', '2024-02-15 20:00:00', 'NORMAL', 'Reuniao geral de pais e mestres');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (8, 3, 7, NOW(), 'REJEITADO', '2024-02-20 10:00:00', '2024-02-20 12:00:00', 'NORMAL', 'Sala em manutencao eletrica');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (9, 4, 9, NOW(), 'ACEITE', '2024-02-22 15:00:00', '2024-02-22 17:00:00', 'DESARRUMADA_SUJA', 'Deixaram restos de PLA nas mesas');
INSERT INTO `requisicao` (`id`, `id_sala`, `id_utilizador`, `data`, `estado_sala`, `data_inicio`, `data_fim`, `estado_devolucao`, `observacao`) VALUES (10, 7, 2, NOW(), 'ACEITE', '2024-02-25 09:00:00', '2024-02-25 10:30:00', 'NORMAL', 'Reuniao de coordenacao do clube');

COMMIT;



START TRANSACTION;
USE `clube_robotica`;
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (1, 'Workshop De Arduino Iniciante', 'Uma introducao pratica ao mundo dos microcontroladores. Aprende a piscar LEDs e ler sensores.', 0, 'https://cr-ofc.pt', 1, 1, '2023-09-15 10:00:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (2, 'Participacao No BotOlympics 2023', 'A nossa equipa alcancou o 2 lugar na competicao nacional de robos seguidores de linha em Coimbra.', 0, 'https://botolympics.pt', 2, 1, '2023-11-20 14:30:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (3, 'Feira De Ciencia Da Escola', 'Vem visitar o nosso stand na feira anual e ve as impressoras 3D em funcionamento.', 0, NULL, 3, 1, '2024-01-10 09:00:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (4, 'Curso De Soldadura Eletronica', 'Aprende a soldar componentes em PCB com seguranca e precisao.', 0, NULL, 4, 0, '2023-05-05 11:00:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (5, 'Torneio de Futebol Robotico', 'Competicao interna de robos autonomos 2vs2.',0 , 'https://cr-ofc.pt', 5, 1, '2024-02-01 10:00:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (6, 'Sessao de Duvidas 12 Ano', 'Apoio aos projetos finais de curso dos alunos finalistas.', 0, NULL, 6, 1, '2024-02-05 14:00:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (7, 'Hackathon de IoT', 'Maratona de programacao de 24 horas focada em Internet das Coisas.',0 , 'https://cr-ofc.pt', 7, 0, '2024-03-20 09:00:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (8, 'Workshop de Soldadura Avancada', 'Tecnicas de soldadura SMD para alunos experientes.', 0, NULL, 8, 1, '2024-04-10 11:30:00');
INSERT INTO `evento` (`id`, `titulo`, `descricao`, `imagem_url`, `url`, `ordem`, `ativo`, `created_at`) VALUES (9, 'Exposicao Open Day', 'Dia aberto a comunidade para mostrar os projetos do clube.', 0, NULL, 9, 1, '2024-05-15 09:00:00');

COMMIT;

-- =====================================================================
-- DADOS DE TESTE ADICIONAIS (gerado): requisicoes de jan a meados de julho 2026
-- + imagens nos eventos. Estados coerentes com as datas.
-- =====================================================================

USE `clube_robotica`;

-- Imagens dos eventos
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1553406830-ef2513450d76?w=900&q=80' WHERE `id` = 1;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1581091226825-a6a2a5aee158?w=900&q=80' WHERE `id` = 2;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1567427017947-545c5f8d16ad?w=900&q=80' WHERE `id` = 3;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1608564697071-ddf911d81370?w=900&q=80' WHERE `id` = 4;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1535378917042-10a22c95931a?w=900&q=80' WHERE `id` = 5;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1522202176988-66273c2fd55f?w=900&q=80' WHERE `id` = 6;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1504384308090-c894fdcc538d?w=900&q=80' WHERE `id` = 7;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1591799264318-7e6ef8ddb7ea?w=900&q=80' WHERE `id` = 8;
UPDATE `evento` SET `imagem_url` = 'https://images.unsplash.com/photo-1531482615713-2afd69097998?w=900&q=80' WHERE `id` = 9;

-- Requisicoes de material
INSERT INTO `requisicao_exemplar` (`id_utilizador`,`id_exemplar`,`data_pedido`,`estado_pedido`,`data_levantamento`,`data_devolucao`,`estado_devolucao`,`observacao`) VALUES
(3, 29, '2026-01-08 17:00:00', 'CONCLUIDO', '2026-01-13 17:00:00', '2026-01-25 17:00:00', 'OK', 'Prototipo do clube.'),
(10, 57, '2026-01-17 17:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Preciso para a feira de ciencias.'),
(3, 58, '2026-01-17 10:00:00', 'CONCLUIDO', '2026-01-18 10:00:00', '2026-01-24 10:00:00', 'OK', 'Para o projeto de robotica.'),
(6, 49, '2026-01-22 12:00:00', 'CONCLUIDO', '2026-01-26 12:00:00', '2026-02-01 12:00:00', 'DANIFICADO', NULL),
(5, 37, '2026-01-20 14:00:00', 'CONCLUIDO', '2026-01-21 14:00:00', '2026-01-31 14:00:00', 'DANIFICADO', 'Trabalho de grupo.'),
(6, 60, '2026-01-24 13:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Trabalho de grupo.'),
(9, 63, '2026-01-31 17:00:00', 'CONCLUIDO', '2026-02-04 17:00:00', '2026-02-07 17:00:00', 'OK', 'Trabalho de grupo.'),
(3, 29, '2026-01-30 16:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Preciso para a feira de ciencias.'),
(3, 62, '2026-02-07 10:00:00', 'CONCLUIDO', '2026-02-12 10:00:00', '2026-02-20 10:00:00', 'DANIFICADO', 'Preciso para a feira de ciencias.'),
(7, 40, '2026-02-06 16:00:00', 'CONCLUIDO', '2026-02-11 16:00:00', '2026-02-17 16:00:00', 'OK', 'Prototipo do clube.'),
(3, 35, '2026-02-06 15:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Para o projeto de robotica.'),
(4, 34, '2026-02-14 16:00:00', 'CONCLUIDO', '2026-02-19 16:00:00', '2026-02-28 16:00:00', 'OK', NULL),
(3, 38, '2026-02-20 15:00:00', 'CONCLUIDO', '2026-02-25 15:00:00', '2026-03-09 15:00:00', 'OK', 'Teste de sensores.'),
(3, 55, '2026-02-20 17:00:00', 'CONCLUIDO', '2026-02-23 17:00:00', '2026-03-04 17:00:00', 'OK', NULL),
(3, 53, '2026-02-19 16:00:00', 'CONCLUIDO', '2026-02-22 16:00:00', '2026-02-25 16:00:00', 'OK', 'Preciso para a feira de ciencias.'),
(3, 26, '2026-02-24 16:00:00', 'CONCLUIDO', '2026-03-01 16:00:00', '2026-03-11 16:00:00', 'OK', 'Preciso para a feira de ciencias.'),
(3, 41, '2026-03-07 13:00:00', 'CONCLUIDO', '2026-03-10 13:00:00', '2026-03-14 13:00:00', 'OK', NULL),
(3, 65, '2026-03-11 17:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Teste de sensores.'),
(5, 65, '2026-03-13 11:00:00', 'CONCLUIDO', '2026-03-15 11:00:00', '2026-03-29 11:00:00', 'OK', 'Trabalho de grupo.'),
(2, 7, '2026-03-18 15:00:00', 'CONCLUIDO', '2026-03-19 15:00:00', '2026-03-22 15:00:00', 'OK', 'Teste de sensores.'),
(5, 58, '2026-03-17 09:00:00', 'CONCLUIDO', '2026-03-22 09:00:00', '2026-03-31 09:00:00', 'OK', 'Teste de sensores.'),
(4, 56, '2026-03-20 11:00:00', 'CONCLUIDO', '2026-03-22 11:00:00', '2026-04-02 11:00:00', 'OK', 'Prototipo do clube.'),
(3, 26, '2026-03-25 10:00:00', 'CONCLUIDO', '2026-03-30 10:00:00', '2026-04-07 10:00:00', 'OK', NULL),
(4, 62, '2026-03-28 09:00:00', 'CONCLUIDO', '2026-04-01 09:00:00', '2026-04-15 09:00:00', 'OK', NULL),
(3, 21, '2026-03-25 17:00:00', 'CONCLUIDO', '2026-03-27 17:00:00', '2026-04-10 17:00:00', 'OK', NULL),
(5, 24, '2026-04-03 17:00:00', 'CONCLUIDO', '2026-04-07 17:00:00', '2026-04-10 17:00:00', 'OK', 'Teste de sensores.'),
(3, 42, '2026-04-03 16:00:00', 'CONCLUIDO', '2026-04-07 16:00:00', '2026-04-13 16:00:00', 'OK', 'Prototipo do clube.'),
(3, 30, '2026-04-11 17:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Para o projeto de robotica.'),
(3, 4, '2026-04-16 15:00:00', 'CONCLUIDO', '2026-04-21 15:00:00', '2026-05-05 15:00:00', 'OK', 'Teste de sensores.'),
(5, 28, '2026-04-18 17:00:00', 'CONCLUIDO', '2026-04-20 17:00:00', '2026-04-23 17:00:00', 'OK', 'Prototipo do clube.'),
(3, 13, '2026-04-25 11:00:00', 'CONCLUIDO', '2026-04-26 11:00:00', '2026-05-10 11:00:00', 'OK', 'Trabalho de grupo.'),
(2, 65, '2026-04-21 14:00:00', 'CONCLUIDO', '2026-04-23 14:00:00', '2026-05-05 14:00:00', 'OK', 'Teste de sensores.'),
(3, 9, '2026-04-30 14:00:00', 'CONCLUIDO', '2026-05-02 14:00:00', '2026-05-08 14:00:00', 'PERDIDO', 'Para o projeto de robotica.'),
(3, 3, '2026-05-08 09:00:00', 'CONCLUIDO', '2026-05-09 09:00:00', '2026-05-12 09:00:00', 'OK', 'Teste de sensores.'),
(10, 5, '2026-05-09 14:00:00', 'CONCLUIDO', '2026-05-12 14:00:00', '2026-05-26 14:00:00', 'OK', 'Trabalho de grupo.'),
(3, 48, '2026-05-16 16:00:00', 'CONCLUIDO', '2026-05-20 16:00:00', '2026-06-01 16:00:00', 'OK', 'Prototipo do clube.'),
(7, 4, '2026-05-21 14:00:00', 'CONCLUIDO', '2026-05-22 14:00:00', '2026-06-04 14:00:00', 'OK', 'Teste de sensores.'),
(6, 3, '2026-05-21 10:00:00', 'CONCLUIDO', '2026-05-24 10:00:00', '2026-06-07 10:00:00', 'OK', 'Prototipo do clube.'),
(3, 16, '2026-05-26 10:00:00', 'CONCLUIDO', '2026-05-28 10:00:00', '2026-06-08 10:00:00', 'OK', 'Para o projeto de robotica.'),
(3, 46, '2026-05-28 13:00:00', 'CONCLUIDO', '2026-05-29 13:00:00', '2026-06-06 13:00:00', 'OK', 'Trabalho de grupo.'),
(3, 24, '2026-05-27 15:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Prototipo do clube.'),
(3, 30, '2026-06-06 17:00:00', 'REJEITADO', NULL, NULL, 'OK', 'Para o projeto de robotica.'),
(3, 41, '2026-06-04 17:00:00', 'CONCLUIDO', '2026-06-08 17:00:00', '2026-06-16 17:00:00', 'OK', 'Preciso para a feira de ciencias.'),
(9, 34, '2026-06-02 11:00:00', 'CONCLUIDO', '2026-06-07 11:00:00', '2026-06-09 11:00:00', 'OK', 'Teste de sensores.'),
(3, 61, '2026-06-11 09:00:00', 'CONCLUIDO', '2026-06-12 09:00:00', '2026-06-24 09:00:00', 'DANIFICADO', 'Para o projeto de robotica.'),
(4, 36, '2026-06-13 11:00:00', 'CONCLUIDO', '2026-06-17 11:00:00', '2026-06-30 11:00:00', 'OK', 'Teste de sensores.'),
(10, 40, '2026-06-11 16:00:00', 'CONCLUIDO', '2026-06-15 16:00:00', '2026-06-25 16:00:00', 'DANIFICADO', 'Preciso para a feira de ciencias.'),
(7, 5, '2026-06-17 16:00:00', 'CONCLUIDO', '2026-06-18 16:00:00', '2026-06-25 16:00:00', 'OK', NULL),
(3, 18, '2026-06-18 14:00:00', 'EM_USO', '2026-06-23 14:00:00', '2026-07-06 14:00:00', 'OK', NULL),
(10, 40, '2026-06-23 09:00:00', 'EM_USO', '2026-06-28 09:00:00', '2026-07-03 09:00:00', 'OK', 'Prototipo do clube.'),
(3, 14, '2026-06-23 15:00:00', 'EM_USO', '2026-06-27 15:00:00', '2026-07-07 15:00:00', 'OK', 'Para o projeto de robotica.'),
(3, 40, '2026-06-24 15:00:00', 'EM_USO', '2026-06-27 15:00:00', '2026-07-07 15:00:00', 'OK', 'Para o projeto de robotica.'),
(3, 37, '2026-07-04 12:00:00', 'ACEITE', '2026-07-06 12:00:00', '2026-07-12 12:00:00', 'OK', 'Teste de sensores.'),
(8, 55, '2026-07-03 12:00:00', 'PENDENTE', '2026-07-04 12:00:00', '2026-07-15 12:00:00', 'OK', 'Teste de sensores.'),
(3, 41, '2026-07-04 11:00:00', 'ACEITE', '2026-07-09 11:00:00', '2026-07-11 11:00:00', 'OK', 'Prototipo do clube.'),
(1, 48, '2026-07-07 10:00:00', 'ACEITE', '2026-07-12 10:00:00', '2026-07-24 10:00:00', 'OK', 'Para o projeto de robotica.'),
(4, 17, '2026-07-15 17:00:00', 'ACEITE', '2026-07-17 17:00:00', '2026-07-28 17:00:00', 'OK', 'Para o projeto de robotica.'),
(3, 39, '2026-07-17 10:00:00', 'ACEITE', '2026-07-18 10:00:00', '2026-07-26 10:00:00', 'OK', NULL);

-- Requisicoes de sala
INSERT INTO `requisicao` (`id_sala`,`id_utilizador`,`data`,`estado_sala`,`data_inicio`,`data_fim`,`estado_devolucao`,`observacao`) VALUES
(6, 4, '2026-01-06 16:00:00', 'CONCLUIDO', '2026-01-11 16:00:00', '2026-01-11 17:00:00', 'NORMAL', 'Preparacao para competicao.'),
(4, 3, '2026-01-12 10:00:00', 'CONCLUIDO', '2026-01-15 10:00:00', '2026-01-15 12:00:00', 'DESARRUMADA_SUJA', 'Workshop de Arduino.'),
(7, 8, '2026-01-20 14:00:00', 'CONCLUIDO', '2026-01-26 14:00:00', '2026-01-26 16:00:00', 'NORMAL', 'Sessao de programacao.'),
(4, 7, '2026-01-26 14:00:00', 'CONCLUIDO', '2026-01-30 14:00:00', '2026-01-30 16:00:00', 'DANIFICADA', 'Workshop de Arduino.'),
(8, 5, '2026-02-21 13:00:00', 'CONCLUIDO', '2026-02-22 13:00:00', '2026-02-22 15:00:00', 'DANIFICADA', 'Sessao de programacao.'),
(5, 2, '2026-03-01 14:00:00', 'CONCLUIDO', '2026-03-06 14:00:00', '2026-03-06 15:00:00', 'DESARRUMADA_SUJA', 'Sessao de programacao.'),
(6, 7, '2026-03-16 09:00:00', 'CONCLUIDO', '2026-03-22 09:00:00', '2026-03-22 12:00:00', 'DESARRUMADA_SUJA', 'Reuniao do clube.'),
(1, 6, '2026-04-24 12:00:00', 'CONCLUIDO', '2026-04-27 12:00:00', '2026-04-27 14:00:00', 'NORMAL', 'Preparacao para competicao.'),
(7, 10, '2026-04-29 13:00:00', 'CONCLUIDO', '2026-05-02 13:00:00', '2026-05-02 14:00:00', 'NORMAL', 'Preparacao para competicao.'),
(6, 5, '2026-05-06 12:00:00', 'CONCLUIDO', '2026-05-07 12:00:00', '2026-05-07 15:00:00', 'NORMAL', 'Reuniao do clube.'),
(9, 2, '2026-05-12 13:00:00', 'CONCLUIDO', '2026-05-18 13:00:00', '2026-05-18 16:00:00', 'NORMAL', 'Workshop de Arduino.'),
(10, 3, '2026-05-18 14:00:00', 'CONCLUIDO', '2026-05-22 14:00:00', '2026-05-22 15:00:00', 'NORMAL', 'Workshop de Arduino.'),
(9, 10, '2026-05-27 15:00:00', 'CONCLUIDO', '2026-06-01 15:00:00', '2026-06-01 18:00:00', 'NORMAL', 'Workshop de Arduino.'),
(4, 9, '2026-06-02 09:00:00', 'CONCLUIDO', '2026-06-07 09:00:00', '2026-06-07 10:00:00', 'NORMAL', 'Preparacao para competicao.'),
(8, 3, '2026-06-09 14:00:00', 'CONCLUIDO', '2026-06-13 14:00:00', '2026-06-13 17:00:00', 'NORMAL', 'Sessao de programacao.'),
(8, 3, '2026-06-23 09:00:00', 'CONCLUIDO', '2026-06-26 09:00:00', '2026-06-26 11:00:00', 'NORMAL', 'Workshop de Arduino.'),
(7, 3, '2026-06-30 13:00:00', 'PENDENTE', '2026-07-05 13:00:00', '2026-07-05 14:00:00', 'NORMAL', 'Reuniao do clube.');
