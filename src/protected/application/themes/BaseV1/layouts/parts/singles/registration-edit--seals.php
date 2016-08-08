<?php
$owner = isset($project->registrationSeals->owner) ? $project->registrationSeals->owner : '';
$institution = isset($project->registrationSeals->institution) ? $project->registrationSeals->institution : '';
$collective = isset($project->registrationSeals->collective) ? $project->registrationSeals->collective : '';

$this->addSealsToJs(false, [$owner, $institution, $collective]);
?>
<!-- BEGIN Seals -->
<div id="registration-agent" class="registration-fieldset">
    <h4>5. Selos Certificadores</h4>
    <p class="registration-help">Selos certificadores que serão atribuídos aos agentes da inscrição quando a mesma for aprovada.</p>
    <ul class="registration-list" ng-controller="SealsController">
        <li class="registration-list-item registration-edit-mode">
            <div class="registration-label">
                <span class="label">Agente responsável</span>
            </div>
            <div class="registration-description">Selos atribuídos a agentes</div>

            <div class="js-registration-agent registration-agent">
                <div class="clearfix">
                    <div ng-if="<?php echo $owner; ?>" class="avatar-agent-registration ng-scope">
                        <img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $owner; ?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
                        <div>
                            <a href="{{seals[getArrIndexBySealId(<?php echo $owner; ?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $owner; ?>)].name}}</a>
                            <span ng-if="!<?php echo $owner; ?>">Não informado</span>
                        </div>
                    </div>
                </div>
            </div>
        </li>
        <li class="registration-list-item registration-edit-mode">
            <div class="registration-label">
                <span class="label">Instituição responsável</span>
            </div>
            <div class="registration-description">Selos atribuídos a instituições</div>

            <div class="js-registration-agent registration-agent">
                <div class="clearfix">
                    <div ng-if="<?php echo $institution; ?>" class="avatar-agent-registration ng-scope">
                        <img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $institution; ?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
                        <div>
                            <a href="{{seals[getArrIndexBySealId(<?php echo $institution; ?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $institution; ?>)].name}}</a>
                            <span ng-if="!<?php echo $institution; ?>">Não informado</span>
                        </div>
                    </div>
                </div>                        
            </div>
        </li>
        <li class="registration-list-item registration-edit-mode">
            <div class="registration-label">
                <span class="label">Coletivo</span>
            </div>
            <div class="registration-description">Selos atribuídos a agentes coletivos</div>

            <div class="js-registration-agent registration-agent">
                <div class="clearfix">
                    <div ng-if="<?php echo $collective; ?>" class="avatar-seal-registration ng-scope">
                        <img ng-src="{{avatarUrl(seals[getArrIndexBySealId(<?php echo $collective; ?>)]['@files:avatar.avatarMedium'].url)}}" class="registration-agent-avatar">
                        <div>
                            <a href="{{seals[getArrIndexBySealId(<?php echo $collective; ?>)].singleUrl}}" class="ng-binding">{{seals[getArrIndexBySealId(<?php echo $collective; ?>)].name}}</a>
                            <span ng-if="'<?php echo $collective; ?>' == ''">Não informado</span>
                        </div>
                    </div>
                </div>
            </div>
        </li>
    </ul>
</div>
<!-- END Seals -->