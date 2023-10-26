<?php

namespace Charts;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();
        
        $app->view->enqueueScript('app', 'chart-js', 'https://cdn.jsdelivr.net/npm/chart.js@2.9.4/dist/Chart.min.js');
        $app->view->enqueueScript('app', 'chart-js-plugin', 'https://cdn.jsdelivr.net/npm/chartjs-plugin-datalabels@0.4.0/dist/chartjs-plugin-datalabels.min.js');
        $app->view->enqueueScript('app', 'chart-main', 'js/charts-main.js', ['chart-js', 'mapasculturais']);
    }

    function register()
    {
    }
}
