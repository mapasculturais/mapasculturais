<div ng-controller="EvaluationsFieldsConfigController" class="registration-fieldset">
    <div>
        <h4><?php \MapasCulturais\i::_e("Configurar campos visíveis para os avaliadores");?></h4>
        <div class="evaluationFields">
            <div>
                <label>
                    <?php \MapasCulturais\i::_e("Filtrar campo");?> <br>
                    <small><i><?php \MapasCulturais\i::_e("Pesquise pelo título ou pelo ID");?></i></small> <br>
                    <input type="text" ng-model="evaluationsFieldsFilter" class="evaluation-fields-filter">
                </label>

                <div>
                    <label><input type="checkbox"  ng-click="checkedAll()"> <?php \MapasCulturais\i::_e("Selecionar todos os campos");?></label> <br>
                </div>
                <hr>
            </div>
           
            <span>
                <label><input type="checkbox" ng-model="data.category" ng-click="selectFields('category')" ng-checked="isChecked('category')"> <?php \MapasCulturais\i::_e("Categoria");?></label> <br>
            </span>

            <span>
                <label><input type="checkbox"  ng-model="data.projectName" ng-click="selectFields('projectName')" ng-checked="isChecked('projectName')"> <?php \MapasCulturais\i::_e("Nome do projeto");?></label> <br>
            </span>

            <span>
                <label><input type="checkbox" ng-model="data.agentsSummary" ng-click="selectFields('agentsSummary')" ng-checked="isChecked('agentsSummary')"> <?php \MapasCulturais\i::_e("Resumo dos agentes");?></label> <br>
            </span>

            <span>
                <label><input type="checkbox" ng-model="data.spaceSummary" ng-click="selectFields('spaceSummary')" ng-checked="isChecked('spaceSummary')"> <?php \MapasCulturais\i::_e("Resumo dos espaços");?></label> <br>
            </span>
            <span ng-repeat="field in data.fields" ng-if="filter(field)"> 
            <code onclick="copyToClipboard(this)" class="hltip field-id" title="<?php \MapasCulturais\i::_e('Clique para copiar')?>" style="color: darkgreen;">#{{field.id}}</code>
            <label><input type="checkbox" ng-click="selectFields(field.ref)" ng-checked="isChecked(field.ref)" ng-model="field.checked">   {{field.title}}</label><br>
            </span>
        </div>
        
    </div>
</div>
