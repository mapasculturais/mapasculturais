<?php
if($this->controller->action == 'edit' || $this->controller->action == 'create' ) {
    return;
}

$app = \MapasCulturais\App::i();
$entityRevisions = $app->repo("EntityRevision")->findEntityRevisions($entity);
?>
<?php if(count($entityRevisions) > 0): ?>
<div class="widget">
    <h3><?php \MapasCulturais\i::_e("Histórico");?></h3>
	<?php foreach($entityRevisions as $revision):?>
    <ul class="widget-list js-slimScroll">
        <li id="revision-<?php echo $revision->id?>" class="widget-list-item" >
            <a class="js-metalist-item-display" href="<?php echo $app->createUrl("entityRevision","history",[$revision->id])?>"><span><?php echo $revision->message?> [<?php echo $revision->createTimestamp->format('d/m/Y H:i:s');?>]</span></a>
        </li>
    </ul>
	<?php endforeach;?>
</div>
<?php endif; ?>
