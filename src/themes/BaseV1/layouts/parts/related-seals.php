<?php
if(!$app->isEnabled('seals'))
	return;

$this->addSealsToJs(true,array(),$entity);
$this->addRelatedSealsToJs($entity);
?>

<?php if($this->controller->action == 'create'): ?>
	<div class="widget">
		<p class="alert info"><?php printf(\MapasCulturais\i::__("Para relacionar o selo ao %s, primeiro é preciso salvar o registro."), $entity->entityTypeLabel); ?><span class="close"></span></p>
	</div>
<?php else: ?>
    <style>
        .seal-status-badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            margin-top: 4px;
        }
        .seal-status-badge.status-fully_valid { background: #d4edda; color: #155724; }
        .seal-status-badge.status-partially_valid { background: #fff3cd; color: #856404; }
        .seal-status-badge.status-invalid { background: #f8d7da; color: #721c24; }
        .seal-sensitive-icon {
            margin-left: 4px;
            font-size: 0.85rem;
            cursor: help;
        }
        .seal-field-status-list {
            list-style: none;
            padding: 0;
            margin: 4px 0 0;
            font-size: 0.8rem;
        }
        .seal-field-status-list li {
            padding: 2px 0;
        }
        .seal-field-status-list .field-status-valid { color: #155724; }
        .seal-field-status-list .field-status-about_to_expire { color: #856404; font-weight: bold; }
        .seal-field-status-list .field-status-expired { color: #721c24; text-decoration: line-through; }
        .seal-field-status-list .field-status-no_expiration { color: #6c757d; }
        .seal-field-icon {
            margin-left: 4px;
            font-size: 0.9em;
            cursor: help;
        }
    </style>
    <div class="selos-add" ng-controller="RelatedSealsController">
        <div ng-if="getLength(relations) > 0 || (isEditable && seals.length > 0)" class="widget">
            <h3 text-align="left" vertical-align="bottom"><?php \MapasCulturais\i::_e("Selos Aplicados");?> <div ng-if="isEditable && canRelateSeal" ng-click="editbox.open('sealsAvailable', $event)" class="hltip editable editable-empty" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selo relacionado");?>"></div></h3>
            <edit-box id="sealsAvailable" position="right" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selo relacionado");?>" cancel-label="<?php \MapasCulturais\i::esc_attr_e("Fechar");?>" close-on-cancel='true'>
                <div ng-if="seals.length > 0" class="widget">
                    <h3><?php \MapasCulturais\i::_e("Selos Disponíveis");?></h3>
                    <div class="selos clearfix">
                        <div ng-if="!sealRelated(seal.id)" class="avatar-seal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}" ng-click="createRelation(seal)">
                            <img ng-src="{{avatarUrl(seal['@files:avatar.avatarMedium'].url)}}">
                            <div class="descricao-do-selo">
                                <h1><a href="{{seal.singleUrl}}" class="ng-binding" rel='noopener noreferrer'>{{seal.name}}</a></h1>
                            </div>
                        </div>
                    </div>
                </div>
            </edit-box>
            <div class="selos clearfix">
                <div class="avatar-seal ng-scope" ng-repeat="relation in relations" ng-if="relation.can_view !== false" ng-class="{pending: relation.status < 0 || relation.toExpire == 0 || relation.computedStatus === 'invalid'}">
                    <?php $idRelation =  '{{relation.id}}';?>
                    <a ng-href="<?php echo $app->createUrl('seal','sealrelation',[$idRelation]);?>" class="ng-binding">
                        <img ng-src="{{avatarUrl(relation.seal.avatar.avatarMedium.url)}}">
                    </a>
                    <div class="botoes" ng-if="isEditable && canRelateSeal"><a class="delete hltip js-remove-item"  data-href="" data-target="" data-confirm-message="" title="<?php \MapasCulturais\i::esc_attr_e("Excluir selo");?>" ng-click="deleteRelation(relation,relation.seal.id)"></a></div>
                    <div class="descricao-do-selo">
                        <h1>
                            <a ng-href="<?php echo $app->createUrl('seal','sealrelation',[$idRelation]);?>" class="ng-binding">{{relation.seal.name}}</a>
                            <span ng-if="relation.seal.sensitive" class="seal-sensitive-icon hltip" title="<?php \MapasCulturais\i::esc_attr_e('Selo sensível/LGPD') ?>">&#128274;</span>
                        </h1>

                        <span ng-if="relation.computedStatus" class="seal-status-badge" ng-class="'status-' + relation.computedStatus">
                            <span ng-if="relation.computedStatus === 'fully_valid'"><?php \MapasCulturais\i::_e('Totalmente Válido') ?></span>
                            <span ng-if="relation.computedStatus === 'partially_valid'"><?php \MapasCulturais\i::_e('Parcialmente Válido') ?></span>
                            <span ng-if="relation.computedStatus === 'invalid'"><?php \MapasCulturais\i::_e('Inválido') ?></span>
                        </span>

                        <div ng-if="hasAboutToExpireFields(relation)" class="alert warning">
                            <?php \MapasCulturais\i::_e('Atenção: alguns campos validados por selos expiram em breve') ?>
                        </div>

                        <ul class="seal-field-status-list" ng-if="relation.fields && relation.fields.length > 0">
                            <li ng-repeat="field in relation.fields" ng-class="'field-status-' + field.fieldStatus">
                                {{field.fieldName}}
                                <span ng-if="field.expiryDate">({{field.expiryDate}})</span>:
                                <span ng-if="field.fieldStatus === 'valid'"><?php \MapasCulturais\i::_e('Válido') ?></span>
                                <span ng-if="field.fieldStatus === 'about_to_expire'"><?php \MapasCulturais\i::_e('Prestes a expirar') ?></span>
                                <span ng-if="field.fieldStatus === 'expired'"><?php \MapasCulturais\i::_e('Expirado') ?></span>
                                <span ng-if="field.fieldStatus === 'no_expiration'"><?php \MapasCulturais\i::_e('Sem expiração') ?></span>
                                <span ng-if="field.isInvalidator" class="hltip seal-field-icon" title="<?php \MapasCulturais\i::esc_attr_e('Campo invalidador') ?>">&#9888;</span>
                                <span ng-if="field.isUnlocked" class="hltip seal-field-icon" title="<?php \MapasCulturais\i::esc_attr_e('Campo desbloqueado para edição') ?>">&#128275;</span>
                            </li>
                        </ul>

                        <p>
                            <span ng-if="relation.toExpire === 2"><b>Não expira</b></span>
                            <span ng-if="relation.toExpire === 1"><b>Expira em: {{relation.validateDate}}</b></span>
                            <span ng-if="relation.toExpire === 0"><b>Expirou em: {{relation.validateDate}}</b></span>
                        </p>
                        <p>
                            <div ng-if="relation.ownerSealUserId != <?php echo $app->user->id;?>" align="center">
                                <a ng-if="!relation.renovationRequest && relation.toExpire < 2" ng-href="{{relation.requestSealRelationUrl}}" class="btn btn-default js-toggle-edit">
                                <?php \MapasCulturais\i::_e("Solicitar renovação");?>
                                </a>
                                <div ng-if="relation.renovationRequest && relation.toExpire < 2" class="alert warning">
                                <?php \MapasCulturais\i::_e("Renovação Solicitada");?>
                                </div>
                            </div>
                            <div ng-if="relation.ownerSealUserId == <?php echo $app->user->id;?>" align="center">
                                <a ng-if="relation.toExpire < 2" ng-href="{{relation.renewSealRelationUrl}}" class="btn btn-default js-toggle-edit">
                                <?php \MapasCulturais\i::_e("Renovar selo");?>
                                </a>
                            </div>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
