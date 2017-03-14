<?php
use MapasCulturais\i;

$this->addOpportunityEvaluationCommitteeToJs($entity->evaluationMethodConfiguration);

$method = $entity->getEvaluationMethod();
?>
<style>
    .committee-avatar img { height: 32px; width:32px; }
</style>
<div class="agentes-relacionados" ng-controller="OpportunityEvaluationCommitteeController">
    <div class="registration-fieldset">
        <h4><?php i::_e('Comissão de Avaliação'); ?></h4>
        <?php if($method->fetchRegistrations()): ?>
            <div id='status-info' class="alert info">
                <p>
                    <?php \MapasCulturais\i::_e("No campo <strong>fatiamento</strong> informe o <strong>final do número de inscrição</strong>, de acordo com os exemplos abaixo.") ?>
                    <ul>
                        <li><?php // \MapasCulturais\i::_e("<strong>1,2,3</strong> - para as inscrições com final 1, 2 e 3</li>");?></li>
                        <li><?php \MapasCulturais\i::_e("<strong>4-10</strong> - para as inscrições com final entre 4 e 10</li>");?></li>
                    </ul>
                </p>

                <div class="close"></div>
            </div>
        <?php endif; ?>
        <table style="width: 100%" ng-if="committee.length > 0">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php i::_e('Nome') ?></th>
                    <th><?php echo strtolower($this->dict('taxonomies:area: name', true)) ?></th>
                    <?php if($method->fetchRegistrations()): ?>
                    <th>
                        <span class="hltip" title="Fatiamento das inscrições: use para dividir as inscrições entre os avaliadores"> <?php i::_e('Fatiamento'); ?> </span>
                    </th>
                    <?php endif; ?>
                    <th><?php i::_e('ações') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="admin in committee">
                    <td class="committee-avatar"><img ng-src="{{avatarUrl(admin.agent)}}" /></td>
                    <td class="committee-name">
                        {{admin.agent.name}}S
                    </td>
                    <td class="committee-areas">
                        <span ng-if="admin.agent.terms.area" ng-repeat="area in admin.agent.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                    </td>
                    <?php if($method->fetchRegistrations()): ?>
                    <td>
                        <input ng-model="fetch[admin.agentUserId]" ng-model-options="{ debounce: 1000, updateOn: 'blur'}" />
                    </td>
                    <?php endif; ?>
                    <td class="committee-actions">
                        <span class="btn btn-danger delete" ng-click="deleteAdminRelation(admin)"><?php i::_e("Excluir");?></span>
                    </td>
                </tr>
            </tbody>
        </table>
        <p ng-if="committee.length < 1"><?php i::_e('Não há nenhum avaliador definido.'); ?></p>
        <span class="btn btn-default add" ng-click="editbox.open('add-committee-agent', $event)" ><?php i::esc_attr_e('Adicionar avaliador'); ?></span>

        <edit-box ng-if="isEditable" id="add-committee-agent" position="right" title="Adicionar agente à comissão de avaliadores" cancel-label="Cancelar" close-on-cancel='true'>
            <find-entity entity="agent" api-query="findQuery" no-results-text="<?php i::esc_attr_e('Nenhum agente encontrado'); ?>" description="" spinner-condition="false" select="createAdminRelation"></find-entity>
        </edit-box>
    </div>
</div>
