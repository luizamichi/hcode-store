DROP PROCEDURE IF EXISTS sp_create_test;

DELIMITER $$
CREATE PROCEDURE sp_create_test (
    IN pnum_uint INT UNSIGNED,
    IN pnum_int INT,
    IN pdes_char CHAR(3),
    IN pdes_vchar VARCHAR(100)
)
BEGIN
    INSERT INTO tb_countries (num_uint, num_int, des_char, des_vchar)
                      VALUES (pnum_uint, pnum_int, pdes_char, pdes_vchar);
END $$
DELIMITER ;