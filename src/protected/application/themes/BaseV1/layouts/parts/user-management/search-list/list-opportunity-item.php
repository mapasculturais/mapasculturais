<article class="objeto clearfix"  ng-repeat="opportunity in opportunities" id="agent-result-{{opportunity.id}}">
  <div class="objeto-header">
    <h1><a href="{{opportunity.singleUrl}}" rel='noopener noreferrer'>{{opportunity.name}}</a></h1>
    <div class="objeto-header-actions">
      <a class="btn btn-default icon icon-user" href="<?php echo $app->createUrl('panel', 'userManagement')?>?userId={{opportunity.owner.userId}}">Info</a>
    </div>
  </div>
  <div class="objeto-content clearfix">
    {{opportunity.shortDescription}}
  </div>
</article>