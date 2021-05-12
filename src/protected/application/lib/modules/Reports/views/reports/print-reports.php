<?php
use MapasCulturais\App;

$app = App::i();

$module = $app->modules['Reports'];

$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->jsObject['angularAppDependencies'][] = 'entity.module.opportunity';

$this->addEntityToJs($opportunity);
$this->includeAngularEntityAssets($opportunity);
$this->includeEditableEntityAssets();

$this->enqueueStyle('app', 'print-reports', 'css/print-reports.css');

$params = [ 
    'opportunity' => $opportunity,
    'module' => $module,
];

?>

<article class="main-content registration" ng-controller="Reports">

<?php $this->part('print/print-static-graphics', $params);?>
<?php $this->part('print/print-dynamic-graphics');?>

</article>
