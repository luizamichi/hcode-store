DROP VIEW IF EXISTS vw_tests;

CREATE VIEW vw_tests AS
    SELECT num_uint, num_int, des_char, des_vchar
      FROM tb_tests;