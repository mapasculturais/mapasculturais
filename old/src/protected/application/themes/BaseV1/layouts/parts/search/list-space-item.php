        
        
            <article class="objeto clearfix" ng-repeat="space in spaces" id="space-result-{{space.id}}">
                <h1><a href="{{space.singleUrl}}" rel='noopener noreferrer'>{{space.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{space.singleUrl}}" class="js-single-url" rel='noopener noreferrer'>
                        <img class="objeto-thumb" ng-src="{{space['@files:avatar.avatarMedium'].url||defaultImageURL.replace('avatar','avatar--space')}}">
                    </a>
                    <p class="objeto-resumo">{{space.shortDescription}}</p>
                    <div class="objeto-meta">
                        <?php $this->applyTemplateHook('list.space.meta','begin'); ?>
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span>
                            <a ng-click="toggleSelection(data.space.types, getId(types.space, space.type.name))" rel='noopener noreferrer'>{{space.type.name}}</a>
                        </div>
                        <div>
                            <span class="label"><?php \MapasCulturais\i::_e("Área de atuação");?>:</span>
                            <span ng-repeat="area in space.terms.area">
                                <a ng-click="toggleSelection(data.space.areas, getId(areas, area))" rel='noopener noreferrer'>{{area}}</a>{{$last ? '' : ', '}}
                            </span>
                        </div>
                        <div ng-if="space.terms.tag.length > 0">
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in space.terms.tag">
                                <a class="tag tag-space" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(keyword:'{{tags}}'),global:(enabled:(space:!t),filterEntity:space,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                        <div ng-show="space.endereco"><span class="label"><?php \MapasCulturais\i::_e("Endereço");?>:</span> {{space.endereco}}</div>
                        <div><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade");?>:</span> {{space.acessibilidade || '<?php \MapasCulturais\i::_e("Não informado");?>'}}</div>
                        <?php $this->applyTemplateHook('list.space.meta','end'); ?>
                    </div>
                </div>
            </article>
