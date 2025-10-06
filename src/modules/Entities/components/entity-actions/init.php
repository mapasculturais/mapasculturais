<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$update_method = 'PATCH';


$controller_id = $this->controller->id;
$action_name = $this->controller->action;

$app->applyHookBoundTo($this,"view({$controller_id}.{$action_name}).updateMethod", [&$update_method]);

$this->jsObject['config']['entity-actions'] = [
    'updateMethod' => $update_method
];