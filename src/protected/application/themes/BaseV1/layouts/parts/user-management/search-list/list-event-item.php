<article class="objeto clearfix" ng-repeat="event in events" id="event-result-{{agent.id}}" >
  <h1>
    {{event.name}}
    <span class="event-subtitle">{{event.subTitle}}</span>
    <a class="btn btn-default icon icon-event" href="{{event.singleUrl}}"><?php \MapasCulturais\i::_e("Agente");?></a>
    <a class="btn btn-default icon icon-user" href="<?php echo $app->createUrl('panel', 'userManagement')?>/?userId={{event.user}}">Info</a>
  </h1>

  <div class="objeto-content clearfix">
    <div class="objeto-resumo">
      <p>{{event.shortDescription}}</p>
    
      <ul class="event-ocurrences">
        <li ng-repeat="occ in event.occurrences">
          <a href="{{occ.space.singleUrl}}">{{occ.space.name}}</a>
            {{occ.space.endereco.trim()}}
            {{occ.rule.description.trim()}}
            <span ng-show="occ.rule.price.length" >. {{occ.rule.price.trim()}}</span>.
        </li>
      </ul>
    </div>

    <div class="objeto-meta">
      <div ng-if="event.project.name">
      <span class="label"><?php \MapasCulturais\i::_e("Projeto");?>:</span>
      <a href="{{event.project.singleUrl}}">{{event.project.name}}</a>
    </div>
    
    <div>
      <span ng-show="event.terms.linguagem" class="label"><?php \MapasCulturais\i::_e("Linguagem");?>:</span>
      <span ng-repeat="linguagem in event.terms.linguagem">
        <a>{{linguagem}}</a>{{$last ? '' : ', '}}
      </span>
    </div>

    <div>
      <span class="label"><?php \MapasCulturais\i::_e("Classificação");?>:</span>
      <a ng-click="toggleSelection(data.event.classificacaoEtaria, getId(classificacoes, event.classificacaoEtaria))">{{event.classificacaoEtaria}}</a>
    </div>

    <div>
      <span class="label">Tags:</span>
      <span ng-repeat="tags in event.terms.tag">
        <a class="tag tag-event" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(keyword:'{{tags}}'),global:(enabled:(event:!t),filterEntity:event,viewMode:list))">{{tags}}</a>
      </span>
    </div>

  </div>
</article>
