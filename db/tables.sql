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


DROP TABLE IF EXISTS tb_persons;

CREATE TABLE tb_persons (
    id_person INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK da pessoa',
    des_person VARCHAR(64) NOT NULL COMMENT 'Nome completo da pessoa',
    des_email VARCHAR(128) NULL COMMENT 'E-mail da pessoa',
    des_cpf CHAR(11) NULL COMMENT 'CPF da pessoa',
    num_phone bigint UNSIGNED NULL COMMENT 'Telefone da pessoa',
    bin_photo MEDIUMBLOB NULL COMMENT 'Foto da pessoa',
    CONSTRAINT pk_tb_persons PRIMARY KEY (id_person),
    CONSTRAINT uk_tb_persons_des_email UNIQUE KEY (des_email),
    CONSTRAINT uk_tb_persons_des_cpf UNIQUE KEY (des_cpf)
) COMMENT = 'Pessoas';


DROP TABLE IF EXISTS tb_users;

CREATE TABLE tb_users (
    id_user INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do usuário',
    id_person INT UNSIGNED NOT NULL COMMENT 'Pessoa que tem acesso',
    des_login VARCHAR(64) NOT NULL COMMENT 'Login do usuário',
    des_password VARCHAR(256) NOT NULL COMMENT 'Senha do usuário',
    is_admin TINYINT NOT NULL DEFAULT 0 COMMENT 'Usuário é administrador?',
    dt_user_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do usuário',
    dt_user_changed_in TIMESTAMP NULL COMMENT 'Data da última alteração do usuário',
    CONSTRAINT pk_tb_users PRIMARY KEY (id_user),
    CONSTRAINT fk_tb_users_to_tb_persons FOREIGN KEY (id_person) REFERENCES tb_persons (id_person) ON DELETE CASCADE,
    CONSTRAINT uk_tb_users_des_login UNIQUE KEY (des_login)
) COMMENT = 'Usuários';


DROP TABLE IF EXISTS tb_users_logs;

CREATE TABLE tb_users_logs (
    id_log INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do log',
    id_user INT UNSIGNED NOT NULL COMMENT 'Usuário logado',
    des_log VARCHAR(128) NOT NULL COMMENT 'Descrição do log',
    des_device VARCHAR(256) NULL COMMENT 'Identificador da máquina do usuário',
    des_user_agent VARCHAR(256) NOT NULL COMMENT 'Navegador web do usuário',
    des_php_session_id VARCHAR(64) NOT NULL COMMENT 'ID da sessão PHP',
    des_source_url VARCHAR(256) NULL COMMENT 'URL de origem',
    des_url VARCHAR(256) NOT NULL COMMENT 'URL acessada pelo usuário',
    dt_log_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data do log',
    CONSTRAINT pk_tb_users_logs PRIMARY KEY (id_log),
    CONSTRAINT fk_tb_users_logs_to_tb_users FOREIGN KEY (id_user) REFERENCES tb_users (id_user) ON DELETE CASCADE
) COMMENT = 'Logs';