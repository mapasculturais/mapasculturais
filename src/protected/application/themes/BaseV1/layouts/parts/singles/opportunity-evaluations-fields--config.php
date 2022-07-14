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
                    <label><input type="checkbox" ng-model="data.allFields.checked" ng-click="checkedAll()"> <?php \MapasCulturais\i::_e("Selecionar todos os campos");?></label> <br>
                </div>
                <hr>
            </div>
            
            <span ng-repeat="field in data.fields" ng-if="filter(field)"> 
            <code onclick="copyToClipboard(this)" class="hltip field-id" title="<?php \MapasCulturais\i::_e('Clique para copiar')?>" style="color: darkgreen;">#{{field.id}}</code>
            <label ng-attr-title="{{field.titleDisabled}}"><input type="checkbox" ng-change="selectFields(field)"  ng-model="field.checked" ng-disabled="field.disabled">   {{field.title}}</label><br>
            </span>
        </div>
        
    </div>
</div>
