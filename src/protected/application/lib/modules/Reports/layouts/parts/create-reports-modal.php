<?php

use MapasCulturais\i;
?>

<div ng-class="{open:data.reportModal}" class="bg-reports-modal" id="reportsModal">
    <div class="reports-modal">

        <div class="graphic-type" ng-class="{hidden:!data.graphicType}">
            <header>
                <h2 class="report-modal-title"><?php i::_e("Criar novo gráfico"); ?></h2>
                <a ng-click="clearModal()" class="close-modal"><i class="fas fa-times close-modal"></i></a>
            </header>

            <p><?php i::_e("Antes de definir os parâmetros, defina o tipo de gráfico que você deseja:"); ?></p>
            <p><b><?php i::_e("Tipo de visualização"); ?></b></p>

            <div class="line">
                <label><input ng-model="data.dataForm.type" value="pie" type="radio"> <i class="fas fa-chart-pie"></i> <span><b><?php i::_e("Gráfico de pizza"); ?></b></span> </label>
            </div>

            <div class="line">
                <label><input ng-model="data.dataForm.type" value="line" type="radio"> <i class="fas fa-chart-area"></i> <span><b><?php i::_e("Gráfico de linha"); ?></b></span> </label>
            </div>

            <div class="line">
                <label><input ng-model="data.dataForm.type" value="bar" type="radio"> <i class="far fa-chart-bar"></i> <span><b><?php i::_e("Gráfico de coluna"); ?></b></span> </label>
            </div>

            <div class="line">
                <label><input ng-model="data.dataForm.type" value="horizontalBar" type="radio"> <i class="fas fa-bars"></i> <span><b><?php i::_e("Gráfico de barra"); ?></b></span> </label>
            </div>
            
            <div class="line">
                <label><input ng-model="data.dataForm.type" value="table" type="radio"> <i class="fas fa-th-list"></i> <span><b><?php i::_e("Gráfico de tabela"); ?></b></span> </label>
            </div>

            <div class="line" ng-if="data.dataForm.type == 'bar' || data.dataForm.type == 'horizontalBar'">
                <label>
                    <input ng-model="data.groupData" value="true" type="checkbox">   
                    <span><b><?php i::_e("Agrupar dados");?></b></span>
                </label>
            </div>


        </div>

        <!--<div class="graphic-data">-->
        <div class="graphic-data" ng-class="{hidden:!data.graphicData}">
            <header>
                <h2 class="report-modal-title"><?php i::_e("Criar novo gráfico de {{data.graphic}}"); ?></h2>
                <a ng-click="clearModal()" class="close-modal"><i class="fas fa-times close-modal"></i></a>
            </header>

            <p><?php i::_e("Agora defina o título e dados exibido no gráfico"); ?></p>

            <div>
                <div class="line flex">
                    <div class="column">
                        <label><?php i::_e("Título do gráfico"); ?></label>
                        <input type="text" ng-model="data.dataForm.title" placeholder="<?php i::_e("Digite um título que represente os dados do novo gráfico"); ?>">
                    </div>
                    <div class="column">
                        <label><?php i::_e("Breve descrição"); ?></label>
                        <input type="text" ng-model="data.dataForm.description" placeholder="<?php i::_e("Digite uma descrição resumida"); ?>">
                    </div>
                </div>
                <div class="line flex">
                    <div class="column">

                        <label ng-if="data.dataForm.type != 'table' && data.dataForm.type != 'horizontalBar'"><?php i::_e("Dados a serem exibidos"); ?></label>
                        <label ng-if="data.dataForm.type == 'table'"><?php i::_e("Dados a serem exibidos na coluna"); ?></label>
                        <label ng-if="data.dataForm.type == 'horizontalBar'"><?php i::_e("Dados a serem exibidos na linha"); ?></label>
                        <select ng-model="data.dataForm.dataDisplayA">
                            <option ng-repeat="(key, dataSelectA) in  data.dataDisplayA" value="{{key}}" label="{{dataSelectA.label}}"></option>
                        </select>
                    </div>

                    <div class="column" ng-if="data.dataForm.type == 'bar' || data.dataForm.type == 'table' || data.dataForm.type == 'horizontalBar'">
                        <label ng-if="data.dataForm.type != 'table' && data.dataForm.type != 'horizontalBar'"><?php i::_e("Dados a serem exibidos"); ?></label>
                        <label ng-if="data.dataForm.type == 'table'"><?php i::_e("Dados a serem exibidos na linha"); ?></label>
                        <label ng-if="data.dataForm.type == 'horizontalBar'"><?php i::_e("Dados a serem exibidos na coluna"); ?></label>
                        <select ng-model="data.dataForm.dataDisplayB">
                            <option ng-repeat="(key, dataSelectB) in  data.dataDisplayB" value="{{key}}" label="{{dataSelectB.label}}"></option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <button class="btn btn-default close-modal cancel" ng-click="clearModal();"><?php i::_e("Cancelar"); ?></button>
            <button class="btn btn-default back" ng-if="data.graphicData == true" ng-click="data.graphicData=false; data.graphicType=true;" class=""><?php i::_e("Voltar"); ?></button>
            <button class="btn btn-primary next" ng-if="data.graphicType == true" ng-click="data.graphicData=true; data.graphicType=false;nextStep()" class="js-close" ng-disabled="!data.dataForm.type"><?php i::_e("Proxima etapa"); ?></button>
            <button class="btn btn-primary next" ng-click="createGraphic()" ng-if="data.graphicData == true && (data.dataForm.type == 'pie' || data.dataForm.type == 'line')" ng-disabled="!data.dataForm.title || !data.dataForm.dataDisplayA"><?php i::_e("Gerar gráfico"); ?></button>
            <button class="btn btn-primary next" ng-click="createGraphic()" ng-if="data.graphicData == true && (data.dataForm.type == 'bar' || data.dataForm.type == 'table' || data.dataForm.type == 'horizontalBar')" ng-disabled="!data.dataForm.title  || !data.dataForm.dataDisplayA || !data.dataForm.dataDisplayB"><?php i::_e("Gerar gráfico"); ?></button>
        </footer>

    </div><!-- /.reports-modal -->
</div><!-- /.bg-reports-modal -->