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