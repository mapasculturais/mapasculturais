        <article class="objeto clearfix" ng-if="openEntity.space">
            <h1><a href="{{openEntity.space.singleUrl}}">{{openEntity.space.name}}</a></h1>
            <div class="objeto-content clearfix">
                <a href="{{openEntity.space.singleUrl}}" class="js-single-url">
                    <img class="objeto-thumb" ng-src="{{openEntity.space['@files:avatar.avatarSmall'].url||assetsUrl.avatarSpace}}">
                </a>
                <p class="objeto-resumo">{{openEntity.space.shortDescription}}</p>
                <div class="objeto-meta">
                    <?php $this->applyTemplateHook('space-infobox-new-fields-before','begin'); ?>
                    <?php $this->applyTemplateHook('space-infobox-new-fields-before','end'); ?>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a ng-click="toggleSelection(data.space.types, getId(types.space, openEntity.space.type.name))">{{openEntity.space.type.name}}</a></div>
                    <div>
                        <span class="label"><?php \MapasCulturais\i::_e("Área de atuação");?>:</span>
                        <span ng-repeat="area in openEntity.space.terms.area">
                            <a ng-click="toggleSelection(data.space.areas, getId(areas, area))">{{area}}</a>{{$last ? '' : ', '}}
                        </span>
                    </div>
                    <div ng-show="openEntity.space.endereco"><span class="label"><?php \MapasCulturais\i::_e("Endereço");?>:</span>{{openEntity.space.endereco}}</div>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade");?>:</span> {{openEntity.space.acessibilidade || '<?php \MapasCulturais\i::_e("Não informado");?>'}}</div>
                    <div>
                        <span class="label">Tags:</span>
                        <span ng-repeat="tags in openEntity.space.terms.tag">
                            <a class="tag tag-space" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(keyword:'{{tags}}'),global:(enabled:(space:!t),filterEntity:space,viewMode:list))">{{tags}}</a>
                        </span>
                    </div>
                </div>
            </div>
        </article>
    
