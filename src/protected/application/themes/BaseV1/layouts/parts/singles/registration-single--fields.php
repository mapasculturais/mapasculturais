<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
    <!--
    <h4><?php \MapasCulturais\i::_e("Campos adicionais do formulário de inscrição.");?></h4>
    -->
    
    <ul class="attachment-list" ng-controller="RegistrationFieldsController">

        <li ng-repeat="field in data.fields" ng-if="showField(field)" id="field_{{::field.id}}" data-field-id="{{::field.id}}" ng-class=" (field.fieldType != 'section') ? 'js-field attachment-list-item registration-view-mode' : ''">
            <div ng-if="field.fieldType !== 'file' && field.fieldType !== 'section' && field.fieldType !== 'persons' && field.config.entityField !== '@location' && field.config.entityField !== '@links' &&  field.fieldType !== 'links' ">
                <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
                <span ng-if="entity[field.fieldName] && field.fieldType !== 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])"></span>
                <p ng-if="entity[field.fieldName] && field.fieldType === 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])" style="white-space: pre-line"></p>
                <span ng-if="!entity[field.fieldName]"><em><?php \MapasCulturais\i::_e("Campo não informado.");?></em></span>
            </div>
            <div ng-if="field.fieldType === 'section'">
                <h4>{{field.title}}</h4>
            </div>
            <div ng-if="field.fieldType === 'persons'">
                <label>{{field.required ? '*' : ''}} {{field.title}}: </label> 
                <div ng-repeat="(key, item) in entity[field.fieldName]" ng-if="item && key !== 'location' && key !== 'publicLocation' " >
                    <div><b ng-if="item.name">Nome: </b>{{item.name}}<b ng-if="item.cpf"> CPF: </b>{{item.cpf}} <b ng-if="item.relationship">Relação: </b>{{item.relationship}}</div>
                </div>
            </div>
            <?php //@TODO pegar endereço do campo endereço (verificar porque não esta salvando corretamente, arquicos location.js e _location.php)?>
            <div ng-if="field.config.entityField === '@location'">
                <label>{{field.required ? '*' : ''}} {{field.title}}: </label> 
                <div ng-repeat="(key, item) in entity[field.fieldName]" ng-if="item && key !== 'location' && key !== 'publicLocation' " >
                {{key.split('_').pop()}}: {{item}} 
                </div>
            </div>
            <div ng-if="field.config.entityField === '@links' || field.fieldType === 'links'">
                <label>{{field.required ? '*' : ''}} {{field.title}}: </label> 
                <div ng-repeat="(key, item) in entity[field.fieldName]" ng-if="item && key !== 'location' && key !== 'publicLocation' " >
                <b>{{item.title}}:</b> <a target="_blank" href="{{item.value}}">{{item.value}}</a>
                </div>
            </div>

            <div ng-if="field.fieldType === 'file'">
                <label>{{::field.required ? '*' : ''}} {{::field.title}}: </label>
                <a ng-if="field.file" class="attachment-title" href="{{::field.file.url}}" target="_blank" rel='noopener noreferrer'>{{::field.file.name}}</a>
                <span ng-if="!field.file"><em><?php \MapasCulturais\i::_e("Arquivo não enviado.");?></em></span>
            </div>
        </li>
    </ul>
</div>
