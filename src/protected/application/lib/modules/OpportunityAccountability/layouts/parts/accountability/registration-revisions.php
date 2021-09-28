<?php
use MapasCulturais\i;

$registration = $entity->registration->accountabilityPhase;
$revisions = $app->repo("EntityRevision")->findEntityRevisions($registration);

foreach($revisions as $revision){
    $dataRevisions[$revision->id] = $revision->getRevisionData();
}

$result = [
    'revisions' => array_reverse($revisions),
    'dataRevisions' => $dataRevisions
];

?>
<?php $this->applyTemplateHook('widget-registration-revision-accountability', 'before'); ?>
<div class="widget">
<?php $this->applyTemplateHook('widget-registration-revision-accountability', 'begin'); ?>
    <h4><?=i::__("Histórico");?></h4>
    <ul class="widget-list widget-registration-revision-accountability">
        <?php foreach ($result['revisions'] as $revision){ ?>
            <?php foreach($result['dataRevisions'][$revision->id] as $v){?>
                
                <?php if($v->key == "openFields"){?>
                    <?php $openFields =  json_decode($v->value, true);?>
                <?php if($openFields){?>
                        <?php if(mb_strpos($revision->message,'Campo') !== false){?>
                            <li class="widget-list-item"><strong><?=$revision->createTimestamp->format('d/m/Y H:i:s')?></strong> - <?=$revision->message?>
                        <?php }?>
                    <?php } else {?>
                        <?php if($revision->message == "Parecer técnico finalizado" || $revision->message == "Parecer técnico reaberto"){ ?>
                            <li class="widget-list-item"><strong><?=$revision->createTimestamp->format('d/m/Y H:i:s')?></strong> - <?=$revision->message?></li>
                        <?php } else{?>
                            <li class="widget-list-item"><strong><?=$revision->createTimestamp->format('d/m/Y H:i:s')?></strong> - <?=i::__("Prestação de contas enviada");?></li>
                        <?php }?>
                    <?php }?>
                    
                <?php }?>
            <?php }?>
        <?php }?>
    </ul>
    <?php $this->applyTemplateHook('widget-registration-revision-accountability', 'end'); ?>
</div>
<?php $this->applyTemplateHook('widget-registration-revision-accountability', 'after'); ?>