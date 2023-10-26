<input type="hidden" id="agenda-count-hidden" value="<?php echo count($events); ?>">
<?php foreach($events as $event): ?>
    <article class="objeto clearfix">
        <h1><a href="<?php echo $app->createUrl('event', 'single', array($event->id))?>">
            <?php echo $event->name ?></a>
        </h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"><img src="<?php echo $event->avatar ? $event->avatar->transform('avatarMedium')->url : '" style="display:none'; ?>"/></div>
            <div class="objeto-resumo">
                <p>
                    <?php echo $event->shortDescription ?>
                </p>
                <ul class="event-ocurrences">
                    <?php
                    unset($lastSpaceId);
                    $occurrences = $event->occurrences->toArray();
                    usort($occurrences, function($a, $b) {
                        return $a->space->id - $b->space->id;
                    });
                    foreach($occurrences as $occ):
                        if($entity->getClassName() === 'MapasCulturais\Entities\Space' && !$entity->equals($occ->space)){
                            continue;
                        }
                        ?>
                        <li>
                            <?php if($entity->className !== 'MapasCulturais\Entities\Space' && (!isset($lastSpaceId) || $occ->space->id !== $lastSpaceId)): ?>
                                <div><a href="<?php echo $app->createUrl('space', 'single', array($occ->space->id))?>"><?php echo $occ->space->name; ?></a></div>
                            <?php endif; ?>
                            <?php if(isset($occ->rule->description)): ?>
                                <div><?php echo trim($occ->rule->description).'. '.$occ->rule->price;?></div>
                            <?php endif; ?>
                        </li>
                        <?php
                        $lastSpaceId = $occ->space->id;
                    endforeach;
                    ?>
                </ul>
            </div>
            <div class="objeto-meta">
                <?php if (!empty($event->terms['linguagem'])): ?>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Linguagem");?>:</span> <?php echo implode(', ', $event->terms['linguagem'])?></div>
                <?php endif; ?>
                <?php if (!empty($event->terms['linguagem'])): ?>
                    <div><span class="label"><?php \MapasCulturais\i::_e("Classificação");?>:</span> <?php echo $event->classificacaoEtaria; ?></div>
                <?php endif; ?>
            </div>

        </div>
    </article>
    <!--.objeto-->
<?php endforeach; ?>