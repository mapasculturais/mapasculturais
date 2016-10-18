<?php
    use MapasCulturais\App;
    $app = App::i();
?>
<ul class="breadcrumb">
    <li>
        <a href="<?php echo $app->createUrl('panel') ?>"><?php echo $this->dict('site: panel')?></a>
    </li>
    <li>
        <a href="<?php echo $app->createUrl('panel', $entity_panel) ?>"><?php echo $this->dict($home_title) ?></a>
    </li>
</ul>
