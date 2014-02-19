<a class="botao adicionar" href="<?php echo $app->createUrl('event', 'create')?>">
adicionar evento</a>
<?php foreach($events as $event): ?>

    <article class="objeto evento clearfix">
        <h1><a href="<?php echo $app->createUrl('event', 'single', array('id'=>$event->id))?>">
            <?php echo $event->name ?></a>
        </h1>
        <div class="objeto-content clearfix">
            <div class="objeto-thumb"><img src="<?php echo $event->avatar->url; ?>"/></div>
            <p class="objeto-resumo">
                <?php echo $event->shortDescription ?>
            </p>
            <div class="objeto-meta">
                <div><span class="label">Linguagem:</span> <?php echo implode(', ', $event->terms['linguagem'])?></div>
                Ocorrências:
                <div style="padding:10px">
                    <?php foreach($event->occurrences as $occ): ?>
                        <?php if($occ->rule->startsOn): ?>
                            <div><span class="label">Data Inicial:</span> <time><?php echo (new DateTime($occ->rule->startsOn))->format('d/m/Y'); ?></time></div>
                        <?php endif; ?>
                        <?php if($occ->rule->until): ?>
                            <div><span class="label">Data Final:</span> <time><?php echo (new DateTime($occ->rule->until))->format('d/m/Y'); ?></time></div>
                        <?php endif; ?>
                        <?php if($occ->rule->startsAt): ?>
                            <div><span class="label">Hora Inicial:</span> <time><?php echo $occ->rule->startsAt ?></time></div>
                        <?php endif; ?>
                        <?php if($occ->rule->endsAt): ?>
                            <div><span class="label">Hora Final:</span> <time><?php echo $occ->rule->endsAt ?></time></div>
                        <?php endif; ?>
                            <hr>
                    <?php endforeach; ?>
                </div>
                <div><span class="label">Horário:</span> <time>00h00</time></div>
                <div><span class="label">Classificação:</span> <?php echo $event->classificacaoEtaria ?></div>
            </div>
        </div>
    </article>
    <!--.objeto-->
<?php endforeach; ?>