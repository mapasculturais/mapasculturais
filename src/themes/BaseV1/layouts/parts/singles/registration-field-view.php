<?php $this->applyTemplateHook('registration-field-item', 'begin') ?>
<div ng-if="field.fieldType !== 'file' && field.fieldType !== 'section' && field.fieldType !== 'persons' && field.config.entityField !== '@location' && field.config.entityField !== '@links' &&  field.fieldType !== 'links'  && !checkRegistrationFields(field, 'links')">
    <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
    <div ng-if="field.fieldType !== 'agent-owner-field'">
        <div ng-if="field.fieldType != 'bankFields'">
            <span ng-if="entity[field.fieldName] && field.fieldType !== 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])"></span>
            <p ng-if="entity[field.fieldName] && field.fieldType === 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])" style="white-space: pre-line"></p>
            <span ng-if="!entity[field.fieldName]"><em><?php \MapasCulturais\i::_e("Campo não informado."); ?></em></span>
        </div>

        <div ng-if="field.fieldType == 'bankFields'">
            <p ng-if="entity[field.fieldName] && field.fieldType === 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])" style="white-space: pre-line"></p>
            <p><strong><?php \MapasCulturais\i::_e("Típo de conta:"); ?></strong> {{formetBankField(entity[field.fieldName]).account_type}}</p>
            <p><strong><?php \MapasCulturais\i::_e("Banco:"); ?></strong> {{formetBankField(entity[field.fieldName]).number}}</p>
            <p><strong><?php \MapasCulturais\i::_e("Agencia:"); ?></strong> {{formetBankField(entity[field.fieldName]).branch}} - {{formetBankField(entity[field.fieldName]).dv_branch}}</p>
            <p><strong><?php \MapasCulturais\i::_e("Conta:"); ?></strong> {{formetBankField(entity[field.fieldName]).account_number}} - {{formetBankField(entity[field.fieldName]).dv_account_number}}</p>
        </div>
       
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
    <div ng-repeat="person in entity[field.fieldName]" ng-if="person">
        <div style="margin-bottom: 15px; padding: 10px; border-left: 3px solid #ccc;">
            <div ng-if="field.config.name && person.name"><strong><?php \MapasCulturais\i::_e("Nome"); ?>: </strong>{{person.name}}</div>
            <div ng-if="field.config.fullName && person.fullName"><strong><?php \MapasCulturais\i::_e("Nome completo"); ?>: </strong>{{person.fullName}}</div>
            <div ng-if="field.config.socialName && person.socialName"><strong><?php \MapasCulturais\i::_e("Nome social"); ?>: </strong>{{person.socialName}}</div>
            <div ng-if="field.config.cpf && person.cpf"><strong><?php \MapasCulturais\i::_e("CPF"); ?>: </strong>{{person.cpf}}</div>
            <div ng-if="field.config.income && person.income"><strong><?php \MapasCulturais\i::_e("Renda"); ?>: </strong>{{person.income}}</div>
            <div ng-if="field.config.education && person.education"><strong><?php \MapasCulturais\i::_e("Escolaridade"); ?>: </strong>{{person.education}}</div>
            <div ng-if="field.config.telephone && person.telephone"><strong><?php \MapasCulturais\i::_e("Telefone"); ?>: </strong>{{person.telephone}}</div>
            <div ng-if="field.config.email && person.email"><strong><?php \MapasCulturais\i::_e("Email"); ?>: </strong>{{person.email}}</div>
            <div ng-if="field.config.race && person.race"><strong><?php \MapasCulturais\i::_e("Raça/Cor"); ?>: </strong>{{person.race}}</div>
            <div ng-if="field.config.gender && person.gender"><strong><?php \MapasCulturais\i::_e("Gênero"); ?>: </strong>{{person.gender}}</div>
            <div ng-if="field.config.sexualOrientation && person.sexualOrientation"><strong><?php \MapasCulturais\i::_e("Orientação sexual"); ?>: </strong>{{person.sexualOrientation}}</div>
            <div ng-if="field.config.deficiencies && person.deficiencies && (typeof person.deficiencies === 'object' ? Object.keys(person.deficiencies).filter(function(k) { return person.deficiencies[k]; }).length > 0 : person.deficiencies)">
                <strong><?php \MapasCulturais\i::_e("Deficiências"); ?>: </strong>
                <span ng-if="typeof person.deficiencies === 'object'">{{Object.keys(person.deficiencies).filter(function(k) { return person.deficiencies[k]; }).join(', ')}}</span>
                <span ng-if="typeof person.deficiencies !== 'object'">{{person.deficiencies}}</span>
            </div>
            <div ng-if="field.config.comunty && person.comunty"><strong><?php \MapasCulturais\i::_e("Comunidade tradicional"); ?>: </strong>{{person.comunty}}</div>
            <div ng-if="field.config.area && person.area && formatPersonArrayField(person.area)">
                <strong><?php \MapasCulturais\i::_e("Áreas de atuação"); ?>: </strong>{{formatPersonArrayField(person.area)}}
            </div>
            <div ng-if="field.config.funcao && person.funcao && formatPersonArrayField(person.funcao)">
                <strong><?php \MapasCulturais\i::_e("Funções/Profissões"); ?>: </strong>{{formatPersonArrayField(person.funcao)}}
            </div>
            <div ng-if="field.config.relationship && person.relationship"><strong><?php \MapasCulturais\i::_e("Relação"); ?>: </strong>{{person.relationship}}</div>
            <div ng-if="field.config.function && person.function"><strong><?php \MapasCulturais\i::_e("Função"); ?>: </strong>{{person.function}}</div>
        </div>
    </div>
</div>
<?php //@TODO pegar endereço do campo endereço (verificar porque não esta salvando corretamente, arquicos location.js e _location.php)
?>
<div ng-if="field.config.entityField === '@location'">
    <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
    <div ng-repeat="(key, item) in entity[field.fieldName]"
        ng-if="key !== 'location' && key !== 'publicLocation' && item && !key.startsWith('field')">
        <span style="text-transform:capitalize"> {{ key.split('_').pop() }}: {{ item }}</span>
    </div>
    <div ng-if="entity[field.fieldName].hasOwnProperty('publicLocation')">
        <span>
            <?php \MapasCulturais\i::_e("Este endereço pode ficar público na plataforma?:"); ?>
            {{ entity[field.fieldName].publicLocation == true || entity[field.fieldName].publicLocation == 'true' ? 'Sim' : 'Não' }}
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