<header id="header-inscritos" class="clearfix">
    <h3><?php \MapasCulturais\i::_e("Inscritos");?></h3>
    <!--<a class="btn btn-default download" href="<?php echo $this->controller->createUrl('report', [$entity->id]); ?>"><?php \MapasCulturais\i::_e("Baixar lista de avaliações");?></a>-->
</header>
<?php if($entity->isUserEvaluationsSent()): ?>
    <div id='status-info' class="alert success">
        <span><?php \MapasCulturais\i::_e("Suas avaliações já foram enviadas:");?></span>

        <div class="close"></div>
    </div>
<?php else: ?>
    <div id='status-info' class="alert info">
        <span><?php \MapasCulturais\i::_e("Após avaliar todas as inscrições clique no botão <strong>Enviar inscrições</strong>:");?></span>

        <div class="close"></div>
    </div>
<?php endif?>

<?php $this->part('singles/opportunity-evaluations--committee--buttons', ['entity' => $entity]) ?>

<table class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}">
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
            <th ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                <?php \MapasCulturais\i::_e("Anexos");?>
            </th>
            <th class="registration-status-col">
                <?php \MapasCulturais\i::_e("Status / Avaliação");?>
            </th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan='6'>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição enviada.");?></span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 0"><?php \MapasCulturais\i::_e("Nenhuma inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length === 1"><?php \MapasCulturais\i::_e("1 inscrição enviada.");?></span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length === 1"><?php \MapasCulturais\i::_e("1 inscrição encontrada com os filtros selecionados.");?></span>
                <span ng-if="!usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} <?php \MapasCulturais\i::_e("inscrições enviadas.");?>
                    <?php if($entity->registrationLimit > 0):?>
                         | <?php \MapasCulturais\i::_e("Número máximo de vagas na oportunidade:");?> <?php echo $entity->registrationLimit;?>
                    <?php endif;?>
                </span>
                <span ng-if="usingFilters() && getFilteredRegistrations().length > 1">{{getFilteredRegistrations().length}} <?php \MapasCulturais\i::_e("inscrições encontradas com os filtros selecionados.");?></span>
            </td>
        </tr>
        <tr ng-repeat="reg in data.registrations" id="registration-{{reg.id}}" class="{{getStatusSlug(reg.status)}}">
            <td class="registration-id-col"><a href="{{reg.singleUrl}}">{{reg.number}}</a></td>
            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{reg.category}}</td>
            <td class="registration-agents-col">
                <p>
                    <span class="label"><?php \MapasCulturais\i::_e("Responsável");?></span><br />
                    <a href="{{reg.owner.singleUrl}}">{{reg.owner.name}}</a>
                </p>

                <p ng-repeat="relation in reg.agentRelations" ng-if="relation.agent">
                    <span class="label">{{relation.label}}</span><br />
                    <a href="{{relation.agent.singleUrl}}">{{relation.agent.name}}</a>
                </p>
            </td>
            <td ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                <a ng-if="reg.files.zipArchive.url" class="icon icon-download" href="{{reg.files.zipArchive.url}}"><div class="screen-reader-text"><?php \MapasCulturais\i::_e("Baixar arquivos");?></div></a>
            </td>
            <td class="registration-status-col">
                <div ng-show="::getEvaluationResultString(reg)">
                    <strong>{{::getEvaluationStatusLabel(reg)}} / {{::getEvaluationResultString(reg)}}</strong>
                </div>
                <div ng-hide="::getEvaluationResultString(reg)">
                    {{::getEvaluationStatusLabel(reg)}}
                </div>
            </td>
        </tr>
    </tbody>
</table>
