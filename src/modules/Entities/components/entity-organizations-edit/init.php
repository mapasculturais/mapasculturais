<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\AgentRelation;

$entity = $this->controller->requestedEntity;
$collab = [];
$pending = [];
$transferred = [];

if ($entity instanceof Agent) {
    $relations = $app->repo('AgentAgentRelation')->findBy(['agent' => $entity]);

    foreach ($relations as $relation) {
        $owner = $relation->owner;
        if (!$owner) {
            continue;
        }

        $typeId = is_object($owner->type) ? (int) $owner->type->id : (int) $owner->type;
        if ($typeId !== 2) {
            continue;
        }

        // Não listar organizações das quais o agente já é proprietário (parent)
        $ownerParentId = $owner->parent
            ? (is_object($owner->parent) ? (int) $owner->parent->id : (int) $owner->parent)
            : null;
        if ($ownerParentId === (int) $entity->id) {
            continue;
        }

        $item = [
            'relationId' => $relation->id,
            'group' => $relation->group,
            'status' => (int) $relation->status,
            'hasControl' => (bool) $relation->hasControl,
            'createTimestamp' => $relation->createTimestamp
                ? $relation->createTimestamp->format('Y-m-d')
                : null,
            'orgId' => $owner->id,
        ];

        if ((int) $relation->status === AgentRelation::STATUS_PENDING) {
            $pending[] = $item;
        } elseif (!$relation->hasControl && (int) $relation->status > 0) {
            $collab[] = $item;
        }
    }

    // Organizações cuja propriedade já foi deste agente (via EntityRevision.parent)
    // e que hoje pertencem a outro agente.
    $agentId = (string) $entity->id;
    $agentIdInt = (int) $entity->id;
    $objectType = Agent::class;
    $conn = $app->em->getConnection();

    $ownedRows = $conn->fetchAllAssociative(
        "SELECT DISTINCT ON (er.object_id)
            er.object_id AS org_id,
            er.create_timestamp AS last_owned_at
        FROM entity_revision er
        INNER JOIN entity_revision_revision_data j ON j.revision_id = er.id
        INNER JOIN entity_revision_data erd ON erd.id = j.revision_data_id
        INNER JOIN agent a ON a.id = er.object_id AND a.type = 2
        WHERE er.object_type::text = :objectType
          AND erd.key = 'parent'
          AND (erd.value::json->>'id') = :agentId
          AND (a.parent_id IS NULL OR a.parent_id <> :agentIdInt)
        ORDER BY er.object_id, er.create_timestamp DESC",
        [
            'objectType' => $objectType,
            'agentId' => $agentId,
            'agentIdInt' => $agentIdInt,
        ]
    );

    foreach ($ownedRows as $row) {
        $orgId = (int) $row['org_id'];
        $lastOwnedAt = $row['last_owned_at'];

        $transferRow = $conn->fetchAssociative(
            "SELECT er.create_timestamp AS transferred_at
             FROM entity_revision er
             INNER JOIN entity_revision_revision_data j ON j.revision_id = er.id
             INNER JOIN entity_revision_data erd ON erd.id = j.revision_data_id
             WHERE er.object_type::text = :objectType
               AND er.object_id = :orgId
               AND erd.key = 'parent'
               AND (erd.value::json->>'id') IS DISTINCT FROM :agentId
               AND er.create_timestamp > :lastOwnedAt
             ORDER BY er.create_timestamp ASC
             LIMIT 1",
            [
                'objectType' => $objectType,
                'orgId' => $orgId,
                'agentId' => $agentId,
                'lastOwnedAt' => $lastOwnedAt,
            ]
        );

        $transferredAt = $transferRow['transferred_at'] ?? $lastOwnedAt;

        $transferred[] = [
            'orgId' => $orgId,
            'transferredAt' => $transferredAt
                ? (new \DateTime($transferredAt))->format('Y-m-d')
                : null,
        ];
    }
}

$this->jsObject['agentOrganizations'] = [
    'collab' => $collab,
    'pending' => $pending,
    'transferred' => $transferred,
];
