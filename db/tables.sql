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


DROP TABLE IF EXISTS tb_cities;

CREATE TABLE tb_cities (
    id_city INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK da cidade',
    id_state INT UNSIGNED NOT NULL COMMENT 'Estado da cidade',
    num_ibge_city INT UNSIGNED NULL COMMENT 'Código IBGE da cidade',
    des_city VARCHAR(32) NOT NULL COMMENT 'Nome da cidade',
    num_ddd TINYINT UNSIGNED NULL COMMENT 'Código de discagem telefônica da cidade',
    dt_city_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro da cidade',
    CONSTRAINT pk_tb_cities PRIMARY KEY (id_city),
    CONSTRAINT fk_tb_cities_to_tb_states FOREIGN KEY (id_state) REFERENCES tb_states (id_state) ON DELETE CASCADE,
    CONSTRAINT uk_tb_cities_num_ibge_city UNIQUE KEY (num_ibge_city),
    CONSTRAINT uk_tb_cities_des_city UNIQUE KEY (des_city, id_state)
) COMMENT = 'Cidades';


DROP TABLE IF EXISTS tb_street_types;

CREATE TABLE tb_street_types (
    id_street_type INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'ID do tipo de logradouro',
    des_street_type VARCHAR(32) NOT NULL COMMENT 'Tipo do logradouro',
    des_acronym VARCHAR(4) NULL COMMENT 'Acrônimo do tipo do logradouro',
    CONSTRAINT pk_tb_street_types PRIMARY KEY (id_street_type),
    CONSTRAINT uk_tb_street_types_des_street_type UNIQUE KEY (des_street_type),
    CONSTRAINT uk_tb_street_types_des_acronym UNIQUE KEY (des_acronym)
) COMMENT = 'Tipos de logradouro';


DROP TABLE IF EXISTS tb_contacts;

CREATE TABLE tb_contacts (
    id_contact INT UNSIGNED AUTO_INCREMENT COMMENT 'PK do contato',
    des_contact VARCHAR(64) NOT NULL COMMENT 'Nome do contato',
    des_contact_email VARCHAR(128) NOT NULL COMMENT 'E-mail do contato',
    des_contact_subject VARCHAR(256) NOT NULL COMMENT 'Assunto do contato',
    des_message LONGTEXT NOT NULL COMMENT 'Mensagem do contato',
    dt_contact_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do contato',
    CONSTRAINT pk_tb_contacts PRIMARY KEY (id_contact)
) COMMENT = 'Contatos';