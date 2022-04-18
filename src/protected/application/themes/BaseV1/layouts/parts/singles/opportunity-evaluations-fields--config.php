<div ng-controller="EvaluationsFieldsConfigController" class="registration-fieldset">
    <div>
        <h4><?php \MapasCulturais\i::_e("Configurar campos visíveis para os avaliadores");?></h4>
        <div>
            <label>
                <input type="checkbox" ng-model="data.category" ng-click="selectFields('category')"> <?php \MapasCulturais\i::_e("Categoria");?>
            </label>

            <label>
                <input type="checkbox"  ng-model="data.projectName" ng-click="selectFields('projectName')"> <?php \MapasCulturais\i::_e("Nome do projeto");?>
            </label>

            <label>
                <input type="checkbox" ng-model="data.agentsSummary" ng-click="selectFields('agentsSummary')"> <?php \MapasCulturais\i::_e("Resumo dos agentes");?>
            </label>

            <label>
                <input type="checkbox" ng-model="data.spaceSummary" ng-click="selectFields('spaceSummary')"> <?php \MapasCulturais\i::_e("Resumo dos agentes");?>
            </label>

            <label ng-repeat="field in data.fields">
                <input type="checkbox" ng-click="selectFields(field)"> {{field.title}}
            </label>
        </div>
        
    </div>
</div>
