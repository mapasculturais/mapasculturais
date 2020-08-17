<?php 

if(!$app->isEnabled('opportunities')){
    return;
}

$class_opportunity = 'MapasCulturais\Entities\Opportunity';
$class_file = 'MapasCulturais\Entities\File';
$num_opportunities           = $this->getNumEntities($class_opportunity);
$num_verified_opportunities  = $this->getNumEntities($class_opportunity, true);

$opportunity_types = $app->getRegisteredEntityTypes($class_opportunity);

$opportunity = $this->getOneVerifiedEntity($class_opportunity);
if($opportunity && $img_url = $this->getEntityFeaturedImageUrl($opportunity)){
    $opportunity_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_opportunities = $this->searchOpportunitiesUrl;

$opportunity_img_attributes = 'class="random-feature no-image"';
?>
<article id="home-opportunities" class="js-page-menu-item home-entity clearfix">
    <?php $this->applyTemplateHook('home-opportunities','begin'); ?>
    <div class="box">
        <h1><span class="icon icon-opportunity"></span> <?php \MapasCulturais\i::_e("Oportunidades");?></h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_opportunities; ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("oportunidades cadastradas");?></div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_opportunities; ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("oportunidades da ");?><?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: opportunities') ?></p>
        <h4><?php \MapasCulturais\i::_e("Encontre oportunidades por");?></h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#opportunity-types" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Tipo");?></a></li>
        </ul>
        <div id="opportunity-types"  class="tag-box">
            <div>
                <?php foreach ($opportunity_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(opportunity:(types:!(<?php echo $t->id ?>)),global:(enabled:(opportunity:!t),filterEntity:opportunity,viewMode:list))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <div class="box">
        <?php if($opportunity): ?>
            <a href="<?php echo $opportunity->singleUrl ?>">
                <div <?php echo $opportunity_img_attributes;?>>
                    <div class="feature-content">
                        <h3><?php \MapasCulturais\i::_e("destaque");?></h3>
                        <h2><?php echo $opportunity->name ?></h2>
                        <p><?php echo $opportunity->shortDescription ?></p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_opportunities ?>"><?php \MapasCulturais\i::_e("Ver tudo");?></a>
    </div>
    <?php $this->applyTemplateHook('home-opportunities','end'); ?>
</article>
