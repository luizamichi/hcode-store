-- CREATE: Apenas INSERT
-- UPDATE: Apenas UPDATE
-- SAVE: INSERT ou UPDATE
-- DELETE: Apenas DELETE


DROP PROCEDURE IF EXISTS sp_save_country;

DELIMITER $$
CREATE PROCEDURE sp_save_country (
    IN pid_country INT,
    IN pnum_ibge_country INT,
    IN pdes_country VARCHAR(32),
    IN pdes_coi CHAR(3),
    IN pnum_ddi INT
)
BEGIN
    IF pid_country > 0 THEN
        UPDATE tb_countries
           SET num_ibge_country = pnum_ibge_country,
               des_country = pdes_country,
               des_coi = pdes_coi,
               num_ddi = pnum_ddi
         WHERE id_country = pid_country;
    ELSE
        INSERT INTO tb_countries (num_ibge_country, des_country, des_coi, num_ddi)
                          VALUES (pnum_ibge_country, pdes_country, pdes_coi, pnum_ddi);

        SET pid_country = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_countries
     WHERE id_country = pid_country;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_state;

DELIMITER $$
CREATE PROCEDURE sp_save_state (
    IN pid_state INT,
    IN pid_country INT,
    IN pnum_ibge_state INT,
    IN pdes_state VARCHAR(32),
    IN pdes_uf CHAR(2)
)
BEGIN
    IF pid_state > 0 THEN
        UPDATE tb_states
           SET id_country = pid_country,
               num_ibge_state = pnum_ibge_state,
               des_state = pdes_state,
               des_uf = pdes_uf
         WHERE id_state = pid_state;
    ELSE
        INSERT INTO tb_states (id_country, num_ibge_state, des_state, des_uf)
                       VALUES (pid_country, pnum_ibge_state, pdes_state, pdes_uf);

        SET pid_state = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_states
     WHERE id_state = pid_state;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_city;

DELIMITER $$
CREATE PROCEDURE sp_save_city (
    IN pid_city INT,
    IN pid_state INT,
    IN pnum_ibge_city INT,
    IN pdes_city VARCHAR(32),
    IN pnum_ddd TINYINT
)
BEGIN
    IF pid_city > 0 THEN
        UPDATE tb_cities
           SET id_state = pid_state,
               num_ibge_city = pnum_ibge_city,
               des_city = pdes_city,
               num_ddd = pnum_ddd
         WHERE id_city = pid_city;

    ELSE
        INSERT INTO tb_cities (id_state, num_ibge_city, des_city, num_ddd, dt_city_created_at)
                       VALUES (pid_state, pnum_ibge_city, pdes_city, pnum_ddd, NOW());

        SET pid_city = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_cities
     WHERE id_city = pid_city;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_street_type;

DELIMITER $$
CREATE PROCEDURE sp_save_street_type (
    IN pid_street_type INT,
    IN pdes_street_type VARCHAR(32),
    IN pdes_acronym VARCHAR(4)
)
BEGIN
    IF pid_street_type > 0 THEN
        UPDATE tb_street_types
           SET des_street_type = pdes_street_type,
               des_acronym = pdes_acronym
         WHERE id_street_type = pid_street_type;

    ELSE
        INSERT INTO tb_street_types (des_street_type, des_acronym)
                             VALUES (pdes_street_type, pdes_acronym);

        SET pid_street_type = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_street_types
     WHERE id_street_type = pid_street_type;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_contact;

DELIMITER $$
CREATE PROCEDURE sp_save_contact (
    IN pdes_contact VARCHAR(64),
    IN pdes_contact_email VARCHAR(128),
    IN pdes_contact_subject VARCHAR(256),
    IN pdes_message LONGTEXT
)
BEGIN
    DECLARE vid_contact INT DEFAULT 0;

    INSERT INTO tb_contacts (des_contact, des_contact_email, des_contact_subject, des_message, dt_contact_created_at)
                     VALUES (pdes_contact, pdes_contact_email, pdes_contact_subject, pdes_message, NOW());

    SET vid_contact = LAST_INSERT_ID();

    SELECT *
      FROM vw_contacts
     WHERE id_contact = vid_contact;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_create_user;

DELIMITER $$
CREATE PROCEDURE sp_create_user (
    IN pdes_login VARCHAR(64),
    IN pdes_password VARCHAR(256),
    IN pis_admin TINYINT,
    IN pdes_person VARCHAR(64),
    IN pdes_email VARCHAR(128),
    IN pdes_cpf CHAR(11),
    IN pnum_phone BIGINT,
    IN pbin_photo MEDIUMBLOB
)
BEGIN
    DECLARE vid_user INT DEFAULT 0;
    DECLARE vid_person INT DEFAULT 0;

    INSERT INTO tb_persons (des_person, des_email, des_cpf, num_phone, bin_photo)
                    VALUES (pdes_person, pdes_email, pdes_cpf, pnum_phone, pbin_photo);

    SET vid_person = LAST_INSERT_ID();

    INSERT INTO tb_users (id_person, des_login, des_password, is_admin, dt_user_created_at)
                  VALUES (vid_person, pdes_login, pdes_password, pis_admin, NOW());

    SET vid_user = LAST_INSERT_ID();

    SELECT *
      FROM vw_users
     WHERE id_user = vid_user;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_update_user;

DELIMITER $$
CREATE PROCEDURE sp_update_user (
    IN pid_user INT,
    IN pdes_login VARCHAR(64),
    IN pis_admin TINYINT,
    IN pdes_person VARCHAR(64),
    IN pdes_email VARCHAR(128),
    IN pdes_cpf CHAR(11),
    IN pnum_phone BIGINT
)
BEGIN
    DECLARE vid_person INT DEFAULT 0;

    SELECT id_person INTO vid_person
      FROM tb_users
     WHERE id_user = pid_user;

    UPDATE tb_persons
       SET des_person = pdes_person,
           des_email = pdes_email,
           des_cpf = pdes_cpf,
           num_phone = pnum_phone
     WHERE id_person = vid_person;

    UPDATE tb_users
       SET des_login = pdes_login,
           is_admin = pis_admin,
           dt_user_changed_in = NOW()
     WHERE id_user = pid_user;

    SELECT *
      FROM vw_users
     WHERE id_user = pid_user;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_delete_user;

DELIMITER $$
CREATE PROCEDURE sp_delete_user (
    IN pid_user INT
)
BEGIN
    DECLARE vid_person INT DEFAULT 0;

    SELECT id_person INTO vid_person
      FROM tb_users
     WHERE id_user = pid_user;

    DELETE
      FROM tb_users
     WHERE id_user = pid_user;

    DELETE
      FROM tb_persons
     WHERE id_person = vid_person;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_user_log;

DELIMITER $$
CREATE PROCEDURE sp_save_user_log (
    IN pid_log INT,
    IN pid_user INT,
    IN pdes_log VARCHAR(128),
    IN pdes_device VARCHAR(256),
    IN pdes_user_agent VARCHAR(256),
    IN pdes_php_session_id VARCHAR(64),
    IN pdes_source_url VARCHAR(256),
    IN pdes_url VARCHAR(256)
)
BEGIN
    IF pid_log > 0 THEN
        UPDATE tb_users_logs
           SET des_log = pdes_log,
               des_device = pdes_device,
               des_user_agent = pdes_user_agent,
               des_php_session_id = pdes_php_session_id,
               des_source_url = pdes_source_url,
               des_url = pdes_url
         WHERE id_log = pid_log;

    ELSE
        INSERT INTO tb_users_logs (id_user, des_log, des_device, des_user_agent, des_php_session_id, des_source_url, des_url, dt_log_created_at)
                           VALUES (pid_user, pdes_log, pdes_device, pdes_user_agent, pdes_php_session_id, pdes_source_url, pdes_url, NOW());

        SET pid_log = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_users_logs
     WHERE id_log = pid_log;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_mail;

DELIMITER $$
CREATE PROCEDURE sp_save_mail (
    IN pid_mail INT,
    IN pdes_recipient_email VARCHAR(128),
    IN pdes_recipient_name VARCHAR(64),
    IN pdes_subject VARCHAR(256),
    IN pdes_content LONGTEXT,
    IN pdes_files TEXT,
    IN pis_sent TINYINT
)
BEGIN
    IF pid_mail > 0 THEN
        UPDATE tb_mails
           SET des_recipient_email = pdes_recipient_email,
               des_recipient_name = pdes_recipient_name,
               des_subject = pdes_subject,
               des_content = pdes_content,
               des_files = pdes_files,
               is_sent = pis_sent,
               dt_mail_changed_in = NOW()
         WHERE id_mail = pid_mail;

    ELSE
        INSERT INTO tb_mails (des_recipient_email, des_recipient_name, des_subject, des_content, des_files, is_sent, dt_mail_created_at)
                      VALUES (pdes_recipient_email, pdes_recipient_name, pdes_subject, pdes_content, pdes_files, pis_sent, NOW());

        SET pid_mail = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_mails
     WHERE id_mail = pid_mail;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_create_user_password_recovery;

DELIMITER $$
CREATE PROCEDURE sp_create_user_password_recovery (
    IN pid_user INT,
    IN pdes_ip VARCHAR(64)
)
BEGIN
    INSERT INTO tb_users_passwords_recoveries (id_user, des_ip, des_security_key, dt_recovery_created_at)
                                       VALUES (pid_user, pdes_ip, MD5(RAND()), NOW());

    SELECT *
      FROM vw_users_passwords_recoveries
     WHERE id_recovery = LAST_INSERT_ID();
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_address;

DELIMITER $$
CREATE PROCEDURE sp_save_address (
    IN pid_address INT,
    IN pid_person INT,
    IN pid_city INT,
    IN pid_street_type INT,
    IN pdes_address VARCHAR(128),
    IN pdes_number VARCHAR(8),
    IN pdes_district VARCHAR(32),
    IN pdes_complement VARCHAR(32),
    IN pdes_reference VARCHAR(32),
    IN pnum_zip_code BIGINT
)
BEGIN
    IF pid_address > 0 THEN
        UPDATE tb_addresses
           SET id_person = pid_person,
               id_city = pid_city,
               id_street_type = pid_street_type,
               des_address = pdes_address,
               des_number = pdes_number,
               des_district = pdes_district,
               des_complement = pdes_complement,
               des_reference = pdes_reference,
               num_zip_code = pnum_zip_code,
               dt_address_changed_in = NOW()
         WHERE id_address = pid_address;

    ELSE
        INSERT INTO tb_addresses (id_person, id_city, id_street_type, des_address, des_number, des_district, des_complement, des_reference, num_zip_code, dt_address_created_at)
                          VALUES (pid_person, pid_city, pid_street_type, pdes_address, pdes_number, pdes_district, pdes_complement, pdes_reference, pnum_zip_code, NOW());

        SET pid_address = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_addresses
     WHERE id_address = pid_address;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_product;

DELIMITER $$
CREATE PROCEDURE sp_save_product (
    IN pid_product INT,
    IN pdes_product VARCHAR(64),
    IN pdes_description TEXT,
    IN pbin_image LONGBLOB,
    IN pvl_price DECIMAL(10, 2),
    IN pvl_width DECIMAL(10, 2),
    IN pvl_height DECIMAL(10, 2),
    IN pvl_length DECIMAL(10, 2),
    IN pvl_weight DECIMAL(10, 2),
    IN pnum_quantity_stock INT,
    IN pis_national TINYINT,
    IN pdes_slug VARCHAR(256)
)
BEGIN
    IF pid_product > 0 THEN
        UPDATE tb_products
           SET des_product = pdes_product,
               des_description = pdes_description,
               vl_price = pvl_price,
               vl_width = pvl_width,
               vl_height = pvl_height,
               vl_length = pvl_length,
               vl_weight = pvl_weight,
               num_quantity_stock = pnum_quantity_stock,
               is_national = pis_national,
               des_slug = pdes_slug,
               dt_product_changed_in = NOW()
         WHERE id_product = pid_product;
    ELSE
        INSERT INTO tb_products (des_product, des_description, bin_image, vl_price, vl_width, vl_height, vl_length, vl_weight, num_quantity_stock, is_national, des_slug, dt_product_created_at)
                         VALUES (pdes_product, pdes_description, pbin_image, pvl_price, pvl_width, pvl_height, pvl_length, pvl_weight, pnum_quantity_stock, pis_national, pdes_slug, NOW());

        SET pid_product = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_products
     WHERE id_product = pid_product;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_category;

DELIMITER $$
CREATE PROCEDURE sp_save_category (
    IN pid_category INT,
    IN pdes_category VARCHAR(32),
    IN pdes_nickname VARCHAR(256),
    IN pfk_category INT
)
BEGIN
    IF pid_category > 0 THEN
        UPDATE tb_categories
           SET des_category = pdes_category,
               des_nickname = pdes_nickname,
               fk_category = pfk_category,
               dt_category_changed_in = NOW()
         WHERE id_category = pid_category;

    ELSE
        INSERT INTO tb_categories (des_category, des_nickname, fk_category, dt_category_created_at)
                           VALUES (pdes_category, pdes_nickname, pfk_category, NOW());

        SET pid_category = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_categories
     WHERE id_category = pid_category;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_cart;

DELIMITER $$
CREATE PROCEDURE sp_save_cart (
    IN pid_cart INT,
    IN pdes_session_id VARCHAR(64),
    IN pid_user INT,
    IN pid_address INT,
    IN pnum_temporary_zip_code BIGINT,
    IN pvl_freight DECIMAL(10, 2),
    IN pdes_type_freight VARCHAR(32),
    IN pnum_days INT
)
BEGIN
    IF pid_cart > 0 THEN
        UPDATE tb_carts
           SET des_session_id = pdes_session_id,
               id_user = pid_user,
               id_address = pid_address,
               num_temporary_zip_code = pnum_temporary_zip_code,
               vl_freight = pvl_freight,
               des_type_freight = pdes_type_freight,
               num_days = pnum_days
         WHERE id_cart = pid_cart;

    ELSE
        INSERT INTO tb_carts (des_session_id, id_user, id_address, num_temporary_zip_code, vl_freight, des_type_freight, num_days, dt_cart_created_at)
                      VALUES (pdes_session_id, pid_user, pid_address, pnum_temporary_zip_code, pvl_freight, pdes_type_freight, pnum_days, NOW());

        SET pid_cart = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_carts
     WHERE id_cart = pid_cart;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_order_status;

DELIMITER $$
CREATE PROCEDURE sp_save_order_status (
    IN pid_status INT,
    IN pdes_status VARCHAR(32),
    IN pnum_code TINYINT
)
BEGIN
    IF pid_status > 0 THEN
        UPDATE tb_orders_status
           SET des_status = pdes_status,
               num_code = pnum_code
         WHERE id_status = pid_status;

    ELSE
        INSERT INTO tb_orders_status (des_status, num_code, dt_status_created_at)
                              VALUES (pdes_status, pnum_code, NOW());

        SET pid_status = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_orders_status
     WHERE id_status = pid_status;
END $$
DELIMITER ;


DROP PROCEDURE IF EXISTS sp_save_order;

DELIMITER $$
CREATE PROCEDURE sp_save_order (
    IN pid_order INT,
    IN pid_cart INT,
    IN pid_user INT,
    IN pid_status INT,
    IN pid_address INT,
    IN pvl_total DECIMAL(10, 2)
)
BEGIN
    IF pid_order > 0 THEN
        UPDATE tb_orders
           SET id_cart = pid_cart,
               id_status = pid_status,
               id_address = pid_address,
               vl_total = pvl_total,
               des_annotation = CONCAT(COALESCE(des_annotation, ''), NOW(), ' -> STATUS: ', id_status, '|ADDRESS: ', id_address, '|TOTAL: ', vl_total, '\n')
         WHERE id_order = pid_order;

    ELSE
        INSERT INTO tb_orders (id_cart, id_user, id_status, id_address, vl_total, des_code, des_annotation, dt_order_created_at)
                       VALUES (pid_cart, pid_user, pid_status, pid_address, pvl_total, UUID_SHORT(), NULL, NOW());

        SET pid_order = LAST_INSERT_ID();
    END IF;

    SELECT *
      FROM vw_orders
     WHERE id_order = pid_order;
END $$
DELIMITER ;