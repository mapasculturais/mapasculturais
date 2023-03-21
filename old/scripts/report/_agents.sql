SELECT
    e.id,
    e.name,
    e.create_timestamp::DATE AS data_cadastro,
    CASE WHEN e.is_verified=TRUE 
        THEN 'COM SELO' 
        ELSE 'SEM SELO' 
        END AS selo,

    CASE WHEN e.parent_id IS NOT NULL AND e.parent_id <> e.id
        THEN (SELECT name FROM agent WHERE id = e.parent_id) 
        ELSE '' 
        END AS publicado_por,

    CASE WHEN e.type=1 
        THEN 'INDIVIDUAL' 
        ELSE 'COLETIVO' 
        END AS tipo,

    CASE WHEN _raca.value IS NOT NULL 
        THEN _raca.value 
        ELSE '' 
        END AS raca,

    CASE WHEN _genero.value IS NOT NULL 
        THEN _genero.value 
        ELSE '' 
        END AS genero,

    CASE WHEN _nascimento.value IS NOT NULL AND TRIM(_nascimento.value) <> '' 
        THEN (date_part('year', current_date::DATE ) - date_part('year', _nascimento.value::DATE))::CHAR
        ELSE '' 
        END AS idade,

    CASE WHEN _zona.value IS NOT NULL 
        THEN _zona.value 
        ELSE '' 
        END AS zona,

    CASE WHEN _subprefeitura.value IS NOT NULL 
        THEN _subprefeitura.value 
        ELSE '' 
        END AS subprefeitura,

    CASE WHEN _distrito.value IS NOT NULL 
        THEN _distrito.value 
        ELSE '' 
        END AS distrito

FROM
    agent e
    LEFT JOIN agent_meta _raca ON _raca.object_id = e.id AND _raca.key = 'raca'
    LEFT JOIN agent_meta _genero ON _genero.object_id = e.id AND _genero.key = 'genero'
    LEFT JOIN agent_meta _nascimento ON _nascimento.object_id = e.id AND _nascimento.key = 'dataDeNascimento'
    LEFT JOIN agent_meta _zona ON _zona.object_id = e.id AND _zona.key = 'geoZona'
    LEFT JOIN agent_meta _subprefeitura ON _subprefeitura.object_id = e.id AND _subprefeitura.key = 'geoSubprefeitura'
    LEFT JOIN agent_meta _distrito ON _distrito.object_id = e.id AND _distrito.key = 'geoDistrito'
WHERE
    e.status > 0

ORDER BY data_cadastro ASC