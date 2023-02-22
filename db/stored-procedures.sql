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