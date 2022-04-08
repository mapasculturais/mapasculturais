
<?php use MapasCulturais\i; ?>

<div class="box">
    <h1> <?= $title ?></h1> 
    <p><?= $text ?></p>
    
    <?php if(!$app->user->is('guest')):?>
        <?php if($accepted): ?>
            <p><?= sprintf(i::__('Aceito em %s'), date(i::__('d/m/Y à\s H:i'), $accepted->timestamp)) ?></p> 
        <?php else: ?>
            <form action='<?= $url ?>' method="POST"> 
                <button type='submit'><?= i::__('Aceitar') ?> </button>
            </form>
        <?php endif; ?>
    <?php endif; ?>
</div>
