SELECT
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

    '' AS linguagem,

    CASE WHEN e.parent_id IS NOT NULL  
       THEN (SELECT name FROM space WHERE id = e.parent_id) 
        ELSE '' 
        END AS projeto_pai,

    CASE WHEN e.use_registrations
        THEN 'SIM'
        ELSE 'NÃO'
        END AS usa_inscricoes_online,

    e.registration_from::DATE AS inscricoes_de,
    e.registration_to::DATE AS inscricoes_ate,

    CASE WHEN e.use_registrations
        THEN (SELECT COUNT(id) FROM registration WHERE project_id = e.id AND status > 0)
        ELSE NULL
        END AS inscricoes_enviadas,

    CASE WHEN e.use_registrations
        THEN (SELECT COUNT(id) FROM registration WHERE project_id = e.id AND status = 0)
        ELSE NULL
        END AS inscricoes_nao_enviadas,

    CASE WHEN e.use_registrations
        THEN (SELECT COUNT(id) FROM registration WHERE project_id = e.id)
        ELSE NULL
        END AS inscricoes_total,

    
    (SELECT COUNT(id) FROM project WHERE parent_id = e.id) AS num_projetos_filhos,
    (SELECT count(distinct(event_id)) FROM recurring_event_occurrence_for('2014-01-01', '2016-12-31', 'Etc/UTC', NULL) WHERE event_id = e.id) AS num_eventos,
    (SELECT count(id) FROM recurring_event_occurrence_for('2014-01-01', '2016-12-31', 'Etc/UTC', NULL) WHERE event_id IN (SELECT id FROM event WHERE project_id = e.id)) AS num_ocorrencias

FROM
    project e
    
WHERE
    e.status > 0

ORDER BY data_cadastro ASC