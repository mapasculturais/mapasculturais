<?php

namespace OpportunityAccountability;

use MapasCulturais\i;

$app = \MapasCulturais\App::i();

?>

<div ng-controller='OpportunityProjects'>
    <div class="card" ng-repeat="project in data.projects">
        <div class="content">
            <header>
                <div class="thumb">
                    <a href="<?php echo $app->createUrl('project', 'single'); ?>{{project.id}}">
                        <img ng-if="!project.avatar" src="<?php $this->asset('img/avatar--project.png'); ?>">
                        <img ng-if="project.avatar" src="{{project.avatar}}">
                    </a>
                </div>
                <h3><a href="<?php echo $app->createUrl('project', 'single'); ?>{{project.id}}">{{project.name}}</a></h3>
                <p ng-if="project.owner.name"><span class="label"><?php i::_e('Por:'); ?></span> <a class="agent color-agent" href="{{project.owner.singleUrl}}">{{project.owner.name}}</a></p>
                <p ng-if="project.type.name"><span class="label"><?php i::_e('Tipo:'); ?></span> <a class="type" href="<?php echo $app->createUrl('site', 'search'); ?>##(global:(filterEntity:event,viewMode:list))">{{project.type.name}}</a></p>
                <p ng-if="project.registrationFrom && project.registrationTo"><span class="label"><?php i::_e('Inscrições:'); ?></span> <?php i::_e('de'); ?> {{project.registrationFrom | date:"dd/MM/yyyy"}} <?php i::_e('até'); ?> {{project.registrationTo | date:"dd/MM/yyyy"}}</p>
            </header>

            <div class="content">
                <p ng-if="project.shortDescription">{{project.shortDescription}}</p>
            </div>

            <footer>
                <div class="tags" ng-if="project.terms.tag.length">
                    <p>
                        <span class="label"><?php i::_e('Tags:'); ?></span>
                        <a ng-repeat="tag in project.terms.tag" class="tag" href="<?php echo $app->createUrl('site', 'search'); ?>##(project:(keyword:'{{tag}}'),global:(enabled:(project:!t),filterEntity:project,viewMode:list))">{{tag}}</a>
                    </p>
                </div>

                <div class="status">
                    <p><span><?php i::_e('Status:'); ?></span></p>
                    <a class="button" href="<?php echo $app->createUrl('site', 'search'); ?>##(event:(keyword:'{{project.name}}'),global:(enabled:(event:!t),filterEntity:event,viewMode:list))"><?php i::_e('Ver agenda'); ?></a>
                </div>
            </footer>
        </div><!-- /.content -->
    </div><!-- /.card -->

    <footer>
        <button ng-if="!(data.projectsAPIMetadata.numPages == data.projectsAPIMetadata.page)" ng-click="loadMore()">Carregar mais</button>
    </footer>

</div><!-- /ng-controller OpportunityProjects -->