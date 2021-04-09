<?php
$this->layout = 'panel';

$subsite = $app->getCurrentSubsite();

$posini = 0;
$posfin = 0;
$msg = "";
$button = "";

?>
<?php $this->applyTemplateHook('content','before'); ?>
<div class="panel-main-content">
<?php $this->applyTemplateHook('content','begin'); ?>

    <?php $this->part('panel/highlighted-message') ?>

    <?php if($subsite && $subsite->canUser('modify')):?>
    <p class="highlighted-message" style="margin-top:-2em;">
        <?php printf(\MapasCulturais\i::__('Você é administrador deste subsite. Clique %saqui%s para configurar.'), '<a rel="noopener noreferrer" href="' . $subsite->singleUrl . '">', '</a>'); ?>
    </p>
    <?php endif; ?>

    <?php $this->applyTemplateHook('content.entities','before'); ?>
    <section id="user-stats" class="clearfix">
        <?php $this->applyTemplateHook('content.entities','begin'); ?>
        <?php if($app->isEnabled('events')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft"><?php $this->dict('entities: Events') ?></span>
                        <div class="icon icon-event alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'events') ?>" title="<?php \MapasCulturais\i::esc_attr_e("Ver Meus eventos");?>"><?php echo $count->events; ?></a>
                        <span class="user-stats-value hltip">|</span>
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'events') ?>#tab=permitido" title="<?php \MapasCulturais\i::esc_attr_e("Ver Eventos Cedidos");?>"><?php echo count($app->user->hasControlEvents);?></a>
                        <?php $this->renderModalFor('event', false, false, "icon icon-add alignright"); ?>  
                                          
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($app->isEnabled('agents')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft"><?php $this->dict('entities: Agents') ?></span>
                        <div class="icon icon-agent alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'agents') ?>" title="<?php \MapasCulturais\i::esc_attr_e("Ver meus agentes");?>"><?php echo $count->agents; ?></a>
                        <span class="user-stats-value hltip">|</span>
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'agents') ?>#tab=permitido" title="<?php \MapasCulturais\i::esc_attr_e("Ver Agentes Cedidos");?>"><?php echo count($app->user->hasControlAgents);?></a>
                        <?php $this->renderModalFor('agent', false, false, "icon icon-add alignright"); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($app->isEnabled('spaces')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft"><?php $this->dict('entities: Spaces') ?></span>
                        <div class="icon icon-space alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'spaces') ?>" title="<?php \MapasCulturais\i::esc_attr_e("Ver");?> <?php $this->dict('entities: My spaces')?>"><?php echo $count->spaces; ?></a>
                        <span class="user-stats-value hltip">|</span>
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'spaces') ?>#tab=permitido" title="<?php \MapasCulturais\i::esc_attr_e("Ver Espaços Cedidos");?>"><?php echo count($app->user->hasControlSpaces);?></a>
                        <?php $this->renderModalFor('space', false, false, "icon icon-add alignright"); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($app->isEnabled('projects')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft"><?php $this->dict('entities: Projects') ?></span>
                        <div class="icon icon-project alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'projects') ?>" title="<?php \MapasCulturais\i::esc_attr_e("Ver meus projetos");?>"><?php echo $count->projects; ?></a>
                        <span class="user-stats-value hltip">|</span>
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'projects') ?>#tab=permitido" title="<?php \MapasCulturais\i::esc_attr_e("Ver Projetos Cedidos");?>"><?php echo count($app->user->hasControlProjects);?></a>
                        <?php $this->renderModalFor('project', false, false, "icon icon-add alignright"); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>
 
       <?php if($app->isEnabled('opportunities')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft"><?php $this->dict('entities: Opportunities') ?></span>
                        <div class="icon icon-opportunity alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'opportunities') ?>" title="<?php \MapasCulturais\i::esc_attr_e("Ver minhas oportunidades");?>"><?php echo $count->opportunities; ?></a>
                        <span class="user-stats-value hltip">|</span>
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'opportunities') ?>#tab=permitido" title="<?php \MapasCulturais\i::esc_attr_e("Ver Oportunidades Cedidas");?>"><?php echo count($app->user->hasControlOpportunities);?></a>
                        <?php $this->renderModalFor('opportunity', false, false, "icon icon-add alignright"); ?>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($app->isEnabled('subsite') && $app->user->is('saasAdmin')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft"><?php \MapasCulturais\i::_e('Subsite'); ?></span>
                        <div class="icon icon-subsite alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'subsite') ?>" title="<?php \MapasCulturais\i::esc_attr_e('Ver meus subsites'); ?>"><?php echo $count->subsite; ?></a>
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('subsite', 'create'); ?>" title="<?php \MapasCulturais\i::esc_attr_e('Adicionar subsite'); ?>"></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php if($app->isEnabled('seals') && $app->user->is('admin')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft"><?php \MapasCulturais\i::_e('Selos'); ?></span>
                        <div class="icon icon-seal alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'seals') ?>" title="<?php \MapasCulturais\i::esc_attr_e("Ver meus selos");?>"><?php echo $count->seals; ?></a>
                        <span class="user-stats-value hltip">|</span>
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'seals') ?>#tab=permitido" title="<?php \MapasCulturais\i::esc_attr_e("Ver Selos Cedidos");?>"><?php echo count($app->user->hasControlSeals);?></a>
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('seal', 'create'); ?>" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar selos");?>"></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        <?php $this->applyTemplateHook('content.entities','end'); ?>
    </section>
    <?php $this->applyTemplateHook('content.entities','after'); ?>
    
<div class="panel-activities">
    <?php if($opportunitiesToEvaluate = $app->user->opportunitiesCanBeEvaluated): ?>
    <?php $this->applyTemplateHook('content.avaluations','before'); ?>
    <section id="avaliacoes" class="panel-list">
        <?php $this->applyTemplateHook('content.avaluations','begin'); ?>
        <header>
            <h2><?php \MapasCulturais\i::_e("Avaliações pendentes");?></h2>
        </header>
        <?php foreach($opportunitiesToEvaluate as $entity): ?>
            <?php $this->part('panel-evaluation', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php $this->applyTemplateHook('content.avaluations','end'); ?>
    </section>
    <?php $this->applyTemplateHook('content.avaluations','after'); ?>
    <?php endif; ?>

    <?php $this->applyTemplateHook('content.registration','before'); ?>
    <?php $drafts = $app->repo('Registration')->findByUser($app->user, \MapasCulturais\Entities\Registration::STATUS_DRAFT); ?>
    <?php if ($drafts): ?>
    <section id="inscricoes-rascunho" class="panel-list">      
        <?php $this->applyTemplateHook('content.registration','begin'); ?>
        <header>
        <?php foreach($drafts as $registration): ?>           
            <?php if($registration->opportunity->isRegistrationOpen() && !$registration->opportunity->isAccountabilityPhase){?>
                <h2><?php \MapasCulturais\i::_e("Inscrições ainda não enviadas");?></h2>
                <?php break;?>
            <?php } ?>
        <?php endforeach; ?>
            
        </header>
        <?php foreach($drafts as $registration): ?>           
            <?php if($registration->opportunity->isRegistrationOpen() && !$registration->opportunity->isAccountabilityPhase){?>
                <?php $this->part('panel-registration', array('registration' => $registration)); ?>
            <?php } ?>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <?php $sent = $app->repo('Registration')->findByUser($app->user, 'sent', 3); ?>
    <?php if ($sent): ?>
        <section id="inscricoes-enviadas" class="panel-list">
            <header>
                <h2><?php \MapasCulturais\i::_e("Inscrições enviadas");?></h2>
            </header>
            <?php foreach($sent as $registration): ?>
                <?php if(!$registration->opportunity->isAccountabilityPhase){?>
                    <?php $this->part('panel-registration', array('registration' => $registration)); ?>
                <?php } ?>
            <?php endforeach; ?>
            <?php $this->applyTemplateHook('content.registration','end'); ?>
        </section>
    <?php endif; ?>
    <?php $this->applyTemplateHook('content.registration','after'); ?>


    <?php if($app->user->notifications): ?>
    <?php $this->applyTemplateHook('content.notification','before'); ?>
    <section id="activities">
        <?php $this->applyTemplateHook('content.notification','begin'); ?>
        <header>
            <h2><?php \MapasCulturais\i::_e("Atividades");?></h2>
        </header>
        <?php foreach ($app->user->notifications as $notification): ?>
            <?php $posini = strpos($notification->message,"<a  rel='noopener noreferrer' "); ?>
            
            <?php $msg = $notification->message;?>
        
            <div class="activity clearfix">
                <p>
                    <span class="small"><?php \MapasCulturais\i::_e("Em");?> <?php echo $notification->createTimestamp->format('d/m/Y - H:i') ?></span><br/>
                    <?php echo $msg; ?>
                </p>
                <?php if ($notification->request): ?>
                    <div>
                        <?php if($notification->request->canUser('approve')): ?><a class="btn btn-small btn-success" href="<?php echo $notification->approveUrl ?>"><?php \MapasCulturais\i::_e("aceitar");?></a><?php endif; ?>
                        <?php if($notification->request->canUser('reject')): ?>
                            <?php if($notification->request->requesterUser->equals($app->user)): ?>
                                <a class="btn btn-small btn-default" href="<?php echo $notification->rejectUrl ?>"><?php \MapasCulturais\i::_e("cancelar");?></a>
                                <a class="btn btn-small btn-success" href="<?php echo $notification->deleteUrl ?>"><?php \MapasCulturais\i::_e("ok");?></a>
                            <?php else: ?>
                                <a class="btn btn-small btn-danger" href="<?php echo $notification->rejectUrl ?>"><?php \MapasCulturais\i::_e("rejeitar");?></a>
                            <?php endif ;?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div>
                    <?php if($button):?>
                        <?php echo $button;?>
                    <?php endif;?>
                    <a class="btn btn-small btn-success" href="<?php echo $notification->deleteUrl ?>"><?php \MapasCulturais\i::_e("ok");?></a>
                    </div>
                <?php endif ?>
            </div>
        <?php endforeach; ?>
        
        <?php $this->applyTemplateHook('content.notification','end'); ?>
    </section>
    <?php $this->applyTemplateHook('content.notification','after'); ?>
    <?php endif; ?>
</div>

    <?php $this->applyTemplateHook('settings','before'); ?>
    <ul class="panel-settings">
        <?php $this->applyTemplateHook('settings','begin'); ?>

        <?php $this->applyTemplateHook('settings','end'); ?>
        <div class="clear"></div>
    </ul>
    <?php $this->applyTemplateHook('settings','after'); ?>
    
    <?php $this->applyTemplateHook('content','end'); ?>
</div>
<?php $this->applyTemplateHook('content','after'); ?>