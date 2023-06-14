SELECT
    e.id,
    e.create_timestamp::DATE AS data_cadastro,
    e.name,
    e.type,
    
    CASE WHEN e.is_verified=TRUE 
        THEN 'COM SELO' 
        ELSE 'SEM SELO' 
        END AS selo,

    CASE WHEN e.agent_id IS NOT NULL 
        THEN (SELECT name FROM agent WHERE id = e.agent_id) 
        ELSE '' 
        END AS publicado_por,

    '' AS area,


    CASE WHEN e.parent_id IS NOT NULL  
       THEN (SELECT name FROM space WHERE id = e.parent_id) 
        ELSE '' 
        END AS espaco_pai,

    (SELECT COUNT(id) FROM space WHERE parent_id = e.id) AS num_espaco_filhos,
    
    CASE WHEN _acessibilidade.value IS NOT NULL 
        THEN _acessibilidade.value 
        ELSE '' 
        END AS acessibilidade,
    
    CASE WHEN _capacidade.value IS NOT NULL 
        THEN _capacidade.value 
        ELSE '' 
        END AS capacidade,

    e.location[1] as latitude,
    e.location[0] as longitude,

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
        END AS distrito,

    (SELECT count(distinct(event_id)) FROM recurring_event_occurrence_for('2014-01-01', '2016-12-31', 'Etc/UTC', NULL) WHERE space_id = e.id) AS num_eventos,
    (SELECT count(id) FROM recurring_event_occurrence_for('2014-01-01', '2016-12-31', 'Etc/UTC', NULL) WHERE space_id = e.id) AS num_ocorrencias

FROM
    space e
    
    LEFT JOIN space_meta _acessibilidade ON _acessibilidade.object_id = e.id AND _acessibilidade.key = 'acessibilidade'
    LEFT JOIN space_meta _capacidade ON _capacidade.object_id = e.id AND _capacidade.key = 'capacidade'
    LEFT JOIN space_meta _zona ON _zona.object_id = e.id AND _zona.key = 'geoZona'
    LEFT JOIN space_meta _subprefeitura ON _subprefeitura.object_id = e.id AND _subprefeitura.key = 'geoSubprefeitura'
    LEFT JOIN space_meta _distrito ON _distrito.object_id = e.id AND _distrito.key = 'geoDistrito'
WHERE
    e.status > 0

ORDER BY data_cadastro ASC