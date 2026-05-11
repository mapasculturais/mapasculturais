<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

 $entity = $this->controller->requestedEntity;
 $app->view->jsObject['config']['EntityProfile'] = [
    'EntityRequiredAvatar' => $app->config['module.Entities']['requiredAvatar'][$entity->getClassName()]
 ];