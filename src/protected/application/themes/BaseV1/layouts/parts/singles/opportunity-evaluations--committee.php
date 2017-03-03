<?php
use MapasCulturais\i;

$this->addOpportunityEvaluationCommitteeToJs($entity->evaluationMethodConfiguration);
?>
<style>
    .committee-avatar img { height: 32px; width:32px; }
</style>
<div class="agentes-relacionados" ng-controller="OpportunityEvaluationCommitteeController">
    <div class="registration-fieldset">
        <h4><?php i::_e('Comissão de Avaliação'); ?></h4>

        <table style="width: 100%" ng-if="committee.length > 0">
            <thead>
                <tr>
                    <th>&nbsp;</th>
                    <th><?php i::_e('Nome') ?></th>
                    <th><?php echo strtolower($this->dict('taxonomies:area: name', true)) ?></th>
                    <th><?php i::_e('ações') ?></th>
                </tr>
            </thead>
            <tbody>
                <tr ng-repeat="admin in committee">
                    <td class="committee-avatar"><img ng-src="{{avatarUrl(admin.agent)}}" /></td>
                    <td class="committee-name">
                        {{admin.agent.name}}
                    </td>
                    <td class="committee-areas">
                        <span ng-if="admin.agent.terms.area" ng-repeat="area in admin.agent.terms.area">{{area}}<span ng-if="!$last && area">, </span></span>
                    </td>
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
