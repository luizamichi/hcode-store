INSERT INTO
    tb_countries (id_country, num_ibge_country, des_country, des_coi, num_ddi)
VALUES
    (1, 132, 'Afeganistão', 'AFG', 93),
    (2, 7560, 'África do Sul', 'RSA', 27),
    (3, 175, 'Albânia', 'ALB', 355),
    (4, 230, 'Alemanha', 'GER', 49),
    (5, 370, 'Andorra', 'AND', 376),
    (6, 400, 'Angola', 'ANG', 244),
    (7, 418, 'Anguilla', 'ANU', 1264),
    (8, 434, 'Antígua e Barbuda', 'ANT', 1268),
    (9, 477, 'Antilhas Holandesas', 'AHO', 599),
    (10, 531, 'Arábia Saudita', 'KSA', 966),
    (11, 590, 'Argélia', 'ALG', 213),
    (12, 639, 'Argentina', 'ARG', 54),
    (13, 647, 'Armênia', 'ARM', 374),
    (14, 655, 'Aruba', 'ARU', 297),
    (15, 698, 'Austrália', 'AUS', 61),
    (16, 728, 'Áustria', 'AUT', 43),
    (17, 736, 'Azerbaijão', 'AZE', 994),
    (18, 779, 'Bahamas', 'BAH', 1242),
    (19, 809, 'Bahrein', 'BRN', 973),
    (20, 817, 'Bangladesh', 'BAN', 880),
    (21, 833, 'Barbados', 'BAR', 1246),
    (22, 850, 'Bielorrússia', 'BLR', 375),
    (23, 876, 'Bélgica', 'BEL', 32),
    (24, 884, 'Belize', 'BIZ', 501),
    (25, 2291, 'Benin', 'BEN', 229),
    (26, 906, 'Bermudas', 'BER', 1441),
    (27, 973, 'Bolívia', 'BOL', 591),
    (28, 981, 'Bósnia e Herzegovina', 'BIH', 387),
    (29, 1015, 'Botsuana', 'BOT', 267),
    (30, 1058, 'Brasil', 'BRA', 55);


INSERT INTO
    tb_states (id_state, id_country, num_ibge_state, des_state, des_uf)
VALUES
    (1, 30, 12, 'Acre', 'AC'),
    (2, 30, 27, 'Alagoas', 'AL'),
    (3, 30, 16, 'Amapá', 'AP'),
    (4, 30, 13, 'Amazonas', 'AM'),
    (5, 30, 29, 'Bahia', 'BA'),
    (6, 30, 23, 'Ceará', 'CE'),
    (7, 30, 53, 'Espírito Santo', 'ES'),
    (8, 30, 32, 'Goiás', 'GO'),
    (9, 30, 52, 'Maranhão', 'MA'),
    (10, 30, 21, 'Mato Grosso', 'MT'),
    (11, 30, 51, 'Mato Grosso do Sul', 'MS'),
    (12, 30, 50, 'Minas Gerais', 'MG'),
    (13, 30, 31, 'Pará', 'PA'),
    (14, 30, 15, 'Paraíba', 'PB'),
    (15, 30, 25, 'Paraná', 'PR'),
    (16, 30, 41, 'Pernambuco', 'PE'),
    (17, 30, 26, 'Piauí', 'PI'),
    (18, 30, 22, 'Rio de Janeiro', 'RJ'),
    (19, 30, 24, 'Rio Grande do Norte', 'RN'),
    (20, 30, 43, 'Rio Grande do Sul', 'RS'),
    (21, 30, 33, 'Rondônia', 'RO'),
    (22, 30, 11, 'Roraima', 'RR'),
    (23, 30, 14, 'Santa Catarina', 'SC'),
    (24, 30, 42, 'São Paulo', 'SP'),
    (25, 30, 35, 'Sergipe', 'SE'),
    (26, 30, 28, 'Tocantins', 'TO'),
    (27, 30, 17, 'Distrito Federal', 'DF');


INSERT INTO
    tb_cities (id_city, id_state, num_ibge_city, des_city, num_ddd, dt_city_created_at)
VALUES
    (1, 1, 1200013, 'Acrelândia', 68, NOW()),
    (2, 1, 1200054, 'Assis Brasil', 68, NOW()),
    (3, 1, 1200104, 'Brasiléia', 68, NOW()),
    (4, 1, 1200138, 'Bujari', 68, NOW()),
    (5, 1, 1200179, 'Capixaba', 68, NOW()),
    (6, 1, 1200203, 'Cruzeiro do Sul', 68, NOW()),
    (7, 1, 1200252, 'Epitaciolândia', 68, NOW()),
    (8, 1, 1200302, 'Feijó', 68, NOW()),
    (9, 1, 1200328, 'Jordão', 68, NOW()),
    (10, 1, 1200336, 'Manoel Urbano', 68, NOW()),
    (11, 1, 1200344, 'Marechal Thaumaturgo', 68, NOW()),
    (12, 1, 1200351, 'Mâncio Lima', 68, NOW()),
    (13, 1, 1200385, 'Plácido de Castro', 68, NOW()),
    (14, 1, 1200807, 'Porto Acre', 68, NOW()),
    (15, 1, 1200393, 'Porto Walter', 68, NOW()),
    (16, 1, 1200401, 'Rio Branco', 68, NOW()),
    (17, 1, 1200427, 'Rodrigues Alves', 68, NOW()),
    (18, 1, 1200435, 'Santa Rosa do Purus', 68, NOW()),
    (19, 1, 1200500, 'Sena Madureira', 68, NOW()),
    (20, 1, 1200450, 'Senador Guiomard', 68, NOW()),
    (21, 1, 1200609, 'Tarauacá', 68, NOW()),
    (22, 1, 1200708, 'Xapuri', 68, NOW());


INSERT INTO
    tb_street_types (id_street_type, des_street_type, des_acronym)
VALUES
    (1, 'Aeroporto', 'AER'),
    (2, 'Alameda', 'AL'),
    (3, 'Área', 'A'),
    (4, 'Avenida', 'AV'),
    (5, 'Campo', NULL),
    (6, 'Chácara', 'CH'),
    (7, 'Colônia', 'COL'),
    (8, 'Condomínio', 'CON'),
    (9, 'Conjunto', 'CJ'),
    (10, 'Distrito', 'DT'),
    (11, 'Esplanada', 'ESP'),
    (12, 'Estação', 'ETC'),
    (13, 'Estrada', 'ESTR'),
    (14, 'Favela', 'FAV'),
    (15, 'Fazenda', 'FAZ'),
    (16, 'Feira', NULL),
    (17, 'Jardim', 'JD'),
    (18, 'Ladeira', 'LD'),
    (19, 'Lago', 'LGO'),
    (20, 'Lagoa', 'LGA'),
    (21, 'Largo', 'LG'),
    (22, 'Loteamento', 'LOT'),
    (23, 'Morro', NULL),
    (24, 'Núcleo', NULL),
    (25, 'Parque', 'PQ'),
    (26, 'Passarela', NULL),
    (27, 'Pátio', 'PAT'),
    (28, 'Praça', 'PC'),
    (29, 'Quadra', 'QD'),
    (30, 'Recanto', NULL),
    (31, 'Residencial', 'RES'),
    (32, 'Rodovia', 'ROD'),
    (33, 'Rua', 'R'),
    (34, 'Setor', NULL),
    (35, 'Sítio', 'SIT'),
    (36, 'Travessa', 'TV'),
    (37, 'Trecho', NULL),
    (38, 'Trevo', NULL),
    (39, 'Vale', NULL),
    (40, 'Vereda', NULL),
    (41, 'Via', 'V'),
    (42, 'Viaduto', NULL),
    (43, 'Viela', NULL),
    (44, 'Vila', 'VL');


INSERT INTO
    tb_contacts (id_contact, des_contact, des_contact_email, des_contact_subject, des_message, dt_contact_created_at)
VALUES
    (1, 'Contato', 'contato@luizamichi.com.br', 'Parabéns', 'Meus parabéns pela inauguração da loja online.', NOW()),
    (2, 'Contact', 'contact@luizamichi.com.br', 'Dúvida', 'Gostaria de saber se é possível realizar o pagamento pelo cartão de crédito.', NOW());


INSERT INTO
    tb_persons (id_person, des_person, des_email, des_cpf, num_phone, bin_photo)
VALUES
    (1, 'Olinda Jesus Canela', 'olinda@hcode.com.br', NULL, NULL, NULL),
    (2, 'Élton Novais Anjos', 'elton_novais@hcode.com.br', NULL, NULL, NULL),
    (3, 'Jacinta Júdice Caiado', 'jacinta@hcode.com.br', NULL, NULL, NULL),
    (4, 'Letízia Barros Loureiro', 'letizia@hcode.com.br', NULL, NULL, NULL),
    (5, 'Dilan Chaves Marroquim', 'dilan_chaves@hcode.com.br', NULL, NULL, NULL),
    (6, 'Nancy Lampreia Baião', 'nancy_lampreia@hcode.com.br', NULL, NULL, NULL),
    (7, 'Ruan Monteiro Lameiras', 'ruan_monteiro@hcode.com.br', NULL, NULL, NULL),
    (8, 'Cristovão Saltão Melgaço', 'cristovao@hcode.com.br', NULL, NULL, NULL),
    (9, 'Alfredo Sanches Prado', 'alfredo@hcode.com.br', NULL, NULL, NULL),
    (10, 'Alex Quesado Sacadura', 'alex_quesado@hcode.com.br', NULL, NULL, NULL),
    (11, 'Enrico Severino Rodrigues', 'enrico-rodrigues96@hcode.com.br', NULL, NULL, NULL),
    (12, 'Luiz Amichi', 'eu@luizamichi.com.br', NULL, 44998665521, NULL),
    (13, 'Andrea Carolina Rocha', 'dominio@luizamichi.com.br', NULL, NULL, NULL);


INSERT INTO
    tb_users (id_user, id_person, des_login, des_password, is_admin, dt_user_created_at, dt_user_changed_in)
VALUES
    (1, 3, 'jacinta', '$2y$10$7UN4gb/quQkgG2KKsuX3IOH/SwJJ9UlkRliBib4Smoi1blOQjznsK', 0, NOW(), NULL),
    (2, 1, 'olinda', '$2y$10$/MAk7SZl/djjYbb4AS2kOegGcYsTSC3FNxail.NnvE.5S./4iBoVu', 1, NOW(), NULL),
    (3, 10, 'alex_quesado', '$2y$10$Lo/3dgTps9CTupwm4tMNhuI54eaAWtKCvdSYSWXANzOqxT3XGSY7m', 0, NOW(), NULL),
    (4, 5, 'dilan_chaves', '$2y$10$hPQ.tp30Yk48C7SlMmLpmuG7SBC7PYaBhmkZSp5CkEbZND0oY7kqC', 0, NOW(), NULL),
    (5, 7, 'ruan_monteiro', '$2y$10$sAlasvAqZtvzKHClwYmli.sMlOKh4py9b/EI3hegKHIeUW99QHKSa', 0, NOW(), NULL),
    (6, 9, 'alfredo', '$2y$10$fmeZOOlE3b2K8fuu7wlIneybLijCEpkeML8C5.f8/paWNj8kImsP6', 1, NOW(), NULL),
    (7, 2, 'elton_novais', '$2y$10$LaLPjAgkU3MtJdp2w3A2kuoBwobCT1ju6dxBjIBZXt0PWkEeehdp.', 0, NOW(), NULL),
    (8, 4, 'letizia', '$2y$10$IjO4EqLEy7YH83vE6cB4aO1RNGjidQYLNfCKonuqekQvCoXC4ceD.', 0, NOW(), NULL),
    (9, 8, 'cristovao', '$2y$10$E8vnFxv8apXr8NXf/N2G2ukaMx5RrxmDtUQ4wxmZaPS1uB/HtoQ5m', 1, NOW(), NULL),
    (10, 6, 'nancy_lampreia', '$2y$10$KVFDxRpEOt40a2/DKIA/cuUexcCrSeeZREUjzUvRhlBS4pLdzhz.a', 0, NOW(), NULL),
    (11, 11, 'enrico', '$2y$12$JFWwC8S9Xny.vlof37ddyuTcFX4AR5sRNGvjl00LrMUu8mHQF8LnW', 1, NOW(), NULL),
    (12, 12, 'luizamichi', '$2y$12$et/EqfwCsYVO6ScZ57bmLeSGuUBjG5g6AJYp4YpxsgXyTnUpg9wUS', 1, NOW(), NULL),
    (13, 13, 'andrea_rocha', '$2y$12$N36opMKT6i.xhaIqfWd2H.z6akj9ac4B.AR8E9F0veTPI62EIs1g2', 0, NOW(), NULL);


INSERT INTO
    tb_users_logs (id_log, id_user, des_log, des_device, des_user_agent, des_php_session_id, des_source_url, des_url, dt_log_created_at)
VALUES
    (1, 1, 'Login', 'Desktop', 'Insomnia/2022.6.0', '769cukva9a9cbsocnasovmo01i', NULL, '/api/login', NOW());


INSERT INTO
    tb_mails (id_mail, des_recipient_email, des_recipient_name, des_subject, des_content, des_files, is_sent, dt_mail_created_at, dt_mail_changed_in)
VALUES
    (1, 'support@luizamichi.com.br', 'Support - Luiz Amichi', '[Hcode Store] Boas vindas', 'Olá, seja bem-vindo à Hcode Store.', NULL, 1, NOW(), NULL),
    (2, 'eu@luizamichi.com.br', 'Luiz Amichi', '[Hcode Store] Boas vindas', 'Olá, seja bem-vindo à Hcode Store. Confira nossas ofertas em nosso site!', '/res/multiShop/img/product-1.jpg;/res/multiShop/img/product-2.jpg;/res/multiShop/img/product-3.jpg', 1, NOW(), NULL),
    (3, 'dominio@luizamichi.com.br', 'Domínio - Luiz Amichi', '[Hcode Store] Confirmação de cadastro', 'Olá, precisamos confirmar que foi você mesmo que realizou o cadastro em nossa plataforma. Por favor, realize o login em nosso site para confirmarmos sua identidade.', NULL, 0, NOW(), NULL);


INSERT INTO
    tb_users_passwords_recoveries (id_recovery, id_user, des_ip, des_security_key, dt_recovery_created_at, dt_recovery)
VALUES
    (1, 2, '127.0.0.1', '1e9ec74e9328a7f49c996701b63064a6', NOW(), NULL),
    (2, 2, '127.0.0.1', MD5(RAND()), NOW(), NULL);


INSERT INTO
    tb_addresses (id_address, id_person, id_city, id_street_type, des_address, des_number, des_district, des_complement, des_reference, num_zip_code, dt_address_created_at, dt_address_changed_in)
VALUES
    (1, 5, 1, 4, 'Paraná', '296', 'Centro', 'Comércio', 'Correios', 69945000, NOW(), NULL),
    (2, 9, 2, 33, 'Dom Giocondo Maria Grotte', '230', 'Centro', 'Casa', 'Casa bege', 69935970, NOW(), NULL),
    (3, 4, 5, 33, 'Ocimar Tessinari', '07', 'Conquista', 'Comércio', 'Distribuidora de bebidas', 69931970, NOW(), NULL),
    (4, 2, 6, 33, 'Rego Barros', '73', 'Centro', 'Apartamento', 'Prédio em construção', 69980970, NOW(), NULL),
    (5, 7, 8, 4, 'Plácido de Castro', '871', 'Centro', 'Casa', 'Ao lado do salão de beleza', 69960970, NOW(), NULL),
    (6, 3, 9, 33, 'Romildo Magalhães', 'S/N', 'Centro', 'Casa', 'Casa do portão vermelho', 69975970, NOW(), NULL),
    (7, 1, 22, 33, '24 de Janeiro', '270', 'Centro', 'Comércio', 'Esquina', 69930000, NOW(), NULL),
    (8, 8, 18, 33, 'Coronel José Ferreira', '1498', 'Centro', 'Comércio', 'Correios', 69955000, NOW(), NULL),
    (9, 6, 14, 33, 'Margaridas', '131', 'Centro', 'Comércio', 'Próximo a lanchonete', 69927000, NOW(), NULL),
    (10, 10, 13, 4, 'Diamantino Augusto de Macedo', '580', 'Centro', 'Hotel', 'Perto das casas coloridas', 69928000, NOW(), NULL);


INSERT INTO
    tb_products (id_product, des_product, des_description, bin_image, vl_price, vl_width, vl_height, vl_length, vl_weight, num_quantity_stock, is_national, des_slug, dt_product_created_at, dt_product_changed_in)
VALUES
    (1, 'Teclado Redragon Fizz K617 RGB', NULL, NULL, 200, 0.31, 0.13, 0.4, 0.49, 5, 0, 'redragon-fizz-rgb', NOW(), NULL),
    (2, 'Iphone 12 Apple 64GB', NULL, NULL, 4230.45, 0.07, 0.14, 0.07, 0.16, 3, 1, 'iphone-12-apple-64gb', NOW(), NULL),
    (3, 'Smartphone Motorola Moto G5 Plus', NULL, NULL, 1135.23, 0.15, 0.7, 0.7, 0.16, 7, 0, 'smartphone-motorola-moto-g5-plus', NOW(), NULL),
    (4, 'Smartphone Moto Z Play', NULL, NULL, 1887.78, 0.14, 0.9, 0.11, 0.13, 4, 0, 'smartphone-moto-z-play', NOW(), NULL),
    (5, 'Smartphone Samsung Galaxy J5 Pro', NULL, NULL, 1299, 0.14, 0.7, 0.8, 0.16, 1, 0, 'smartphone-samsung-galaxy-j5', NOW(), NULL),
    (6, 'Smartphone Samsung Galaxy J7 Prime', NULL, NULL, 1149, 0.15, 0.7, 0.8, 0.16, 6, 0, 'smartphone-samsung-galaxy-j7', NOW(), NULL),
    (7, 'Smartphone Samsung Galaxy J3 Dual', NULL, NULL, 679.9, 0.14, 0.7, 0.7, 0.14, 2, 0, 'smartphone-samsung-galaxy-j3', NOW(), NULL);


INSERT INTO
    tb_categories (id_category, des_category, des_nickname, fk_category, dt_category_created_at, dt_category_changed_in)
VALUES
    (1, 'Informática', 'informatica', NULL, NOW(), NULL),
    (2, 'Eletrodoméstico', 'eletrodomestico', NULL, NOW(), NULL),
    (3, 'Vestuário', 'vestuario', NULL, NOW(), NULL),
    (4, 'Cosmético', 'cosmetico', NULL, NOW(), NULL),
    (5, 'Smartphone', 'smartphone', NULL, NOW(), NULL),
    (6, 'Android', 'android', 5, NOW(), NULL),
    (7, 'iOS', 'ios', 5, NOW(), NULL);


INSERT INTO
    tb_products_categories (id_product, id_category)
VALUES
    (1, 1),
    (2, 5),
    (2, 7),
    (3, 5),
    (3, 6),
    (4, 5),
    (4, 6),
    (5, 5),
    (5, 6),
    (6, 5),
    (6, 6),
    (7, 5),
    (7, 6);


INSERT INTO
    tb_wishlist (id_user, id_product, dt_product_added_at)
VALUES
    (1, 1, NOW()),
    (2, 2, NOW()),
    (3, 3, NOW());


INSERT INTO
    tb_carts (id_cart, des_session_id, id_user, id_address, num_temporary_zip_code, vl_freight, des_type_freight, num_days, dt_cart_created_at)
VALUES
    (1, '9g2b4a2bfnnv279f232te9u7g0', NULL, NULL, NULL, NULL, NULL, NULL, NOW()),
    (2, '2v0r2o3fvvvurtmdrr07v1opuo', 2, NULL, NULL, NULL, NULL, NULL, NOW()),
    (3, 'oab5dlightjoldg159621hibqc', NULL, NULL, 87020250, 331.99, '1', 10, NOW()),
    (4, 'vf9qrt3obtfijsk77arq0sj0id', NULL, NULL, 87020260, 113.16, '1', 5, NOW()),
    (5, 'u1b87l8rjsiediu9rekathchq5', 3, NULL, 69928000, 114.5, '1', 3, NOW());


INSERT INTO
    tb_carts_products (id_cart_product, id_cart, id_product, vl_unit_price, dt_removed, dt_added_to_cart)
VALUES
    (1, 2, 3, 1135.23, NOW(), NOW()),
    (2, 2, 3, 1135.23, NOW(), NOW()),
    (3, 2, 7, 679.9, NOW(), NOW()),
    (4, 2, 7, 679.9, NULL, NOW()),
    (5, 2, 4, 1887.78, NULL, NOW()),
    (6, 2, 4, 1887.78, NULL, NOW()),
    (7, 2, 4, 1887.78, NULL, NOW()),
    (8, 3, 3, 1135.23, NULL, NOW()),
    (9, 3, 3, 1135.23, NULL, NOW()),
    (10, 3, 3, 1135.23, NULL, NOW()),
    (11, 4, 5, 1299, NULL, NOW()),
    (12, 5, 5, 1299, NULL, NOW());


INSERT INTO
    tb_orders_status (id_status, des_status, num_code, dt_status_created_at)
VALUES
    (1, 'Em aberto', 1, NOW()),
    (2, 'Aguardando pagamento', 2, NOW()),
    (3, 'Pagamento confirmado', 3, NOW()),
    (4, 'Pedido enviado', 4, NOW()),
    (5, 'Pedido entregue', 5, NOW()),
    (6, 'Pedido cancelado', 6, NOW());


INSERT INTO
    tb_orders (id_order, id_cart, id_user, id_status, id_address, vl_total, des_code, des_annotation, dt_order_created_at)
VALUES
    (1, 2, 2, 1, 7, 8231.02, UUID_SHORT(), NULL, NOW()),
    (2, 5, 3, 6, 10, 0, UUID_SHORT(), NULL, NOW());


INSERT INTO
    tb_topics_types (id_type, des_type, des_summary, des_route, dt_type_created_at)
VALUES
    (1, 'Perguntas frequentes', 'Tire todas as suas dúvidas aqui, confira nossas perguntas frequentes sobre cadastro, compra, garantia e outros assuntos.', 'faq', NOW()),
    (2, 'Código de conduta ética', 'O Código de conduta ética reúne os princípios e valores adotados em nossa organização, objetivando orientar a conduta pessoal e profissional dos colaboradores, prestadores de serviços e terceiros, a fim de prestar o auxílio necessário para que desempenhem seu papel profissional de forma íntegra.', 'cce', NOW()),
    (3, 'Política de cookies', 'Pensando na manutenção e tratamento dos seus dados pessoais criamos nossa Política de Privacidade e Proteção de Dados Pessoais. Aqui é possível entender as nossas práticas sobre o tratamento dos seus dados, o propósito dessa coleta e como buscamos melhorar a experiência do usuário na plataforma.', 'cookie-policy', NOW()),
    (4, 'Política de privacidade', 'Aqui você encontrará informações importantes sobre os seus direitos e obrigações, bem como dos procedimentos a serem adotados.', 'privacy-policies', NOW());


INSERT INTO
    tb_topics (id_topic, id_type, des_topic, dt_topic_created_at)
VALUES
    (1, 1, 'Pedido', NOW()),
    (2, 1, 'Pagamento e estorno', NOW());


INSERT INTO
    tb_subtopics (id_subtopic, id_topic, id_type, des_subtopic, des_text, dt_subtopic_created_at, dt_subtopic_changed_in)
VALUES
    (1, 1, NULL, 'Dúvidas sobre o status do pedido', 'Para verificar o status do seu pedido, acesse sua conta e clique para acessar a lista de pedidos. Na lista, procure pelo pedido desejado.', NOW(), NULL),
    (2, 1, NULL, 'Posso alterar o endereço de entrega com o objeto em transporte?', 'Quando seu pedido já estiver em transporte, infelizmente não será possível alterar o endereço de entrega.', NOW(), NULL),
    (3, 1, NULL, 'Qual o prazo para confirmação do pagamento?', 'Pagamentos efetuados via boleto bancário e cartão de crédito têm um prazo de 1 a 2 dias úteis para confirmação. Já para pagamentos via PIX, o prazo de confirmação é de até 30 minutos.', NOW(), NULL),
    (4, NULL, 3, 'O que são cookies', 'Os cookies são pequenos arquivos enviados pelos sites durante a navegação. Eles ficam armazenados nos dispositivos de computadores e dispositivos móveis, que permitem melhorar o desempenho e a experiência de navegação dos nossos clientes.', NOW(), NULL);