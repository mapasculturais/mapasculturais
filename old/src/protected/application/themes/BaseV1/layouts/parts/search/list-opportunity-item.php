<article class="objeto clearfix"  ng-repeat="opportunity in opportunities" id="agent-result-{{opportunity.id}}">
    <h1><a href="{{opportunity.singleUrl}}" rel='noopener noreferrer'>{{opportunity.name}}</a></h1>
    <div class="objeto-content clearfix">
        <a href="{{opportunity.singleUrl}}" class="js-single-url" rel='noopener noreferrer'>
            <img class="objeto-thumb" ng-src="{{opportunity['@files:avatar.avatarMedium'].url||assetsUrl.avatarOpportunity}}">
        </a>
        <p class="objeto-resumo">
            {{opportunity.shortDescription}}
        </p>
        <div class="objeto-meta">
            <?php $this->applyTemplateHook('list.opportunity.meta','begin'); ?>
            <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <a href="#" rel='noopener noreferrer'>{{opportunity.type.name}}</a></div>
            <div ng-if="readableOpportunityRegistrationDates(opportunity)"><span class="label"><?php \MapasCulturais\i::_e("Inscrições");?>:</span> {{readableOpportunityRegistrationDates(opportunity)}}</div>
            <div>
                <span class="label">Tags:</span>
                <span ng-repeat="tags in opportunity.terms.tag">
                    <a class="tag tag-opportunity" href="<?php echo $app->createUrl('site', 'search') ?>##(opportunity:(keyword:'{{tags}}'),global:(enabled:(opportunity:!t),filterEntity:opportunity,viewMode:list))">{{tags}}</a>
                </span>
            </div>
            <?php $this->applyTemplateHook('list.opportunity.meta','end'); ?>
        </div>
    </div>
</article>
<!--.objeto-->