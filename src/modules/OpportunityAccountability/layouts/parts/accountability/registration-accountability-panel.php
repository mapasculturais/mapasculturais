<?php 
$sent = $app->repo('Registration')->findByUser($app->user, 'sent', 3);
$drafts = $app->repo('Registration')->findByUser($app->user, \MapasCulturais\Entities\Registration::STATUS_DRAFT);

?>
<section id="inscricoes-rascunho" class="panel-list">      
    <header>
    <?php foreach($sent as $registration): ?>           
        <?php if($registration->opportunity->isAccountabilityPhase){?>
            <h2><?php \MapasCulturais\i::_e("Prestações de contas enviadas");?></h2>
            <?php break;?>
        <?php } ?>
    <?php endforeach; ?>
        
    </header>
    <?php foreach($sent as $registration): ?>           
        <?php if($registration->opportunity->isAccountabilityPhase){?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php } ?>
    <?php endforeach; ?>
</section>


<section id="inscricoes-rascunho" class="panel-list">      
    <header>
    <?php foreach($drafts as $registration): ?>           
        <?php if($registration->opportunity->isRegistrationOpen() && $registration->opportunity->isAccountabilityPhase){?>            
            <h2><?php \MapasCulturais\i::_e("Prestações de contas ainda não enviadas");?></h2>
            <?php break;?>
        <?php } ?>
    <?php endforeach; ?>
        
    </header>
    <?php foreach($drafts as $registration): ?>           
        <?php if($registration->opportunity->isRegistrationOpen() && $registration->opportunity->isAccountabilityPhase){?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php } ?>
    <?php endforeach; ?>
</section>