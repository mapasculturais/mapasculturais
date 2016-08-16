<?php

if(!$app->isEnabled('saas')){
    return;
}

$class_saas = 'MapasCulturais\Entities\SaaS';
$num_saas             = $this->getNumEntities($class_saas);
$num_verified_saas    = $this->getNumEntities($class_saas, true);
$saas_areas = array_values($app->getRegisteredTaxonomy($class_saas, 'area')->restrictedTerms);
sort($saas_areas);

$saas_types = $app->getRegisteredEntityTypes($class_saas);

$saas_img_attributes = 'class="random-feature no-image"';

$saas = $this->getOneVerifiedEntity($class_saas);
if($saas && $img_url = $this->getEntityFeaturedImageUrl($saas)){
    $saas_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_saas = $this->searchsaasUrl;
?>
<article id="home-saas" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-saas"></span> <?php $this->dict('entities: SaaS') ?></h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_saas ?></div>
                <div class="statistic-label"><?php $this->dict('entities: registered saas') ?></div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_saas; ?></div>
                <div class="statistic-label"><?php $this->dict('entities: saas') ?> da <?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: saas'); ?></p>
        <h4>Encontre <?php $this->dict('entities: saas') ?> por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#saas-terms">Área de atuação</a></li>
            <li><a href="#saas-types">Tipo</a></li>
        </ul>
        <div id="saas-terms" class="tag-box">
            <div>
                <?php foreach ($saas_areas as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(saas:(areas:!(<?php echo $i ?>)),global:(enabled:(saas:!t),filterEntity:saas))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
        <div id="saas-types" class="tag-box">
            <div>
                <?php foreach ($saas_types as $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(saas:(types:!(<?php echo $t->id ?>)),global:(enabled:(saas:!t),filterEntity:saas))"><?php echo $t->name ?></a>
                <?php endforeach; ?>
            </div>
        </div>

    </div>
    <div class="box">
        <?php if($saas): ?>
            <a href="<?php echo $saas->singleUrl ?>">
                <div <?php echo $saas_img_attributes;?>>
                    <div class="feature-content">
                        <h3>destaque</h3>
                        <h2><?php echo $saas->name ?></h2>
                        <p><?php echo $saas->shortDescription ?></p>
                    </div>
                </div>
            </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('saas', 'create') ?>">Adicionar <?php $this->dict('entities: saas') ?></a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_saas ?>">Ver tudo</a>
    </div>
</article>
