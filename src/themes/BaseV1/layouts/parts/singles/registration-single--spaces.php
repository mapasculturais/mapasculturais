<?php 
//dump($entity);
    //$spaceRelation = array_key_exists('useSpaceRelation', $project->metadata) ? $project->metadata['useSpaceRelation'] : '';
    
    //if($spaceRelation == 'optional' || $spaceRelation == 'required'):
?>

<div class="registration-fieldset">
    <h4><?php \MapasCulturais\i::_e("Espaço Vinculado");?></h4>
    <!-- espaços relacionados a inscricao -->
    <div class="registration-list">
        <input type="hidden" id="ownerId" name="ownerId" class="js-editable" data-edit="ownerId"/>
            <div id="registration-agent-space" class="js-registration-agent registration-agent">
                <p ng-if="data.entity.registrationSpace.status < 0" class="alert warning"><?php \MapasCulturais\i::_e("Aguardando confirmação");?></p>
                <div class="clearfix">
                    <img ng-src="{{data.entity.registrationSpace.space.avatar.avatarSmall.url || data.assets.avatarSpace}}" class="registration-space-avatar" />
                    
                    <div class="space-single-view-message">
                        <a ng-if="data.entity.registrationSpace.space" href="{{data.entity.registrationSpace.space.singleUrl}}">{{data.entity.registrationSpace.space.name}}</a>
                        <span ng-if="!data.entity.registrationSpace.space"><?php \MapasCulturais\i::_e("Não informado");?></span>
                    </div>
                </div>
                <div class="registration-space-details">
                    <div ng-repeat="prop in data.spaceLabels" ng-if="data.entity.spaceData[prop.name]">
                        <span class="label">{{prop.label}}</span>: {{prop.name === 'location' ? getReadableLocation(data.entity.spaceData[prop.name]) : data.entity.spaceData[prop.name]}}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php //endif; ?>
