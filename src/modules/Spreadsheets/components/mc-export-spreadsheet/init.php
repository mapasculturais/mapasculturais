<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$entity = $this->controller->requestedEntity;
$files = $entity ? $entity->files : [];

foreach ($files as $group => $group_files) {
    if (is_array($group_files)) {
        $files[$group] = \Spreadsheets\Module::sortExportedFilesByNewestFirst($group_files);
    }
}

$this->jsObject['config']['mcExportSpreadsheet'] = [
    'files' => $files,
    'evaluation_type' => $entity ? ($entity->evaluationMethod ? $entity->evaluationMethod->slug."-spreadsheets" : null) : null
];