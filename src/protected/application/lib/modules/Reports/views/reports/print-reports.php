<?php
use MapasCulturais\App;

$app = App::i();

$module = $app->modules['Reports'];

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->jsObject['isPrintReport'] = true;

$app->view->jsObject['reportStatus'] = $this->data['status'];

$this->addEntityToJs($opportunity);
$this->includeAngularEntityAssets($opportunity);
$this->includeEditableEntityAssets();

$this->enqueueStyle('app', 'print-reports', 'css/print-reports.css', ['reports'], 'print');
$this->enqueueScript('app', 'print-reports', 'js/print-reports.js', [], 'print');

$params = [ 
    'opportunity' => $opportunity,
    'module'      => $module,
    'print'       => true,
    'status' => $status
];

?>

<article class="main-content registration" id="print-reports" ng-controller="Reports">

    <?php

    $this->part('print/print-header', $params);
    $this->part('print/print-static-graphics', $params);
    $this->part('print/print-dynamic-graphics', $params);

    ?>

</article>