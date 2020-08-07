<article class="objeto clearfix" ng-repeat="event in events" id="event-result-{{agent.id}}" >
  <div class="objeto-header">
    <h1><a href="{{event.singleUrl}}" rel='noopener noreferrer'>{{event.name}}</a></h1>
    <div class="objeto-header-actions">
      <a class="btn btn-default icon icon-user" href="<?php echo $app->createUrl('panel', 'userManagement')?>/?userId={{event.owner.userId}}">Info</a>
    </div>
  </div>

  <div class="objeto-content clearfix">
    <span class="event-subtitle">{{event.subTitle}}</span>
    <div class="objeto-resumo">
      <p>{{event.shortDescription}}</p>
    
      <ul class="event-ocurrences">
        <li ng-repeat="occ in event.occurrences | limitTo:2">
          <a href="{{occ.space.singleUrl}}" rel='noopener noreferrer'>{{occ.space.name}}</a>
            {{occ.space.endereco.trim()}}
            {{occ.rule.description.trim()}}
            <span ng-show="occ.rule.price.length" >. {{occ.rule.price.trim()}}</span>.
        </li>
      </ul>
    </div>

    <div class="objeto-meta">
      <div ng-if="event.project.name">
      <span class="label"><?php \MapasCulturais\i::_e("Projeto");?>:</span>
      <a href="{{event.project.singleUrl}}" rel='noopener noreferrer'>{{event.project.name}}</a>
    </div>
  </div>
</article>
