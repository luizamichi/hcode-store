DROP VIEW IF EXISTS vw_countries;

CREATE VIEW vw_countries AS
    SELECT id_country, num_ibge_country, des_country, des_coi, num_ddi, GROUP_CONCAT(des_uf SEPARATOR ', ') des_uf
      FROM tb_countries
      LEFT JOIN tb_states USING (id_country)
     GROUP BY id_country;