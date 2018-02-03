<?php
    use MapasCulturais\App;
    $app = App::i();

    if($app->user->is('guest'))
        return;

?>
<ul class="breadcrumb">
    <li>
        <a href="<?php echo $app->createUrl('panel') ?>"><?php echo $this->dict('site: panel')?></a>
    </li>
    <li>
        <a href="<?php echo $app->createUrl('panel', $entity_panel) ?>"><?php echo $this->dict($home_title) ?></a>
    </li>
    <li>
        <span><?php echo $this->dict("entities: " . ucwords(substr($entity_panel,0,-1)));?></span>
    </li>
</ul>
