<?php
$entity = $this->controller->requestedEntity;

$this->jsObject['config']['entity-renew-lock'] = [
    'renewInterval' => (int) $app->config['entity.lock.renewInterval'],
    'usesLock' => $entity->usesLock()
];