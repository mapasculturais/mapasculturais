<?php

if(!$app->isEnabled('seals'))
	return;

$seals = $project->registrationSeals;
$owner = isset($seals->owner) ? $seals->owner : '';
$institution = isset($seals->institution) ? $seals->institution : '';
$collective = isset($seals->collective) ? $seals->collective : '';

$this->addSealsToJs(false, [$owner, $institution, $collective]);
if(EMPTY($owner+$institution+$collective))
	return;
?>
<!-- BEGIN Seals -->
<div id="registration-agent" class="registration-fieldset">
    <h4>5. <?php \MapasCulturais\i::_e("Selos Certificadores");?></h4>
    <p class="registration-help"><?php \MapasCulturais\i::_e("Selos certificadores que serão atribuídos aos agentes da inscrição quando a mesma for aprovada.");?></p>
    <ul class="registration-list" ng-controller="SealsController">
        <li class="registration-list-item registration-edit-mode">
            <div class="registration-label">
                <span class="label"><?php \MapasCulturais\i::_e("Agente responsável");?></span>
            </div>
            <div class="registration-description"><?php \MapasCulturais\i::_e("Selos atribuídos a agentes");?></div>

            <div class="js-registration-agent registration-agent">
                <div class="clearfix">
                    <div ng-if="<?php echo $owner; ?>" class="avatar-agent-registration ng-scope">
                        <img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $owner; ?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
                        <div>
                            <a href="{{seals[getArrIndexBySealId(<?php echo $owner; ?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $owner; ?>)].name}}</a>
                            <span ng-if="!<?php echo $owner; ?>"><?php \MapasCulturais\i::_e("Não informado");?></span>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li class="registration-list-item registration-edit-mode">
            <div class="registration-label">
                <span class="label"><?php \MapasCulturais\i::_e("Instituição responsável");?></span>
            </div>
            <div class="registration-description"><?php \MapasCulturais\i::_e("Selos atribuídos a instituições");?></div>

            <div class="js-registration-agent registration-agent">
                <div class="clearfix">
                    <div ng-if="<?php echo $institution; ?>" class="avatar-agent-registration ng-scope">
                        <img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $institution; ?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
                        <div>
                            <a href="{{seals[getArrIndexBySealId(<?php echo $institution; ?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $institution; ?>)].name}}</a>
                            <span ng-if="!<?php echo $institution; ?>"><?php \MapasCulturais\i::_e("Não informado");?></span>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li class="registration-list-item registration-edit-mode">
            <div class="registration-label">
                <span class="label"><?php \MapasCulturais\i::_e("Coletivo");?></span>
            </div>
            <div class="registration-description"><?php \MapasCulturais\i::_e("Selos atribuídos a agentes coletivos");?></div>

            <div class="js-registration-agent registration-agent">
                <div class="clearfix">
                    <div ng-if="<?php echo $collective; ?>" class="avatar-seal-registration ng-scope">
                        <img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $collective; ?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
                        <div>
                            <a href="{{seals[getArrIndexBySealId(<?php echo $collective; ?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $collective; ?>)].name}}</a>
                            <span ng-if="'<?php echo $collective; ?>' == ''"><?php \MapasCulturais\i::_e("Não informado");?></span>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</div>
<!-- END Seals -->
