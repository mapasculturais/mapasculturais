<?php

use MapasCulturais\i;

?>
<?php $this->applyTemplateHook('print-dynamic-graphics', 'before'); ?>
<div class="charts-dynamic">
    <?php $this->applyTemplateHook('print-dynamic-graphics', 'begin'); ?>
    <div class="chart-wrap type-{{graphic.typeGraphic}}" ng-repeat="(key, graphic) in data.graphics">  
        <header>
            <h3>{{graphic.title}}</h3>
            <span class="hltip type">{{graphic.fields}}</span>
            <p class="description">{{graphic.description}}</p>
            
            <div ng-if="graphic.typeGraphic === 'table' && graphic.graphBreak">
                <?php $this->part('info-dynamic-graphics-break')?>
            </div>
        </header>
        
        <div ng-if="graphic.typeGraphic == 'table'" class="chart-container dynamic-graphic-{{graphic.identifier}} chart-{{graphic.typeGraphic}}" style="position: relative; height:auto; width:100%;">
            <table>
                <thead>
                    <tr>
                        <th></th>
                        <th ng-repeat="(key, label) in graphic.data.labels"><span>{{label}}</span></th>
                        <th><span><?php i::_e("Total"); ?></span></th>
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

        <div ng-if="graphic.typeGraphic === 'pie'" class="chart-container dynamic-graphic-{{graphic.identifier}} chart-{{graphic.typeGraphic}}" style="position: relative; height:auto; width:60%">
            <canvas id="dynamic-graphic-{{graphic.identifier}}"></canvas>
        </div>

        <div ng-if="graphic.typeGraphic === 'bar' || graphic.typeGraphic === 'horizontalBar'" class="chart-container dynamic-graphic-{{graphic.identifier}} chart-{{graphic.typeGraphic}}" style="position: relative; height:auto;" ng-style="{ 'width' : graphic.countData + '%'}">
            <div class="chart-scroll">
                <canvas id="dynamic-graphic-{{graphic.identifier}}"></canvas>
            </div>
        </div>

        <div ng-if="graphic.typeGraphic === 'line'" class="chart-container dynamic-graphic-{{graphic.identifier}} chart-{{graphic.typeGraphic}}" style="position: relative; height:auto; width:100%">
            <div class="chart-scroll">
                <canvas id="dynamic-graphic-{{graphic.identifier}}"></canvas>
            </div>
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
    <?php $this->applyTemplateHook('print-dynamic-graphics', 'after'); ?>
</div>
<?php $this->applyTemplateHook('print-dynamic-graphics', 'end'); ?>