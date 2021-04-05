<?php
use MapasCulturais\i;
$registration = $entity->registration->accountabilityPhase;
$revisions = $app->repo("EntityRevision")->findEntityRevisions($registration);

foreach($revisions as $revision){
    $dataRevisions[$revision->id] = $revision->getRevisionData();
}

$result = [
    'revisions' => $revisions,
    'dataRevisions' => $dataRevisions
];

?>
<ul>
    <?php foreach ($result['revisions'] as $revision){ ?>
        <?php foreach($result['dataRevisions'][$revision->id] as $v){?>
            
            <?php if($v->key == "openFields"){?>
                <?php $openFields =  json_decode($v->value, true);?>
               <?php if($openFields){?>
                    <?php if(mb_strpos($revision->message,'Campo') !== false){?>
                        <li><small><?=$revision->message?> <?=i::__("em");?> <?=$revision->createTimestamp->format('d/m/Y H:i:s')?></small></li>      
                    <?php }?>
                <?php } else {?>
                    <li><small><?=i::__("Prestação enviada em");?> <?=$v->timestamp->format('d/m/Y H:i:s')?></small></li>
                <?php }?>
                
            <?php }?>
        <?php }?>
    <?php }?>
</ul>