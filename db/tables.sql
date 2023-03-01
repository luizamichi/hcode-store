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


DROP TABLE IF EXISTS tb_mails;

CREATE TABLE tb_mails (
    id_mail INT UNSIGNED AUTO_INCREMENT COMMENT 'PK do e-mail',
    des_recipient_email VARCHAR(128) NOT NULL COMMENT 'E-mail do destinatário',
    des_recipient_name VARCHAR(64) NOT NULL COMMENT 'Nome do destinatário do e-mail',
    des_subject VARCHAR(256) NOT NULL COMMENT 'Assunto do e-mail',
    des_content LONGTEXT NOT NULL COMMENT 'Conteúdo do e-mail',
    des_files TEXT NULL COMMENT 'Arquivos anexados ao e-mail',
    is_sent TINYINT NOT NULL DEFAULT 0 COMMENT 'E-mail foi enviado?',
    dt_mail_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do e-mail',
    dt_mail_changed_in TIMESTAMP NULL COMMENT 'Data da última alteração do e-mail',
    CONSTRAINT pk_tb_mails PRIMARY KEY (id_mail)
) COMMENT = 'E-mails';


DROP TABLE IF EXISTS tb_users_passwords_recoveries;

CREATE TABLE tb_users_passwords_recoveries (
    id_recovery INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK da recuperação de senha',
    id_user INT UNSIGNED NOT NULL COMMENT 'Usuário',
    des_ip VARCHAR(64) NOT NULL COMMENT 'IP do usuário',
    des_security_key VARCHAR(32) NOT NULL COMMENT 'Chave de segurança',
    dt_recovery_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data da solicitação',
    dt_recovery TIMESTAMP NULL COMMENT 'Data da recuperação',
    CONSTRAINT pk_tb_users_passwords_recoveries PRIMARY KEY (id_recovery),
    CONSTRAINT fk_tb_users_passwords_recoveries_to_tb_users FOREIGN KEY (id_user) REFERENCES tb_users (id_user) ON DELETE CASCADE
) COMMENT = 'Recuperações de senha';


DROP TABLE IF EXISTS tb_addresses;

CREATE TABLE tb_addresses (
    id_address INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do endereço',
    id_person INT UNSIGNED NOT NULL COMMENT 'Pessoa que mora no endereço',
    id_city INT UNSIGNED NOT NULL COMMENT 'Cidade do endereço',
    id_street_type INT UNSIGNED NULL COMMENT 'Tipo do logradouro',
    des_address VARCHAR(128) NOT NULL COMMENT 'Logradouro do endereço',
    des_number VARCHAR(8) NOT NULL DEFAULT 'S/N' COMMENT 'Número do endereço',
    des_district VARCHAR(32) NULL COMMENT 'Bairro do endereço',
    des_complement VARCHAR(32) NOT NULL COMMENT 'Complemento do endereço',
    des_reference VARCHAR(32) NULL COMMENT 'Referência do endereço',
    num_zip_code bigint(8) UNSIGNED ZEROFILL NOT NULL COMMENT 'Código postal do endereço',
    dt_address_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do endereço',
    dt_address_changed_in TIMESTAMP NULL COMMENT 'Data da última alteração do endereço',
    CONSTRAINT pk_tb_addresses PRIMARY KEY (id_address),
    CONSTRAINT fk_tb_addresses_to_tb_persons FOREIGN KEY (id_person) REFERENCES tb_persons (id_person) ON DELETE NO ACTION,
    CONSTRAINT fk_tb_addresses_to_tb_cities FOREIGN KEY (id_city) REFERENCES tb_cities (id_city) ON DELETE NO ACTION,
    CONSTRAINT fk_tb_addresses_to_tb_street_types FOREIGN KEY (id_street_type) REFERENCES tb_street_types (id_street_type) ON DELETE NO ACTION,
    CONSTRAINT uk_tb_addresses_id_person UNIQUE KEY (id_person)
) COMMENT = 'Endereços';


DROP TABLE IF EXISTS tb_products;

CREATE TABLE tb_products (
    id_product INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do produto',
    des_product VARCHAR(64) NOT NULL COMMENT 'Nome do produto',
    des_description TEXT NULL COMMENT 'Descrição do produto',
    bin_image LONGBLOB NULL COMMENT 'Imagem do produto',
    vl_price DECIMAL(10, 2) NOT NULL COMMENT 'Preço do produto',
    vl_width DECIMAL(10, 2) NOT NULL COMMENT 'Largura do produto',
    vl_height DECIMAL(10, 2) NOT NULL COMMENT 'Altura do produto',
    vl_length DECIMAL(10, 2) NOT NULL COMMENT 'Comprimento do produto',
    vl_weight DECIMAL(10, 2) NOT NULL COMMENT 'Peso do produto',
    num_quantity_stock INT UNSIGNED NOT NULL COMMENT 'Quantidade em estoque',
    is_national TINYINT DEFAULT 1 NOT NULL COMMENT 'Produto é nacional?',
    des_slug VARCHAR(256) NOT NULL COMMENT 'Identificador único do produto (utilizado na URL)',
    dt_product_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do produto',
    dt_product_changed_in TIMESTAMP NULL COMMENT 'Data da última alteração do produto',
    CONSTRAINT pk_tb_products PRIMARY KEY (id_product),
    CONSTRAINT uk_tb_products_des_slug UNIQUE KEY (des_slug)
) COMMENT = 'Produtos';


DROP TABLE IF EXISTS tb_categories;

CREATE TABLE tb_categories (
    id_category INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK da categoria',
    des_category VARCHAR(32) NOT NULL COMMENT 'Descrição da categoria',
    des_nickname VARCHAR(256) NOT NULL COMMENT 'Identificador único da categoria (utilizado na URL)',
    fk_category INT UNSIGNED NULL COMMENT 'Categoria mãe',
    dt_category_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro da categoria',
    dt_category_changed_in TIMESTAMP NULL COMMENT 'Data da última alteração da categoria',
    CONSTRAINT pk_tb_categories PRIMARY KEY (id_category),
    CONSTRAINT fk_tb_categories_to_tb_categories FOREIGN KEY (fk_category) REFERENCES tb_categories (id_category) ON DELETE SET NULL,
    CONSTRAINT uk_tb_categories_des_category UNIQUE KEY (des_category),
    CONSTRAINT uk_tb_categories_des_nickname UNIQUE KEY (des_nickname)
) COMMENT = 'Categorias';


DROP TABLE IF EXISTS tb_products_categories;

CREATE TABLE tb_products_categories (
    id_product INT UNSIGNED NOT NULL COMMENT 'Produto',
    id_category INT UNSIGNED NOT NULL COMMENT 'Categoria',
    CONSTRAINT pk_tb_products_categories PRIMARY KEY (id_product, id_category),
    CONSTRAINT fk_tb_products_categories_to_tb_products FOREIGN KEY (id_product) REFERENCES tb_products (id_product) ON DELETE CASCADE,
    CONSTRAINT fk_tb_products_categories_to_tb_categories FOREIGN KEY (id_category) REFERENCES tb_categories (id_category) ON DELETE CASCADE
) COMMENT = 'Produtos x Categorias';


DROP TABLE IF EXISTS tb_wishlist;

CREATE TABLE tb_wishlist (
    id_user INT UNSIGNED NOT NULL COMMENT 'Usuário',
    id_product INT UNSIGNED NOT NULL COMMENT 'Produto',
    dt_product_added_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de adição do produto à lista de desejos',
    CONSTRAINT pk_tb_wishlist PRIMARY KEY (id_user, id_product),
    CONSTRAINT fk_tb_wishlist_to_tb_users FOREIGN KEY (id_user) REFERENCES tb_users (id_user) ON DELETE CASCADE,
    CONSTRAINT fk_tb_wishlist_to_tb_products FOREIGN KEY (id_product) REFERENCES tb_products (id_product) ON DELETE CASCADE
) COMMENT = 'Lista de desejos';


DROP TABLE IF EXISTS tb_carts;

CREATE TABLE tb_carts (
    id_cart INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do carrinho',
    des_session_id VARCHAR(64) NOT NULL COMMENT 'ID da sessão',
    id_user INT UNSIGNED NULL COMMENT 'Usuário do carrinho',
    id_address INT UNSIGNED NULL COMMENT 'Endereço de entrega',
    num_temporary_zip_code bigint(8) UNSIGNED ZEROFILL NULL COMMENT 'Código postal temporário do endereço de entrega',
    vl_freight DECIMAL(10, 2) NULL COMMENT 'Valor do frete',
    des_type_freight VARCHAR(32) NULL COMMENT 'Tipo do frete',
    num_days INT UNSIGNED NULL COMMENT 'Dias para entrega',
    dt_cart_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de criação do carrinho',
    CONSTRAINT pk_tb_carts PRIMARY KEY (id_cart),
    CONSTRAINT fk_tb_carts_to_tb_users FOREIGN KEY (id_user) REFERENCES tb_users (id_user) ON DELETE NO ACTION,
    CONSTRAINT fk_tb_carts_to_tb_addresses FOREIGN KEY (id_address) REFERENCES tb_addresses (id_address) ON DELETE SET NULL
) COMMENT = 'Carrinho de compras';


DROP TABLE IF EXISTS tb_carts_products;

CREATE TABLE tb_carts_products (
    id_cart_product INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do carrinho x produto',
    id_cart INT UNSIGNED NOT NULL COMMENT 'Carrinho',
    id_product INT UNSIGNED NULL COMMENT 'Produto',
    vl_unit_price DECIMAL(10, 2) NOT NULL DEFAULT 0.0 COMMENT 'Preço unitário do produto',
    dt_removed TIMESTAMP NULL COMMENT 'Data de remoção do produto do carrinho',
    dt_added_to_cart TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de inserção do produto no carrinho',
    CONSTRAINT pk_tb_carts_products PRIMARY KEY (id_cart_product),
    CONSTRAINT fk_tb_carts_products_to_tb_carts FOREIGN KEY (id_cart) REFERENCES tb_carts (id_cart) ON DELETE CASCADE,
    CONSTRAINT fk_tb_carts_products_to_tb_products FOREIGN KEY (id_product) REFERENCES tb_products (id_product) ON DELETE SET NULL
) COMMENT = 'Carrinhos x Produtos';


DROP TABLE IF EXISTS tb_orders_status;

CREATE TABLE tb_orders_status (
    id_status INT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT 'PK do status de pedido',
    des_status VARCHAR(32) NOT NULL COMMENT 'Descrição do status de pedido',
    num_code TINYINT UNSIGNED NOT NULL COMMENT 'Código do status de pedido',
    dt_status_created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Data de cadastro do status do pedido',
    CONSTRAINT pk_tb_orders_status PRIMARY KEY (id_status),
    CONSTRAINT uk_tb_orders_status_des_status UNIQUE KEY (des_status),
    CONSTRAINT uk_tb_orders_status_num_code UNIQUE KEY (num_code)
) COMMENT = 'Status de pedidos';