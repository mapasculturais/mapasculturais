<?php
$this->addSealsToJs(true);
$this->addRelatedSealsToJs($entity);
?>
<div class="agentes-relacionados" ng-controller="RelatedSealsController">
    <div ng-if="relations.length > 0" class="widget">
    	<h3>Selos Aplicados </h3>
        <div class="selos clearfix">
            <div class="avatar-seal ng-scope" ng-repeat="relation in relations" ng-class="{pending: relation.status < 0}" ng-click="deleteRelation(relation,relation.seal.id)">
				<img ng-src="{{avatarUrl(relation.seal.avatar.avatarMedium.url)}}">
                <div class="descricao-do-selo">
                	<?php $idRelation =  '{{relation.id}}';?>
                    <h1><a href="<?php echo $app->createUrl('seal','sealrelation',[$idRelation]);?>" class="ng-binding">{{relation.seal.name}}</a></h1>
                </div>
            </div>
        </div>
    </div>
    <div ng-if="seals.length > 0" class="widget">
    	<h3>Selos Disponíveis</h3>
        <div class="selos clearfix">
            <div ng-if="!sealRelated(seal.id)" class="avatar-seal" ng-repeat="seal in seals" ng-class="{pending: seal.status < 0}" ng-click="createRelation(seal)">
				<img ng-src="{{avatarUrl(seal['@files:avatar.avatarMedium'].url)}}">
				<div class="descricao-do-selo">
					<h1><a href="{{seal.singleUrl}}" class="ng-binding">{{seal.name}}</a></h1>
				</div>
            </div>
        </div>
    </div>
</div>