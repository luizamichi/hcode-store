-- BOOL - is
-- BLOB, MEDIUMBLOB, LONGBLOB - bin
-- CHAR, VARCHAR, TEXT - des
-- DATETIME, DATE, TIMESTAMP - dt
-- DECIMAL - vl
-- FOREIGN KEY - fk, id
-- INT, TINYINT, BIGINT - num
-- PRIMARY KEY - id


DROP TABLE IF EXISTS tb_countries;

CREATE TABLE tb_countries (
    id_country INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do país',
    num_ibge_country INT UNSIGNED NULL COMMENT 'Código IBGE do país',
    des_country VARCHAR(32) NOT NULL COMMENT 'Nome do país',
    des_coi CHAR(3) NOT NULL COMMENT 'Código COI do país',
    num_ddi INT UNSIGNED NULL COMMENT 'Código internacional de telefone do país',
    CONSTRAINT pk_tb_countries PRIMARY KEY (id_country),
    CONSTRAINT uk_tb_countries_num_ibge_country UNIQUE KEY (num_ibge_country),
    CONSTRAINT uk_tb_countries_des_coi UNIQUE KEY (des_coi)
) COMMENT = 'Países';


DROP TABLE IF EXISTS tb_states;

CREATE TABLE tb_states (
    id_state INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do estado',
    id_country INT UNSIGNED NOT NULL COMMENT 'País do estado',
    num_ibge_state INT UNSIGNED NULL COMMENT 'Código IBGE do estado',
    des_state VARCHAR(32) NOT NULL COMMENT 'Nome do estado',
    des_uf CHAR(2) NOT NULL COMMENT 'Unidade federativa',
    CONSTRAINT pk_tb_states PRIMARY KEY (id_state),
    CONSTRAINT fk_tb_states_to_tb_countries FOREIGN KEY (id_country) REFERENCES tb_countries (id_country) ON DELETE CASCADE,
    CONSTRAINT uk_tb_states_num_ibge_state UNIQUE KEY (num_ibge_state),
    CONSTRAINT uk_tb_states_des_uf UNIQUE KEY (des_uf, id_country)
) COMMENT = 'Estados';