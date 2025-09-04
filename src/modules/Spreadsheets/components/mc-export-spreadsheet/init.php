<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$entity = $this->controller->requestedEntity;

$this->jsObject['config']['mcExportSpreadsheet'] = [
    'files' => $entity ? $entity->files : [],
    'evaluation_type' => $entity ? ($entity->evaluationMethod ? $entity->evaluationMethod->slug."-spreadsheets" : null) : null
];