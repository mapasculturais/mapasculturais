        
        
            <article class="objeto clearfix"  ng-repeat="project in projects" id="agent-result-{{project.id}}">
                <h1><a href="{{project.singleUrl}}" rel='noopener noreferrer'>{{project.name}}</a></h1>
                <div class="objeto-content clearfix">
                    <a href="{{project.singleUrl}}" class="js-single-url" rel='noopener noreferrer'>
                        <img class="objeto-thumb" ng-src="{{project['@files:avatar.avatarMedium'].url||assetsUrl.avatarProject}}">
                    </a>
                    <p class="objeto-resumo">
                        {{project.shortDescription}}
                    </p>
                    <div class="objeto-meta">
                        <?php $this->applyTemplateHook('list.project.meta','begin'); ?>
                        <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a href="#" rel='noopener noreferrer'>{{project.type.name}}</a></div>
                        <div ng-if="readableProjectRegistrationDates(project)"><span class="label"><?php \MapasCulturais\i::_e("Inscrições");?>:</span> {{readableProjectRegistrationDates(project)}}</div>
                        <div ng-if="project.terms.tag.length > 0">
                            <span class="label">Tags:</span>
                            <span ng-repeat="tags in project.terms.tag">
                                <a class="tag tag-project" href="<?php echo $app->createUrl('site', 'search') ?>##(project:(keyword:'{{tags}}'),global:(enabled:(project:!t),filterEntity:project,viewMode:list))">{{tags}}</a>
                            </span>
                        </div>
                        <?php $this->applyTemplateHook('list.project.meta','end'); ?>
                    </div>
                </div>
            </article>
            <!--.objeto-->
