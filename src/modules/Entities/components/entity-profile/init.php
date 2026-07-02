<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$required_avatar_by_entity_type = [];
$required_avatar_config = $app->config['module.Entities']['requiredAvatar'] ?? [];

foreach ($required_avatar_config as $entity_class => $is_required) {
    if ($entity_type = $app->getControllerIdByEntity($entity_class)) {
        $required_avatar_by_entity_type[$entity_type] = (bool) $is_required;
    }
}

$app->view->jsObject['config']['EntityProfile'] = [
    'requiredAvatarByEntityType' => $required_avatar_by_entity_type,
];
