<?php
if(!$app->isEnabled('seals'))
	return;
?>
<!-- BEGIN Seals -->
<div id="registration-seals" class="registration-fieldset">
	<h4>5. <?php \MapasCulturais\i::_e("Selos Certificadores");?></h4>
	<p class="registration-help"><?php \MapasCulturais\i::_e("Relacione os selos que serão atribuídos as entidades relacionadas a inscrição quando o inscrito for aprovado.");?></p>
	<div class="registration-related-agent-configuration" ng-controller="SealsController">
		<p>
			<span class="label"><?php \MapasCulturais\i::_e("Agente responsável");?></span> <span class="registration-help"><?php \MapasCulturais\i::_e("(Selos atribuídos a agentes)");?></span><br>
		</p>
		<div class="selos-relacionados">
			<input id="registrationSeals" type="hidden" class="js-editable" data-edit="registrationSeals">
			<div class="widget">
				<div class="selos clearfix">
					<div ng-if="!entity.registrationSeals.owner" ng-click="editbox.open('set-seal-owner', $event)" class="hltip editable editable-empty" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selo");?>"></div>
					<edit-box id='set-seal-owner' cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true'>
			            <div ng-if="seals.length > 0" class="widget">
					        <div class="selos clearfix">
					            <div ng-if="!sealRelated(seal)" class="avatar-seal modal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}"  ng-click="setSeal('owner', seal)">
									<img ng-src="{{avatarUrl(seal['@files:avatar.avatarMedium'].url)}}" width="48">
									<h3><a href="{{seal.singleUrl}}" class="ng-binding">{{seal.name}}</a></h3>
					            </div>
					        </div>
					    </div>
					</edit-box>
					<div ng-if="entity.registrationSeals.owner" class="avatar-seal">
						<img ng-src="{{avatarUrl(seals[getArrIndexBySealId(entity.registrationSeals.owner)]['@files:avatar.avatarMedium'].url)}}">
		                <div class="descricao-do-selo">
		                    <h1><a href="{{seals[getArrIndexBySealId(entity.registrationSeals.owner)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(entity.registrationSeals.owner)].name}}</a></h1>
		                </div>
		                <div align="right">
		                	<a class="btn btn-default edit hltip" ng-click="openEditBox('set-seal-owner', $event)" title="<?php \MapasCulturais\i::esc_attr_e("Editar");?> {{seals[getArrIndexBySealId(entity.registrationSeals.owner)].name}}"><?php \MapasCulturais\i::_e("Trocar selo");?></a>
		                	<a ng-click="removeSeal('owner')" class="btn btn-default delete hltip" title="<?php \MapasCulturais\i::esc_attr_e("excluir selo");?>"><?php \MapasCulturais\i::esc_attr_e("Excluir");?></a>
		                </div>
	            	</div>
				</div>
			</div>
		</div>
		<p>
			<span class="label"><?php \MapasCulturais\i::_e("Instituição responsável");?></span> <span class="registration-help"><?php \MapasCulturais\i::_e("(Selos atribuídos a instituições)");?></span><br>
		</p>
		<div class="selos-relacionados">
			<div class="widget">
				<div class="selos clearfix">
					<div ng-if="!entity.registrationSeals.institution" ng-click="editbox.open('set-seal-institution', $event)" class="hltip editable editable-empty" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selo");?>"></div>
					<edit-box id='set-seal-institution' cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true'>
			            <div ng-if="seals.length > 0" class="widget">
					        <div class="selos clearfix">
					            <div ng-if="!sealRelated(seal)" class="avatar-seal modal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}"  ng-click="setSeal('institution', seal)">
									<img ng-src="{{avatarUrl(seal['@files:avatar.avatarMedium'].url)}}">
									<h3><a href="{{seal.singleUrl}}" class="ng-binding">{{seal.name}}</a></h3>
					            </div>
					        </div>
					    </div>
					</edit-box>
					<div ng-if="entity.registrationSeals.institution" class="avatar-seal">
						<img ng-src="{{avatarUrl(seals[getArrIndexBySealId(entity.registrationSeals.institution)]['@files:avatar.avatarMedium'].url)}}">
		                <div class="descricao-do-selo">
		                    <h1><a href="{{seals[getArrIndexBySealId(entity.registrationSeals.institution)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(entity.registrationSeals.institution)].name}}</a></h1>
		                </div>
		                <div align="right">
		                	<a class="btn btn-default edit hltip" ng-click="openEditBox('set-seal-institution', $event)" title="<?php \MapasCulturais\i::esc_attr_e("Editar");?> {{seals[getArrIndexBySealId(entity.registrationSeals.institution)].name}}"><?php \MapasCulturais\i::_e("Trocar selo");?></a>
		                	<a ng-click="removeSeal('institution')" class="btn btn-default delete hltip" title="<?php \MapasCulturais\i::esc_attr_e("excluir selo");?>"> <?php \MapasCulturais\i::_e("Excluir");?> </a>
		                </div>
	            	</div>
				</div>
			</div>
		</div>
		<p>
			<span class="label"><?php \MapasCulturais\i::_e("Coletivo");?></span> <span class="registration-help"><?php \MapasCulturais\i::_e("(Selos atribuídos a agentes coletivos)");?></span><br>
		</p>
		<div class="selos-relacionados">
			<div class="widget">
				<div class="selos clearfix">
					<div ng-if="!entity.registrationSeals.collective" ng-click="editbox.open('set-seal-collective', $event)" class="hltip editable editable-empty" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selo");?>"></div>
					<edit-box id='set-seal-collective' cancel-label="<?php \MapasCulturais\i::esc_attr_e("Cancelar");?>" close-on-cancel='true'>
			            <div ng-if="seals.length > 0" class="widget">
					        <div class="selos clearfix">
					            <div ng-if="!sealRelated(seal)" class="avatar-seal modal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}"  ng-click="setSeal('collective', seal)">
									<img ng-src="{{avatarUrl(seal['@files:avatar.avatarMedium'].url)}}" width="48">
									<h3><a href="{{seal.singleUrl}}" class="ng-binding">{{seal.name}}</a></h3>
					            </div>
					        </div>
					    </div>
					</edit-box>
	            	<div ng-if="entity.registrationSeals.collective" class="avatar-seal">
						<img ng-src="{{avatarUrl(seals[getArrIndexBySealId(entity.registrationSeals.collective)]['@files:avatar.avatarMedium'].url)}}">
		                <div class="descricao-do-selo">
		                    <h1><a href="{{seals[getArrIndexBySealId(entity.registrationSeals.collective)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(entity.registrationSeals.collective)].name}}</a></h1>
		                </div>
		                <div align="right">
		                	<a class="btn btn-default edit hltip" ng-click="openEditBox('set-seal-collective', $event)" title="<?php \MapasCulturais\i::esc_attr_e("Editar");?> {{seals[getArrIndexBySealId(entity.registrationSeals.collective)].name}}"><?php \MapasCulturais\i::_e("Trocar selo");?></a>
		                	<a ng-click="removeSeal('collective')" class="btn btn-default delete hltip" title="<?php \MapasCulturais\i::esc_attr_e("excluir selo");?>"><?php \MapasCulturais\i::esc_attr_e("Excluir");?> </a>
		                </div>
	            	</div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- END Seals -->
<!-- #registration-agent-relations -->
