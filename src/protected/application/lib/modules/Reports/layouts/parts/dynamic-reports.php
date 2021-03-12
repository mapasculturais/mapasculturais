<?php

use MapasCulturais\i;

?>
<?php $this->applyTemplateHook('dynamic-reports', 'before'); ?>
<div class="charts-dynamic">
    <?php $this->applyTemplateHook('dynamic-reports', 'begin'); ?>
    <div class="chart-wrap" ng-repeat="(key, graphic) in data.graphics">  
        <header>
            <h3>{{graphic.title}}</h3>
            <button ng-click="createCsv(graphic.reportData.graphicId)" name="{{graphic.identifier}}" class="hltip download" title="<?php i::_e("Baixar em CSV"); ?>"></button>
            <button ng-click="deleteGraphic(graphic.reportData.graphicId)" class="hltip delete" title="<?php i::_e("Excluir gráfico"); ?>"></button>
            <span class="hltip type" title="{{graphic.fields}}"><i class="fas fa-info-circle"></i></span>
            <p class="description">{{graphic.description}}</p>
        </header>
        
        <div ng-if="graphic.typeGraphic == 'table'" class="chart-container dynamic-graphic-{{graphic.identifier}} chart-{{graphic.typeGraphic}}" style="position: relative; height:auto; width: 100%;">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th ng-repeat="(key, label) in graphic.data.labels">{{label}}</th>
                        <th><?php i::_e("Total"); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr ng-repeat="(key, serie) in graphic.data.series track by $index">                    
                        <td >{{serie.label}}</td>
                        <td ng-repeat="(key, value) in serie.data track by $index">{{value}}</td>
                        <td>{{graphic.data.sumLines[key]}}</td>
                    </tr>
                    <tr>
                        <td><?php i::_e("Total"); ?></td>
                        <td ng-repeat="(key, sumColumn) in graphic.data.sumColumns track by $index">{{sumColumn}}</td> 
                        <td>{{graphic.data.total}}</td>
                    </tr>
                </tbody>
            </table> 
        </div>            
    
        <div ng-if="graphic.typeGraphic != 'table'" class="chart-container dynamic-graphic-{{graphic.identifier}} chart-{{graphic.typeGraphic}}" style="position: relative; height:auto;" ng-style="{'width': (graphic.typeGraphic == 'pie') ? '60%' : '100%'}">
            <canvas id="dynamic-graphic-{{graphic.identifier}}"></canvas>
        </div>
        
        <footer>
            <div class="legends-charts" id="dynamic-legends-{{graphic.identifier}}">
                <div class="each" ng-if="graphic.typeGraphic != 'pie'" ng-repeat="(key, label) in graphic.data.legends">
                    <span class="dot" ng-style="{'background-color': getLabelColor(graphic, key)}"></span><p>{{label}}</p>
                </div>

                <div class="each" ng-if="graphic.typeGraphic == 'pie'" ng-repeat="(key, label) in graphic.data.labels">
                    <span class="dot" ng-style="{'background-color': getLabelColor(graphic, key)}"></span><p>{{label}}</p>
                </div>
            </div>
        </footer>

    </div>
    <?php $this->applyTemplateHook('dynamic-reports', 'end'); ?>
</div>
<?php $this->applyTemplateHook('dynamic-reports', 'after'); ?>