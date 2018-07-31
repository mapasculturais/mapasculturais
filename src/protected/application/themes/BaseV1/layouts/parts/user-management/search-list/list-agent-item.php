<article class="objeto clearfix" ng-repeat="agent in agents" id="agent-result-{{agent.id}}" >
  <div class="objeto-header">
    <h1><a href="{{agent.singleUrl}}"> {{agent.name}} </a></h1>
    <div class="objeto-header-actions">
      <a class="btn btn-default icon icon-agent" href="{{agent.singleUrl}}"><?php \MapasCulturais\i::_e("Ver Perfil");?></a>
      <a class="btn btn-default icon icon-user" href="<?php echo $app->createUrl('panel', 'userManagement')?>?userId={{agent.user}}">Info</a>
    </div>
  </div>

  <div class="objeto-content clearfix">
    {{agent.shortDescription}}
  </div>

</article>