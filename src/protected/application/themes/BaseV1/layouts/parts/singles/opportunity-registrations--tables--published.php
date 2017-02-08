<table class="js-registration-list registrations-table published-registration-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
    <thead>
        <tr>
            <th class="registration-id-col">
                <?php \MapasCulturais\i::_e("Inscrição");?>
            </th>
            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                <mc-select placeholder="status" model="data.registrationCategory" data="data.registrationCategoriesToFilter"></mc-select>
            </th>
            <th class="registration-agents-col">
                <?php \MapasCulturais\i::_e("Agentes");?>
            </th>
            <th class="registration-status-col">
                <mc-select placeholder="status" model="data.publishedRegistrationStatus" data="data.publishedRegistrationStatuses"></mc-select>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan='5'>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição enviada.");?></span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1"><?php \MapasCulturais\i::_e("1 inscrição enviada.");?></span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 1"><?php \MapasCulturais\i::_e("1 inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} <?php \MapasCulturais\i::_e("inscrições enviadas.");?></span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} <?php \MapasCulturais\i::_e("inscrições encontradas com os filtros selecionados.");?></span>
            </td>
        </tr>
        <tr ng-repeat="reg in data.entity.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-show="showRegistration(reg)" ng-if="reg.status == 10 || reg.status == 8" >
            <td class="registration-id-col"><strong>{{reg.number}}</strong></td>
            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
            <td class="registration-agents-col">
                <p>
                    <span class="label">Responsável</span><br />
                    <a href="{{reg.owner.singleUrl}}">{{reg.owner.name}}</a>
                </p>

                <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                    <span class="label">{{relation.label}}</span><br />
                    <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                </p>
            </td>
            <td class="registration-status-col">
                <?php if ($entity->publishedRegistrations): ?>
                    <span class="status status-{{getStatusSlug(reg.status)}}">{{getStatusNameById(reg.status)}}</span>
                <?php else: ?>
                    <mc-select model="reg" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus"></mc-select>
                <?php endif; ?>
            </td>
        </tr>
    </tbody>
</table>