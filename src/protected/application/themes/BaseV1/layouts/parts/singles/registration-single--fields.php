<div ng-if="data.fields.length > 0" id="registration-attachments" class="registration-fieldset">
    <h4>Campos adicionais do formulário de inscrição.</h4>
    <ul class="attachment-list" ng-controller="RegistrationFieldsController">

        <li ng-repeat="field in data.fields" ng-if="showFieldForCategory(field)" id="registration-file-{{fileConfiguration.id}}" class="attachment-list-item registration-view-mode">
            <div ng-if="field.fieldType !== 'file'">
                <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
                <span ng-if="entity[field.fieldName] && field.fieldType !== 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])"></span>
                <p ng-if="entity[field.fieldName] && field.fieldType === 'textarea'" ng-bind-html="printField(field, entity[field.fieldName])" style="white-space: pre-line"></p>
                <span ng-if="!entity[field.fieldName]"><em>Campo não informado.</em></span>
            </div>
            <div ng-if="field.fieldType === 'file'">
                <label>{{field.required ? '*' : ''}} {{field.title}}: </label>
                <a ng-if="field.file" class="attachment-title" href="{{field.file.url}}" target="_blank">{{field.file.name}}</a>
                <span ng-if="!field.file"><em>Arquivo não enviado.</em></span>
            </div>
        </li>
    </ul>
</div>
