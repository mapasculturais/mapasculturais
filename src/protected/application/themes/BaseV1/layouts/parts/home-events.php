<?php 

if(!$app->isEnabled('events')){
    return;
}

$class_event = 'MapasCulturais\Entities\Event';
$num_events             = $this->getNumEvents();
$num_verified_events    = $this->getNumVerifiedEvents();
$event_linguagens = array_values($app->getRegisteredTaxonomy($class_event, 'linguagem')->restrictedTerms);
sort($event_linguagens);

$event_img_attributes = 'class="random-feature no-image"';

$event = $this->getOneVerifiedEntity($class_event);
if($event && $img_url = $this->getEntityFeaturedImageUrl($event)){
    $event_img_attributes = 'class="random-feature" style="background-image: url(' . $img_url . ');"';
}

$url_search_events = $this->searchEventsUrl;

?>

<article id="home-events" class="js-page-menu-item home-entity clearfix">
    <div class="box">
        <h1><span class="icon icon-event"></span><?php \MapasCulturais\i::_e("Eventos");?></h1>
        <div class="clearfix">
            <div class="statistics">
                <div class="statistic"><?php echo $num_events ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("eventos agendados");?></div>
            </div>
            <div class="statistics">
                <div class="statistic"><?php echo $num_verified_events ?></div>
                <div class="statistic-label"><?php \MapasCulturais\i::_e("eventos da ");?><?php $this->dict('home: abbreviation'); ?></div>
            </div>
        </div>
        <p><?php $this->dict('home: events') ?></p>
        <h4>Encontre eventos por</h4>
        <ul class="abas clearfix">
            <li class="active"><a href="#event-terms"><?php \MapasCulturais\i::_e("Linguagem");?></a></li>
        </ul>
        <div id="event-terms" class="tag-box">
            <div>
                <?php foreach ($event_linguagens as $i => $t): ?>
                    <a class="tag" href="<?php echo $app->createUrl('site', 'search') ?>##(event:(linguagens:!(<?php echo $i ?>)),global:(enabled:(event:!t),filterEntity:event))"><?php echo $t ?></a>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <div class="box">
        <?php if($event): ?>
        <a href="<?php echo $event->singleUrl ?>">
            <div <?php echo $event_img_attributes;?>>
                <div class="feature-content">
                    <h3>destaque</h3>
                    <h2><?php echo $event->name ?></h2>
                    <p><?php echo $event->shortDescription ?></p>
                </div>
            </div>
        </a>
        <?php endif; ?>
        <a class="btn btn-accent btn-large add" href="<?php echo $app->createUrl('event', 'create') ?>"><?php \MapasCulturais\i::_e("Adicionar evento");?></a>
        <a class="btn btn-accent btn-large" href="<?php echo $url_search_events ?>"><?php \MapasCulturais\i::_e("Ver tudo");?></a>
    </div>
</article>