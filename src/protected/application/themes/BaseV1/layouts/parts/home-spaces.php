<?php 

if(!$app->isEnabled('spaces')){
    return;
}

$class_space = 'MapasCulturais\Entities\Space';
$num_spaces             = $this->getNumEntities($class_space);
$num_verified_spaces    = $this->getNumEntities($class_space, true);
$space_areas = array_values($app->getRegisteredTaxonomy($class_space, 'area')->restrictedTerms);
sort($space_areas);

$space_types = $app->getRegisteredEntityTypes($class_space);

$space_img_attributes = 'class="random-feature no-image"';

$space = $this->getOneVerifiedEntity($class_space);
if($space && $img_url = $this->getEntityFeaturedImageUrl($space)){
    $space_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_spaces = $this->searchSpacesUrl;
?>
<article id="home-spaces" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-space"></span> <?php $this->dict('entities: Spaces') ?></h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_spaces ?></div>
                <div class="statistic-label"><?php $this->dict('entities: registered spaces') ?></div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_spaces; ?></div>
                <div class="statistic-label"><?php $this->dict('entities: spaces') ?> da <?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: spaces'); ?></p>
        <h4><?php \MapasCulturais\i::_e("Encontre ");?><?php $this->dict('entities: spaces') ?> <?php \MapasCulturais\i::_e(" por ");?></h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#space-terms"><?php \MapasCulturais\i::_e("Área de atuação");?></a></li>
            <li><a href="#space-types"><?php \MapasCulturais\i::_e("Tipo");?></a></li>
        </ul>
        <div id="space-terms" class="tag-box">
            <div>
                <?php foreach ($space_areas as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(areas:!(<?php echo $i ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="space-types" class="tag-box">
            <div>
                <?php foreach ($space_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(space:(types:!(<?php echo $t->id ?>)),global:(enabled:(space:!t),filterEntity:space))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
    <div class="box">
        <?php if($space): ?>
            <a href="<?php echo $space->singleUrl ?>">
                <div <?php echo $space_img_attributes;?>>
                    <div class="feature-content">
                        <h3><?php \MapasCulturais\i::_e("destaque");?></h3>
                        <h2><?php echo $space->name ?></h2>
                        <p><?php echo $space->shortDescription ?></p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('space', 'create') ?>"><?php \MapasCulturais\i::_e("Adicionar ");?><?php $this->dict('entities: space') ?></a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_spaces ?>"><?php \MapasCulturais\i::_e("Ver tudo");?></a>
    </div>
</article>
