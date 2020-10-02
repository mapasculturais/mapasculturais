        <article class="objeto clearfix" ng-if="openEntity.space">
            <?php $this->applyTemplateHook('infobox-space','begin'); ?>
            <h1><a href="{{openEntity.space.singleUrl}}" rel='noopener noreferrer'>{{openEntity.space.name}}</a></h1>
            <?php $this->applyTemplateHook('infobox-space.content','before'); ?>
            <div class="objeto-content clearfix">
                <?php $this->applyTemplateHook('infobox-space.content','begin'); ?>
                <a href="{{openEntity.space.singleUrl}}" class="js-single-url" rel='noopener noreferrer'>
                    <img class="objeto-thumb" ng-src="{{openEntity.space['@files:avatar.avatarSmall'].url||assetsUrl.avatarSpace}}">
                </a>
                <p class="objeto-resumo">{{openEntity.space.shortDescription}}</p>
                <div class="objeto-meta">
                    <?php $this->applyTemplateHook('infobox-space.metadata','begin'); ?>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a ng-click="toggleSelection(data.space.filters.type, openEntity.space.type.id.toString())" rel='noopener noreferrer'>{{openEntity.space.type.name}}</a></div>
                    <div>
                        <span class="label"><?php \MapasCulturais\i::_e("Área de atuação");?>:</span>
                        <span ng-repeat="area in openEntity.space.terms.area">
                            <a ng-click="toggleSelection(data.space.areas, getId(areas, area))" rel='noopener noreferrer'>{{area}}</a>{{$last ? '' : ', '}}
                        </span>
                    </div>
                    <div ng-show="openEntity.space.endereco"><span class="label"><?php \MapasCulturais\i::_e("Endereço");?>:</span>{{openEntity.space.endereco}}</div>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade");?>:</span> {{openEntity.space.acessibilidade || '<?php \MapasCulturais\i::_e("Não informado");?>'}}</div>
                    <div ng-if="openEntity.space.terms.tag.length > 0">
                        <span class="label">Tags:</span>
                        <span ng-repeat="tags in openEntity.space.terms.tag">
                            <a class="tag tag-space" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(keyword:'{{tags}}'),global:(enabled:(space:!t),filterEntity:space,viewMode:list))">{{tags}}</a>
                        </span>
                    </div>
                    <?php $this->applyTemplateHook('infobox-space.metadata','end'); ?>
                </div>
                <?php $this->applyTemplateHook('infobox-space.content','end'); ?>
            </div>
            <?php $this->applyTemplateHook('infobox-space.content','after'); ?>
            <?php $this->applyTemplateHook('infobox-space','end'); ?>
        </article>
    
