<article class="objeto clearfix"  ng-repeat="project in projects" id="agent-result-{{project.id}}">
  <div class="objeto-header">
    <h1><a href="{{project.singleUrl}}" rel='noopener noreferrer'>{{project.name}}</a></h1>
    <div class="objeto-header-actions">
      <a class="btn btn-default icon icon-user" href="<?php echo $app->createUrl('panel', 'userManagement')?>?userId={{project.owner.userId}}">Info</a>
    </div>
  </div>
  <div class="objeto-content clearfix">
    {{project.shortDescription}}
  </div>
</article>