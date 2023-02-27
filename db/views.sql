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
    SELECT id_contact, des_contact, des_contact_email, des_contact_subject, des_message, dt_contact_created_at
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