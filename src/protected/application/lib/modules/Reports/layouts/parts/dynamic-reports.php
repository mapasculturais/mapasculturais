<?php

use MapasCulturais\i;
//$route = MapasCulturais\App::i()->createUrl('reports', $action, ['opportunity' => $opportunity->id, 'action' => $action]);
?>

<div class="charts-dynamic">
    <div class="chart-wrap" ng-repeat="graphic in data.loadingGrafics">    
        <header>
        <h3>{{graphic.reportData.title}}</h3>
            <a href="" name="{{graphic.identifier}}" class="hltip download" title="<?php i::_e("Baixar em CSV"); ?>"></a>
        </header>
        <div class="chart-container dinamic-graphic-{{graphic.identifier}} chart-{{graphic.reportData.typeGrafic}}" style="position: relative; height:auto;" ng-style="{'width': (graphic.reportData.typeGrafic == 'pie') ? '60%' : '100%'}">
            <canvas id="dinamic-graphic-{{graphic.identifier}}"></canvas>
        </div>

        <footer>
            <div class="legends-chats" id="dinamic-legends-{{graphic.identifier}}">            
                
                <div class="each" ng-if="graphic.reportData.typeGrafic != 'pie'" ng-repeat="(key, label) in graphic.data.legends">
                    <span class="dot" ng-style="{'background-color': getLabelColor(graphic, key)}"></span><p>{{label}}<p>
                </div>

                <div class="each" ng-if="graphic.reportData.typeGrafic == 'pie'" ng-repeat="(key, label) in graphic.data.labels">
                    <span class="dot" ng-style="{'background-color': getLabelColor(graphic, key)}"></span><p>{{label}}<p>
                </div>
            </div>
        </footer>

    </div>
</div>