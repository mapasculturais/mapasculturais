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
            <div class="avatar" ng-repeat="relation in relations" ng-class="{pending: relation.status < 0}" ng-click="deleteRelation(relation)">
                <span >
                    <img ng-src="{{relation.seal.avatar.avatarMedium.url}}">
                </span>
                <div class="descricao-do-selo">
                    <!-- h1><a href="{{seal.singleUrl}}">{{seal.name}}</a></h1-->
                </div>
            </div>
        </div>
    </div>
    <div ng-if="seals.length > 0" class="widget">
    	<h3>Selos Disponíveis</h3>
        <div class="selos clearfix">
            <div ng-if="!sealRelated(seal)" class="avatar" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}"  ng-click="createRelation(seal)">
                <span>
                    <img ng-src="{{seal['@files:avatar.avatarMedium'].url}}">
                </span>

                <div class="descricao-do-selo">
                    <!-- h1><a href="{{seal.singleUrl}}">{{seal.name}}</a></h1-->
                </div>
            </div>
        </div>
    </div>
</div>