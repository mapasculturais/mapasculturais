SELECT
    e.id,
    e.create_timestamp::DATE AS data_cadastro,
    e.name,

    CASE WHEN _subtitulo.value IS NOT NULL
        THEN _subtitulo.value
        ELSE ''
        END AS subtitulo,

    CASE WHEN e.is_verified=TRUE
        THEN 'COM SELO'
        ELSE 'SEM SELO'
        END AS selo,

    CASE WHEN e.agent_id IS NOT NULL
        THEN (SELECT name FROM agent WHERE id = e.agent_id)
        ELSE ''
        END AS publicado_por,

    '' AS linguagem,

    CASE WHEN e.project_id IS NOT NULL
        THEN (SELECT name FROM project WHERE id = e.project_id)
        ELSE ''
        END AS projeto,

    CASE WHEN _classificacao.value IS NOT NULL
        THEN _classificacao.value
        ELSE ''
        END AS classificacao,

    CASE WHEN _libras.value IS NOT NULL
        THEN _libras.value
        ELSE ''
        END AS traducao_libras,

    CASE WHEN _desc.value IS NOT NULL
        THEN _desc.value
        ELSE ''
        END AS descricao_sonora,

    (SELECT count(distinct(space_id)) FROM recurring_event_occurrence_for('2014-01-01', '2016-12-31', 'Etc/UTC', NULL) WHERE event_id = e.id) AS num_espacos,
    (SELECT count(id) FROM recurring_event_occurrence_for('2014-01-01', '2016-12-31', 'Etc/UTC', NULL) WHERE event_id = e.id) AS num_ocorrencias

FROM
    event e

    LEFT JOIN event_meta _subtitulo ON _subtitulo.object_id = e.id AND _subtitulo.key = 'subTitle'
    LEFT JOIN event_meta _classificacao ON _classificacao.object_id = e.id AND _classificacao.key = 'classificacaoEtaria'
    LEFT JOIN event_meta _libras ON _libras.object_id = e.id AND _libras.key = 'traducaoLibras'
    LEFT JOIN event_meta _desc ON _desc.object_id = e.id AND _desc.key = 'descricaoSonora'

WHERE
    e.status > 0 AND e.agent_id IS NOT NULL

ORDER BY data_cadastro ASC