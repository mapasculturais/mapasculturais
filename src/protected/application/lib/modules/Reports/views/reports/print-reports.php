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

$this->enqueueStyle('app', 'print-reports', 'css/print-reports.css', ['reports'], 'print');

$params = [ 
    'opportunity' => $opportunity,
    'module'      => $module,
    'print'       => true
];

?>

<article class="main-content registration" id="print-reports" ng-controller="Reports">

    <?php

    $this->part('print/print-header', $params);
    $this->part('print/print-static-graphics', $params);
    $this->part('print/print-dynamic-graphics', $params);

    ?>

</article>


<script>
    /**
     * Ajusta o gráfico durante a impressão
     */
    function setPrinting(printing) {
        Chart.helpers.each(Chart.instances, function(chart) {
            chart._printing = printing;
            chart.resize();
            chart.update();
        });
    }

    (function() {
        if (window.matchMedia) {
            var mediaQueryList = window.matchMedia('print');
            mediaQueryList.addListener(function(args) {
                if (args.matches) {
                    setPrinting(true);
                } else {
                    setPrinting(false);
                }
            });
        }

        window.onbeforeprint = beforePrint;
        window.onafterprint  = afterPrint;
    }());
</script>