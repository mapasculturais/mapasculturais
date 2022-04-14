
<?php 
use MapasCulturais\i;
?>
<div class="box">
    <h3> <?= $title ?></h3> 
    <p><?= $text ?></p>
    <?php if(!$app->user->is('guest')):?>
        <?php if($accepted): ?>
            <p ><?= sprintf(i::__('Aceito em %s'), date(i::__('d/m/Y à\s H:i'), $accepted->timestamp)) ?></p> 
        <?php else: ?>
            <div class='align-button'>
                <form action='<?= $url ?>' method="POST"> 
                    <button class='accept' type='submit'><?= i::__('Aceitar') ?> </button>
                </form>
                <form action ="<?=$app->createUrl('auth', 'logout') ?>" method="POST"> 
                    <button class='refuse' type = 'submit'><?=i::__('Recusar')?></button>
                </form>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>
