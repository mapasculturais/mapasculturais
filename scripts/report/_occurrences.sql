SELECT
    e.id as event_id,
    e.name AS evento,
    occ.starts_on as dia,
    concat(date_part('hour',occ.starts_at),':',date_part('minutes',occ.starts_at)) as horario,

    CASE WHEN e.is_verified=TRUE 
        THEN 'COM SELO' 
        ELSE 'SEM SELO' 
        END AS evento_selo,

    CASE WHEN e.agent_id IS NOT NULL 
        THEN (SELECT name FROM agent WHERE id = e.agent_id) 
        ELSE '' 
        END AS evento_publicado_por,

    '' AS evento_linguagens,

    CASE WHEN e.project_id IS NOT NULL 
        THEN (SELECT name FROM project WHERE id = e.project_id) 
        ELSE '' 
        END AS evento_projeto,
    
    CASE WHEN _classificacao.value IS NOT NULL 
        THEN _classificacao.value 
        ELSE '' 
        END AS evento_classificacao,

    CASE WHEN _libras.value IS NOT NULL 
        THEN _libras.value 
        ELSE '' 
        END AS evento_traducao_libras,

    CASE WHEN _desc.value IS NOT NULL 
        THEN _desc.value 
        ELSE '' 
        END AS evento_descricao_sonora,

    s.name AS espaco,
    s.id as space_id,
    CASE WHEN s.is_verified=TRUE 
        THEN 'COM SELO' 
        ELSE 'SEM SELO' 
        END AS espaco_selo,

    CASE WHEN s.agent_id IS NOT NULL 
        THEN (SELECT name FROM agent WHERE id = s.agent_id) 
        ELSE '' 
        END AS espaco_publicado_por,

    '' AS espaco_areas,

    CASE WHEN _acessibilidade.value IS NOT NULL 
        THEN _acessibilidade.value 
        ELSE '' 
        END AS espaco_acessibilidade,
    
    CASE WHEN _capacidade.value IS NOT NULL 
        THEN _capacidade.value 
        ELSE '' 
        END AS espaco_capacidade,

    s.location[1] as latitude,
    s.location[0] as longitude,

    CASE WHEN _zona.value IS NOT NULL 
        THEN _zona.value 
        ELSE '' 
        END AS espaco_zona,

    CASE WHEN _subprefeitura.value IS NOT NULL 
        THEN _subprefeitura.value 
        ELSE '' 
        END AS espaco_subprefeitura,

    CASE WHEN _distrito.value IS NOT NULL 
        THEN _distrito.value 
        ELSE '' 
        END AS espaco_distrito

FROM
    recurring_event_occurrence_for('2014-01-01', '2016-12-31', 'Etc/UTC', NULL) occ
    LEFT JOIN event e ON e.id = occ.event_id
    LEFT JOIN space s ON s.id = occ.space_id
    
    LEFT JOIN event_meta _classificacao ON _classificacao.object_id = e.id AND _classificacao.key = 'classificacaoEtaria'
    LEFT JOIN event_meta _libras ON _libras.object_id = e.id AND _libras.key = 'traducaoLibras'
    LEFT JOIN event_meta _desc ON _desc.object_id = e.id AND _desc.key = 'descricaoSonora'
    
    LEFT JOIN space_meta _acessibilidade ON _acessibilidade.object_id = s.id AND _acessibilidade.key = 'acessibilidade'
    LEFT JOIN space_meta _capacidade ON _capacidade.object_id = s.id AND _capacidade.key = 'capacidade'
    LEFT JOIN space_meta _zona ON _zona.object_id = s.id AND _zona.key = 'geoZona'
    LEFT JOIN space_meta _subprefeitura ON _subprefeitura.object_id = s.id AND _subprefeitura.key = 'geoSubprefeitura'
    LEFT JOIN space_meta _distrito ON _distrito.object_id = s.id AND _distrito.key = 'geoDistrito'
WHERE
    e.status > 0 AND s.status > 0

ORDER BY occ.starts_on ASC