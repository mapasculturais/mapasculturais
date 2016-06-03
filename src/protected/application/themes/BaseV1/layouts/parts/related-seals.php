<?php
if($this->controller->action === 'create')
    return;

$this->addPermitedSealsToJs();
$this->addRelatedSealsToJs($entity);
?>
<div class="selos-relacionados" ng-controller="RelatedSealsController">
    <div class="widget">
    	<h3>Selos Aplicados </h3>
        <div class="selos clearfix">
            <div class="avatar" ng-repeat="relation in relations" ng-class="{pending: seal.status < 0}">
                <a href="{{relation.seal.singleUrl}}" ng-click="toggleSeal(relation.seal)">
                    <img ng-src="{{relation.seal['@files:avatar.avatarMedium'].url}}">
                </a>
                <div class="descricao-do-selo">
                    <!-- h1><a href="{{seal.singleUrl}}">{{seal.name}}</a></h1-->
                </div>
            </div>
        </div>
    </div>
    <div class="widget">
    	<h3>Selos Disponíveis</h3>
        <div class="selos clearfix">
            <div ng-if="!sealRelated(seal)" class="avatar" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}">
                <span ng-click="toggleSeal(seal)">
                    <img ng-src="{{seal['@files:avatar.avatarMedium'].url}}">
                </span>

                <div class="descricao-do-selo">
                    <!-- h1><a href="{{seal.singleUrl}}">{{seal.name}}</a></h1-->
                </div>
            </div>
        </div>
    </div>
</div>