<?php $this->applyTemplateHook('registration-field-item', 'begin') ?>
<div ng-if="field.fieldType !== 'file' && field.fieldType !== 'section' && field.fieldType !== 'persons' && field.config.entityField !== '@location' && field.config.entityField !== '@links' &&  field.fieldType !== 'links'  && !checkRegistrationFields(field, 'links')">
    <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
    <div ng-if="field.fieldType !== 'agent-owner-field'">
        <span ng-if="entity[field.fieldName] && field.fieldType !== 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])"></span>
        <p ng-if="entity[field.fieldName] && field.fieldType === 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])" style="white-space: pre-line"></p>
        <span ng-if="!entity[field.fieldName]"><em><?php \MapasCulturais\i::_e("Campo não informado."); ?></em></span>
    </div>

    <div ng-if="field.fieldType === 'agent-owner-field'">
       <div ng-if="field.config.entityField === 'pessoaDeficiente'">
            <span ng-if="checkField(entity[field.fieldName])" ng-bind-html="checkField(entity[field.fieldName])"></span>
            <span ng-if="!checkField(entity[field.fieldName])"><em><?php \MapasCulturais\i::_e("Campo não informado."); ?></em></span>
       </div>

       <div ng-if="field.config.entityField !== 'pessoaDeficiente'">
            <span ng-if="entity[field.fieldName] && field.fieldType !== 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])"></span>
            <p ng-if="entity[field.fieldName] && field.fieldType === 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])" style="white-space: pre-line"></p>
            <span ng-if="!entity[field.fieldName]"><em><?php \MapasCulturais\i::_e("Campo não informado."); ?></em></span>
       </div>
    </div>
</div>
<div ng-if="field.fieldType === 'section'">
    <h4>{{field.title}}</h4>
</div>
<div ng-if="field.fieldType === 'persons'">
    <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
    <div ng-repeat="(key, item) in entity[field.fieldName]" ng-if="item && key !== 'location' && key !== 'publicLocation' ">
        <div><b ng-if="item.name">Nome: </b>{{item.name}}<b ng-if="item.cpf"> CPF: </b>{{item.cpf}} <b ng-if="item.relationship">Relação: </b>{{item.relationship}} <b ng-if="item.function">Função: </b>{{item.function}}</div>
    </div>
</div>
<?php //@TODO pegar endereço do campo endereço (verificar porque não esta salvando corretamente, arquicos location.js e _location.php)
?>
<div ng-if="field.config.entityField === '@location'">
    <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
    <div ng-repeat="(key, item) in entity[field.fieldName]"
        ng-if="key !== 'location' && key !== 'publicLocation' && !(item.En_CEP === '' && item.En_Estado === '' && item.En_Nome_Logradouro === '' && item.En_Num === '' && item.En_Bairro === '' && item.En_Complemento === '' && item.En_Pais === '' && item.En_Municipio === '')">
        <span>{{ key.split('_').pop() }}: {{ item }}</span>
    </div>
    <div ng-if="entity[field.fieldName].hasOwnProperty('publicLocation')">
        <span>
            <?php \MapasCulturais\i::_e("Este endereço pode ficar público na plataforma?:"); ?>
                {{ entity[field.fieldName].publicLocation === true ? 'Sim' : 'Não' }}
        </span>
    </div>
</div>

<div ng-if="field.config.entityField === '@links' || field.fieldType === 'links' || checkRegistrationFields(field, 'links')">
    <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
    <div ng-repeat="(key, item) in entity[field.fieldName]" ng-if="item && key !== 'location' && key !== 'publicLocation' ">
        <b>{{item.title}}:</b> <a target="_blank" href="{{item.value}}">{{item.value}}</a>
    </div>
</div>

<div ng-if="field.fieldType === 'file'">
    <label>{{::field.required ? '*' : ''}} {{::field.title}}: </label>
    <a ng-if="field.file" class="attachment-title" href="{{::field.file.url}}" target="_blank" rel='noopener noreferrer'>{{::field.file.name}}</a>
    <span ng-if="!field.file"><em><?php \MapasCulturais\i::_e("Arquivo não enviado."); ?></em></span>
</div>
<?php $this->applyTemplateHook('registration-field-item', 'end') ?>