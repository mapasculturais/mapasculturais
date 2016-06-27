<header id="header-inscritos" class="clearfix">
    <h3>Inscritos</h3>
    <div class="alert info hide-tablet">
        Não é possível alterar o status das inscrições através desse dispositivo. Tente a partir de um dispositivo com tela maior.
        <div class="close"></div>
    </div>
    <a class="btn btn-default download" href="<?php echo $this->controller->createUrl('report', [$entity->id]); ?>">Baixar lista de inscritos</a>
</header>
<div id='status-info' class="alert info">
    <p>Altere os status das inscrições na última coluna da tabela de acordo com o seguinte critério:</p>
    <ul>
        <li><span>Inválida - em desacordo com o regulamento (ex. documentação incorreta).</span></li>
        <li><span>Pendente - ainda não avaliada.</span></li>
        <li><span>Não selecionada - avaliada, mas não selecionada.</span></li>
        <li><span>Suplente - avaliada, mas aguardando vaga.</span></li>
        <li><span>Selecionada - avaliada e selecionada.</span></li>
        <li><span>Rascunho - utilize essa opção para permitir que o responsável edite e reenvie uma inscrição. Ao selecionar esta opção, a inscrição não será mais exibida nesta tabela.</span></li>
    </ul>
    <div class="close"></div>
</div>
<table class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}"><!-- adicionar a classe registrations-results quando resultados publicados-->
    <thead>
        <tr>
            <th class="registration-id-col">
                Inscrição
            </th>
            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                <mc-select placeholder="status" model="data.registrationCategory" data="data.registrationCategoriesToFilter"></mc-select>
            </th>
            <th class="registration-agents-col">
                Agentes
            </th>
            <th ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                Anexos
            </th>
            <th class="registration-status-col">
                <mc-select placeholder="status" model="data.registrationStatus" data="data.registrationStatuses"></mc-select>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan='5'>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0">Nenhuma inscrição enviada.</span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 0">Nenhuma inscrição encontrada com os filtros selecionados.</span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1">1 inscrição enviada.</span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 1">1 inscrição encontrada com os filtros selecionados.</span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscrições enviadas.</span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} inscrições encontradas com os filtros selecionados.</span>
            </td>
        </tr>
        <tr ng-repeat="reg in data.entity.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}" ng-show="showRegistration(reg)" >
            <td class="registration-id-col"><a href="{{reg.singleUrl}}">{{reg.number}}</a></td>
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
            <td ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                <a ng-if="reg.files.zipArchive.url" class="icon icon-download" href="{{reg.files.zipArchive.url}}"><div class="screen-reader-text">Baixar arquivos</div></a>
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