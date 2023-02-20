DROP TABLE IF EXISTS tb_tests;

CREATE TABLE tb_tests (
    num_uint INT UNSIGNED COMMENT 'Unsigned int',
    num_int INT COMMENT 'Int',
    des_char CHAR(3) NULL COMMENT 'Char',
    des_vchar VARCHAR(100) NULL COMMENT 'Varchar'
) COMMENT = 'Testes';