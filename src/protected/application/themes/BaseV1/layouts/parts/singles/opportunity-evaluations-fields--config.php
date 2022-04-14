<div ng-controller="EvaluationsFieldsConfigController" class="registration-fieldset">
    <div>
        <h4><?php \MapasCulturais\i::_e("Configurar campos visíveis para os avaliadores");?></h4>
        <div>
            <label ng-repeat="field in data.fields">
                <input type="checkbox"> {{field.title}}
            </label>
        </div>
        
    </div>
</div>
