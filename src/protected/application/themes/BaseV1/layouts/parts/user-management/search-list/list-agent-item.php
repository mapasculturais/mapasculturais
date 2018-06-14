<article class="objeto clearfix" ng-repeat="agent in agents" id="agent-result-{{agent.id}}" >
  <div class="objeto-header">
    <h1>{{agent.name | capitalize}}</h1>
    <div class="objeto-header-actions">
      <a class="btn btn-default agent" href="{{agent.singleUrl}}">Agente</a>
      <a class="btn btn-default info" href="<?php echo $app->createUrl('panel', 'userManagement')?>/?userId={{agent.user}}">Info</a>
    </div>
  </div>

  <div class="objeto-content clearfix">
    {{agent.shortDescription}}
  </div>

</article>