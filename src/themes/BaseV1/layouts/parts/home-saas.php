<?php

if(!$app->isEnabled('subsite')){
    return;
}

$class_subsite = 'MapasCulturais\Entities\Subsite';
$num_subsite             = $this->getNumEntities($class_subsite);
$num_verified_subsite    = $this->getNumEntities($class_subsite, true);
$subsite_areas = array_values($app->getRegisteredTaxonomy($class_subsite, 'area')->restrictedTerms);
sort($subsite_areas);

$subsite_types = $app->getRegisteredEntityTypes($class_subsite);

$subsite_img_attributes = 'class="random-feature no-image"';

$subsite = $this->getOneVerifiedEntity($class_subsite);
if($subsite && $img_url = $this->getEntityFeaturedImageUrl($subsite)){
    $subsite_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_subsite = $this->searchsubsiteUrl;
?>
<article id="home-subsite" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-subsite"></span> <?php $this->dict('entities: Subsite') ?></h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_subsite ?></div>
                <div class="statistic-label"><?php $this->dict('entities: registered subsite') ?></div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_subsite; ?></div>
                <div class="statistic-label"><?php $this->dict('entities: subsite') ?> da <?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: subsite'); ?></p>
        <h4><?php printf(\MapasCulturais\i::__('Encontre %s por'), $this->dict('entities: subsite', false)); ?></h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#subsite-terms" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Área de atuação'); ?></a></li>
            <li><a href="#subsite-types" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Tipo'); ?></a></li>
        </ul>
        <div id="subsite-terms" class="tag-box">
            <div>
                <?php foreach ($subsite_areas as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(subsite:(areas:!(<?php echo $i ?>)),global:(enabled:(subsite:!t),filterEntity:subsite))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="subsite-types" class="tag-box">
            <div>
                <?php foreach ($subsite_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(subsite:(types:!(<?php echo $t->id ?>)),global:(enabled:(subsite:!t),filterEntity:subsite))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
    <div class="box">
        <?php if($subsite): ?>
            <a href="<?php echo $subsite->singleUrl ?>">
                <div <?php echo $subsite_img_attributes;?>>
                    <div class="feature-content">
                        <h3><?php \MapasCulturais\i::_e('destaque'); ?></h3>
                        <h2><?php echo $subsite->name ?></h2>
                        <p><?php echo $subsite->shortDescription ?></p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('subsite', 'create') ?>"><?php $this->dict('entities: add new subsite') ?></a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_subsite ?>"><?php \MapasCulturais\i::_e('Ver tudo'); ?></a>
    </div>
</article>
