<?php $this->applyTemplateHook('opportunity-evaluations--committee--table','before'); ?>
<header id="header-inscritos" class="clearfix">
    <?php $this->applyTemplateHook('opportunity-evaluations--committee--table','begin'); ?>

    <h3><?php \MapasCulturais\i::_e("Avaliações");?></h3>
</header>
<?php if($entity->isUserEvaluationsSent()): ?>
    <div id='status-info' class="alert success">
        <span><?php \MapasCulturais\i::_e("Suas avaliações já foram enviadas:");?></span>

        <div class="close"></div>
    </div>
<?php else: ?>
    <div id='status-info' class="alert info">
        <span><?php \MapasCulturais\i::_e("Após avaliar todas as inscrições clique no botão <strong>Enviar Avaliações</strong>:");?></span>

        <div class="close"></div>
    </div>
<?php endif?>

<?php $this->part('singles/opportunity-evaluations--committee--buttons', ['entity' => $entity]) ?>

<table class="js-registration-list registrations-table" ng-class="{'no-options': data.entity.registrationCategories.length === 0, 'no-attachments': data.entity.registrationFileConfigurations.length === 0, 'registrations-results': data.entity.published}">
<thead>
    
        <tr>
        <?php $this->applyTemplateHook('opportunity-evaluations--committee--table-thead-tr','begin'); ?>    
            <th class="registration-id-col">
                <?php \MapasCulturais\i::_e("Inscrição");?>
            </th>
            <th ng-if="data.entity.registrationCategories" class="registration-option-col">
                <mc-select placeholder="status" model="evaluationsFilters['registration:category']" data="data.registrationCategoriesToFilter"></mc-select>
            </th>
            <th class="registration-agents-col">
                <?php \MapasCulturais\i::_e("Agentes");?>
            </th>
            <th ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                <?php \MapasCulturais\i::_e("Anexos");?>
            </th>
            <th class="registration-status-col">
                <mc-select placeholder="<?php \MapasCulturais\i::esc_attr_e("Status"); ?>" model="evaluationsFilters['status']" data="data.evaluationStatuses"></mc-select>
            </th>
            <th class="registration-status-col">
                <?php \MapasCulturais\i::esc_attr_e("Avaliação"); ?>
            </th>
            <?php $this->applyTemplateHook('opportunity-evaluations--committee--table-thead-tr','end'); ?>    
        </tr>
    </thead>
    <tbody>
        <tr>
            <td colspan='10'>
                <span ng-if="data.evaluations.length === 0"><?php \MapasCulturais\i::_e("Nenhuma avaliação enviada.");?></span>
                <span ng-if="data.evaluations.length === 1"><?php \MapasCulturais\i::_e("1 avaliação encontrada.");?></span>                
                <span ng-if="data.evaluations.length > 1">{{data.evaluations.length}}
                    <span ng-if="data.evaluationsAPIMetadata.count > 0">
                        <i> de {{ data.evaluationsAPIMetadata.count }}</i>
                    </span>
                 <?php \MapasCulturais\i::_e("Avaliações");?>
             </span>   
            </td>
        </tr>
        <tr ng-repeat="evaluation in data.evaluations" id="registration-{{evaluation.registration.id}}">
            <?php $this->applyTemplateHook('opportunity-evaluations--committee--table-tbody-tr','begin'); ?> 

            <td class="registration-id-col"><a href="{{evaluation.registration.singleUrl}}" rel='noopener noreferrer'>{{evaluation.registration.number}}</a></td>
            <td ng-if="data.entity.registrationCategories" class="registration-option-col">{{evaluation.registration.category}}</td>
            <td class="registration-agents-col">
                <p>
                    <span class="label"><?php \MapasCulturais\i::_e("Responsável");?></span><br />
                    <a href="{{evaluation.registration.owner.singleUrl}}" rel='noopener noreferrer'>{{evaluation.registration.owner.name}}</a>
                </p>

                <p ng-repeat="relation in evaluation.registration.agentRelations" ng-if="relation.agent">
                    <span class="label">{{relation.label}}</span><br />
                    <a href="{{relation.agent.singleUrl}}" rel='noopener noreferrer'>{{relation.agent.name}}</a>
                </p>
            </td>
            <td ng-if="data.entity.registrationFileConfigurations.length > 0" class="registration-attachments-col">
                <a ng-if="evaluation.registration.files.zipArchive.url" class="icon icon-download" href="{{evaluation.registration.files.zipArchive.url}}" rel='noopener noreferrer'><div class="screen-reader-text"><?php \MapasCulturais\i::_e("Baixar arquivos");?></div></a>
            </td>
            <td class="registration-status-col">
                {{getEvaluationStatusLabel(evaluation)}}
            </td>
            <td class="registration-status-col">
                {{getEvaluationResultString(evaluation)}}
            </td>
            <?php $this->applyTemplateHook('opportunity-evaluations--committee--table-tbody-tr','end'); ?>    
        </tr>
    </tbody>
</table>
<?php $this->applyTemplateHook('opportunity-evaluations--committee--table','end'); ?>
<?php $this->applyTemplateHook('opportunity-evaluations--committee--table','after'); ?>
