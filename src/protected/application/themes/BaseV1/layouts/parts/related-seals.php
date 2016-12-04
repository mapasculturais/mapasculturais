<?php
if(!$app->isEnabled('seals'))
	return;

$this->addSealsToJs(true,array(),$entity);
$this->addRelatedSealsToJs($entity);
?>

<?php if($this->controller->action == 'create'): ?>
	<div class="widget">
		<p class="alert info"><?php printf(\MapasCulturais\i::__("Para relacionar o selo ao %s, primeiro é preciso salvar o registro."), $entity->entityTypeLabel()); ?><span class="close"></span></p>
	</div>
<?php else: ?>
    <div class="selos-add" ng-controller="RelatedSealsController">
        <div ng-if="relations.length > 0 || (isEditable && seals.length > 0)" class="widget">
            <h3 text-align="left" vertical-align="bottom"><?php \MapasCulturais\i::_e("Selos Aplicados");?> <div ng-if="isEditable && canRelateSeal" ng-click="editbox.open('sealsAvailable', $event)" class="hltip editable editable-empty" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selo relacionado");?>"></div></h3>
            <edit-box id="sealsAvailable" position="right" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selo relacionado");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Fechar");?>" close-on-cancel='true'>
                <div ng-if="seals.length > 0" class="widget">
                    <h3><?php \MapasCulturais\i::_e("Selos Disponíveis");?></h3>
                    <div class="selos clearfix">
                        <div ng-if="!sealRelated(seal.id)" class="avatar-seal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}" ng-click="createRelation(seal)">
                            <img ng-src="{{avatarUrl(seal['@files:avatar.avatarMedium'].url)}}">
                            <div class="descricao-do-selo">
                                <h1><a href="{{seal.singleUrl}}" class="ng-binding">{{seal.name}}</a></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </edit-box>
            <div class="selos clearfix">
                <div class="avatar-seal ng-scope" ng-repeat="relation in relations" ng-class="{pending: relation.status < 0}">
                    <?php $idRelation =  '{{relation.id}}';?>
                    <a href="<?php echo $app->createUrl('seal','sealrelation',[$idRelation]);?>" class="ng-binding">
                        <img ng-src="{{avatarUrl(relation.seal.avatar.avatarMedium.url)}}">
                    </a>
                    <div class="botoes" ng-if="isEditable && canRelateSeal"><a class="delete hltip js-remove-item"  data-href="" data-target="" data-confirm-message="" title="<?php \MapasCulturais\i::esc_attr_e("Excluir selo");?>" ng-click="deleteRelation(relation,relation.seal.id)"></a></div>
                    <div class="descricao-do-selo">
                        <h1><a href="<?php echo $app->createUrl('seal','sealrelation',[$idRelation]);?>" class="ng-binding">{{relation.seal.name}}</a></h1>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
