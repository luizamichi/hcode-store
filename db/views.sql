DROP VIEW IF EXISTS vw_countries;

CREATE VIEW vw_countries AS
    SELECT id_country, num_ibge_country, des_country, des_coi, num_ddi, GROUP_CONCAT(des_uf SEPARATOR ', ') des_uf
      FROM tb_countries
      LEFT JOIN tb_states USING (id_country)
     GROUP BY id_country;


DROP VIEW IF EXISTS vw_states;

CREATE VIEW vw_states AS
    SELECT id_state, num_ibge_state, des_state, des_uf,
           id_country, num_ibge_country, des_country, des_coi, num_ddi
      FROM tb_states
     INNER JOIN tb_countries USING (id_country);


DROP VIEW IF EXISTS vw_cities;

CREATE VIEW vw_cities AS
    SELECT id_city, num_ibge_city, des_city, num_ddd, dt_city_created_at,
           id_state, id_country, num_ibge_state, des_state, des_uf
      FROM tb_cities
     INNER JOIN tb_states USING (id_state);


DROP VIEW IF EXISTS vw_street_types;

CREATE VIEW vw_street_types AS
    SELECT id_street_type, des_street_type, des_acronym
      FROM tb_street_types;


DROP VIEW IF EXISTS vw_contacts;

CREATE VIEW vw_contacts AS
    SELECT id_contact, des_contact, des_contact_email, num_contact_phone, des_contact_subject, des_message, dt_contact_created_at
      FROM tb_contacts;


DROP VIEW IF EXISTS vw_persons;

CREATE VIEW vw_persons AS
    SELECT id_person, des_person, des_email, des_cpf, num_phone, bin_photo
      FROM tb_persons;


DROP VIEW IF EXISTS vw_users;

CREATE VIEW vw_users AS
    SELECT id_user, des_login, des_password, is_admin, dt_user_created_at, dt_user_changed_in,
           id_person, des_person, des_email, des_cpf, num_phone, bin_photo
      FROM tb_users
     INNER JOIN tb_persons USING (id_person);


DROP VIEW IF EXISTS vw_users_logs;

CREATE VIEW vw_users_logs AS
    SELECT id_log, des_log, des_device, des_user_agent, des_php_session_id, des_source_url, des_url, dt_log_created_at,
           id_user, id_person, des_login, des_password, is_admin, dt_user_created_at, dt_user_changed_in
      FROM tb_users_logs
     INNER JOIN tb_users USING (id_user);


DROP VIEW IF EXISTS vw_mails;

CREATE VIEW vw_mails AS
    SELECT id_mail, des_recipient_email, des_recipient_name, des_subject, des_content, des_files, is_sent, dt_mail_created_at, dt_mail_changed_in
      FROM tb_mails;


DROP VIEW IF EXISTS vw_users_passwords_recoveries;

CREATE VIEW vw_users_passwords_recoveries AS
    SELECT id_recovery, des_ip, des_security_key, dt_recovery_created_at, dt_recovery,
           id_user, id_person, des_login, des_password, is_admin, dt_user_created_at, dt_user_changed_in
      FROM tb_users_passwords_recoveries
     INNER JOIN tb_users USING (id_user);


DROP VIEW IF EXISTS vw_addresses;

CREATE VIEW vw_addresses AS
    SELECT id_address, des_address, des_number, des_district, des_complement, des_reference, num_zip_code, dt_address_created_at, dt_address_changed_in,
           id_person, des_person, des_email, des_cpf, num_phone, bin_photo,
           id_city, id_state, num_ibge_city, des_city, num_ddd, dt_city_created_at,
           id_street_type, des_street_type, des_acronym
      FROM tb_addresses
     INNER JOIN tb_persons USING (id_person)
     INNER JOIN tb_cities USING (id_city)
      LEFT OUTER JOIN tb_street_types USING (id_street_type);


DROP VIEW IF EXISTS vw_products;

CREATE VIEW vw_products AS
    SELECT id_product, des_product, des_description, bin_image, vl_price, vl_width, vl_height, vl_length, vl_weight,
           num_quantity_stock, is_national, des_slug, dt_product_created_at, dt_product_changed_in
      FROM tb_products;


DROP VIEW IF EXISTS vw_categories;

CREATE VIEW vw_categories AS
    SELECT c.id_category, c.des_category, c.des_nickname, c.dt_category_created_at, c.dt_category_changed_in,
           c.fk_category, s.des_category des_super_category, s.des_nickname des_super_nickname, s.fk_category fk_super_category, s.dt_category_created_at dt_super_category_created_at, s.dt_category_changed_in dt_super_category_changed_in
      FROM tb_categories c
      LEFT OUTER JOIN tb_categories s ON c.fk_category = s.id_category;


DROP VIEW IF EXISTS vw_products_categories;

CREATE VIEW vw_products_categories AS
     SELECT p.id_product, p.des_product, p.des_description, p.bin_image, p.vl_price, p.vl_width, p.vl_height, p.vl_length, p.vl_weight, p.num_quantity_stock, p.is_national, p.des_slug, p.dt_product_created_at, p.dt_product_changed_in,
            c.id_category, c.des_category, c.des_nickname, c.fk_category, c.dt_category_created_at, c.dt_category_changed_in
       FROM tb_products_categories
      INNER JOIN tb_products p USING (id_product)
      INNER JOIN tb_categories c USING (id_category);


DROP VIEW IF EXISTS vw_wishlist;

CREATE VIEW vw_wishlist AS
    SELECT dt_product_added_at,
           id_user, des_login, des_password, is_admin, dt_user_created_at, dt_user_changed_in, id_person, des_person, des_email, des_cpf, num_phone, bin_photo,
           id_product, des_product, des_description, bin_image, vl_price, vl_width, vl_height, vl_length, vl_weight, num_quantity_stock, is_national, des_slug, dt_product_created_at, dt_product_changed_in
      FROM tb_wishlist
     INNER JOIN tb_users USING (id_user)
     INNER JOIN tb_products USING (id_product);


DROP VIEW IF EXISTS vw_carts;

CREATE VIEW vw_carts AS
    SELECT c.id_cart, c.des_session_id, c.num_temporary_zip_code, c.vl_freight, c.des_type_freight, c.num_days, c.dt_cart_created_at,
           u.id_user, u.id_person, u.des_login, u.des_password, u.is_admin, u.dt_user_created_at, u.dt_user_changed_in,
           a.id_address, a.id_city, a.id_street_type, a.des_address, a.des_number, a.des_district, a.des_complement, a.des_reference, a.num_zip_code, a.dt_address_created_at, a.dt_address_changed_in
      FROM tb_carts c
      LEFT OUTER JOIN tb_users u USING (id_user)
      LEFT OUTER JOIN tb_addresses a USING (id_address);


DROP VIEW IF EXISTS vw_carts_products;

CREATE VIEW vw_carts_products AS
    SELECT id_cart_product, vl_unit_price, dt_removed, dt_added_to_cart,
           id_cart, des_session_id, id_user, id_address, num_temporary_zip_code, vl_freight, des_type_freight, num_days, dt_cart_created_at,
           id_product, des_product, des_description, bin_image, vl_price, vl_width, vl_height, vl_length, vl_weight, num_quantity_stock, is_national, des_slug, dt_product_created_at, dt_product_changed_in
      FROM tb_carts_products
     INNER JOIN tb_carts USING (id_cart)
     INNER JOIN tb_products USING (id_product);


DROP VIEW IF EXISTS vw_orders_status;

CREATE VIEW vw_orders_status AS
    SELECT id_status, des_status, num_code, dt_status_created_at
      FROM tb_orders_status;


DROP VIEW IF EXISTS vw_orders;

CREATE VIEW vw_orders AS
    SELECT o.id_order, o.vl_total, o.des_code, o.des_annotation, o.dt_order_created_at,
           c.id_cart, c.des_session_id, c.num_temporary_zip_code, c.vl_freight, c.des_type_freight, c.num_days, c.dt_cart_created_at,
           u.id_user, u.id_person, u.des_login, u.des_password, u.is_admin, u.dt_user_created_at, u.dt_user_changed_in,
           s.id_status, s.des_status, s.num_code, s.dt_status_created_at,
           a.id_address, a.id_city, a.id_street_type, a.des_address, a.des_number, a.des_district, a.des_complement, a.des_reference, a.num_zip_code, a.dt_address_created_at, a.dt_address_changed_in
      FROM tb_orders o
     INNER JOIN tb_carts c ON o.id_cart = c.id_cart
     INNER JOIN tb_users u ON o.id_user = u.id_user
     INNER JOIN tb_orders_status s ON o.id_status = s.id_status
     INNER JOIN tb_addresses a ON o.id_address = a.id_address;


DROP VIEW IF EXISTS vw_topics_types;

CREATE VIEW vw_topics_types AS
    SELECT id_type, des_type, des_summary, des_route, dt_type_created_at
      FROM tb_topics_types;


DROP VIEW IF EXISTS vw_topics;

CREATE VIEW vw_topics AS
    SELECT id_topic, des_topic, dt_topic_created_at,
           id_type, des_type, des_summary, des_route, dt_type_created_at
      FROM tb_topics
     INNER JOIN tb_topics_types USING (id_type);


DROP VIEW IF EXISTS vw_subtopics;

CREATE VIEW vw_subtopics AS
    SELECT st.id_subtopic, st.des_subtopic, st.des_text, st.dt_subtopic_created_at, st.dt_subtopic_changed_in,
           t.id_topic, t.des_topic, t.dt_topic_created_at,
           tt.id_type, tt.des_type, tt.des_summary, tt.des_route, tt.dt_type_created_at
      FROM tb_subtopics st
      LEFT OUTER JOIN tb_topics t ON st.id_topic = t.id_topic
      LEFT OUTER JOIN tb_topics_types tt ON st.id_type = tt.id_type;