<?php
if($this->controller->action == 'edit' || $this->controller->action == 'create' ) {
    return;
}

$app = \MapasCulturais\App::i();
$entityRevisions = $app->repo("EntityRevision")->findEntityRevisions($entity);
$currentDate = null;
?>
<?php if(is_array($entityRevisions) && count($entityRevisions) > 0): ?>
<div class="widget">
    <h3><?php \MapasCulturais\i::_e("Histórico");?></h3>
    <ul class="widget-list js-slimScroll horizontalScroll">
	<?php foreach($entityRevisions as $revision):?>
        <li id="revision-<?php echo $revision->id?>" class="widget-list-item" >
            <?php if(is_null($currentDate) || trim($currentDate->format('d/m/Y')) != ($revision->createTimestamp->format('d/m/Y'))):?>
            <small><?php echo $revision->createTimestamp->format('d/m/Y');?></small>
            <?php endif;?><a class="js-metalist-item-display" href="<?php echo $app->createUrl("entityRevision","history",[$revision->id])?>"><span><?php echo $revision->message;?>
            [<?php echo $revision->createTimestamp->format('H:i:s');?>]</span></a>
        </li>
        <?php $currentDate = $revision->createTimestamp;?>
	<?php endforeach;?>
    </ul>
</div>
<?php endif; ?>
