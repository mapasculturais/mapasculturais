<?php 
$agentsRelated = $entity->getRelatedAgents();
foreach($agentsRelated as $k => $agents){
    $agentsRelated[$k] = $agents[0];
}
$agentsRelated['owner'] = $entity->owner;
?>
<div class="registration-fieldset">
    <h4><?php \MapasCulturais\i::_e("Agentes (proponentes)");?></h4>
    <!-- agentes relacionados a inscricao -->
    <ul class="registration-list">
        <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
        <li ng-repeat="def in ::data.entity.registrationAgents" class="registration-list-item" ng-if="def.use !== 'dontUse'">
            <div class="registration-label">{{::def.label}}</div>
            <div class="registration-description">{{::def.description}}</div>

            <div id="registration-agent-{{::def.agentRelationGroupName}}" class="js-registration-agent registration-agent" ng-class="{pending: def.relationStatus < 0}">
                <p ng-if="::def.relationStatus < 0" class="alert warning"><?php \MapasCulturais\i::_e("Aguardando confirmação");?></p>
                <div class="clearfix">
                    <img ng-src="{{::def.agent.avatarUrl || data.assets.avatarAgent}}" class="registration-agent-avatar" />
                    <div>
                        <a ng-if="def.agent" href="{{::def.agent.singleUrl}}" rel='noopener noreferrer'>{{::def.agent.name}}</a>
                        <span ng-if="!def.agent"><?php \MapasCulturais\i::_e("Não informado");?></span>
                    </div>
                </div>
                <div class="registration-agent-details">
                    <div ng-repeat="prop in data.propLabels" ng-if="::def.agent[prop.name]">
                        <span class="label"> {{::prop.label}}</span>:
                        {{::prop.name === 'location' ? getReadableLocation(def.agent[prop.name]) : def.agent[prop.name]}}
                    </div>
                </div>
            </div>

            <edit-box id="editbox-select-registration-{{::def.agentRelationGroupName}}" position="left" title="<?php \MapasCulturais\i::esc_attr_e("Selecionar");?> {{::def.label}}" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true' spinner-condition="data.registrationSpinner">
                <!-- <p ng-if='def.agentRelationGroupName != "owner"'><label><input type="checkbox"> Permitir que este agente também edite essa inscrição.</label></p> -->
                <find-entity id='find-entity-registration-{{::def.agentRelationGroupName}}' name='{{::def.agentRelationGroupName}}' api-query="data.relationApiQuery[def.agentRelationGroupName]" entity="agent" no-results-text="<?php \MapasCulturais\i::esc_attr_e("Selecionar");?> {{::def.label}}" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Nenhum agente encontrado");?>" select="setRegistrationAgent" spinner-condition="data.registrationSpinner"></find-entity>
            </edit-box>
            <a ng-if="::def.agent" class="btn btn-default" ng-click="data['modal-' + def.agentRelationGroupName] = true" data-modal-id="portifolio-{{::def.agentRelationGroupName}}"  rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Visualizar Portfólio");?></a>
        </li>
    </ul>
</div>


<?php foreach($agentsRelated as $group => $agent): ?>

<div id="portifolio-<?php echo $group ?>" class="modal-wrapper <?php echo $group ?> ng-hide" ng-show="data['modal-<?php echo $group ?>']" >
        <div class="modal">
            <!-- Video Gallery BEGIN -->
            <?php $this->part('video-gallery.php', array('entity'=> $agent)); ?>
            <!-- Video Gallery END -->

            <!-- Image Gallery BEGIN -->
            <?php $this->part('gallery.php', array('entity'=> $agent)); ?>
            <!-- Image Gallery END -->

            <!-- Downloads BEGIN -->
            <?php $this->part('downloads.php', array('entity' => $agent)); ?>
            <!-- Downloads END -->

            <!-- Link List BEGIN -->
            <?php $this->part('link-list.php', array('entity' => $agent)); ?>
            <!-- Link List END -->

            <div class="tabs-content">
                <div id="sobre" class="aba-content">
                    <?php $this->applyTemplateHook('tabs-content','begin'); ?>
                    <?php $this->applyTemplateHook('tabs-content','end'); ?>
                </div>
            </div>
            <footer>
                <a class="btn btn-default" ng-click="data['modal-<?php echo $group ?>'] = false" data-modal-id="portifolio-<?php echo $group ?>"><?php \MapasCulturais\i::_e("Fechar");?></a>
            </footer>
        </div>
    </div>
<?php endforeach; ?>