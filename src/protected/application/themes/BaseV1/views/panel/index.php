<?php
$this->layout = 'panel';
$posini = 0;
$posfin = 0;
$msg = "";
$button = "";
?>
<div class="panel-main-content">

    <?php $this->part('panel/highlighted-message') ?>

    <section id="user-stats" class="clearfix">
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
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('event', 'create'); ?>" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar eventos");?>"></a>
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
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('agent', 'create'); ?>" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar agentes");?>"></a>
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
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('space', 'create'); ?>" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar");?> <?php $this->dict('entities: spaces') ?>"></a>
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
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('project', 'create'); ?>" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar projetos");?>"></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <?php if($app->isEnabled('seals') && ($app->user->is('superAdmin') || $app->user->is('admin'))): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft">Selos</span>
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
    </section>
    <?php if($app->user->notifications): ?>
    <section id="activities">
        <header>
            <h2><?php \MapasCulturais\i::_e("Atividades");?></h2>
        </header>
        <?php foreach ($app->user->notifications as $notification): ?>
            <?php $posini = strpos($notification->message,"<a"); ?>
            <?php if($posini > 0): ?>
                <?php
                $posfin = strpos($notification->message,"</a>");
                $button = substr($notification->message,$posini,$posfin);
                $msg = str_replace($button,"",$notification->message);
                ?>
            <?php else: ?>
                <?php $msg = $notification->message;?>
            <?php endif;?>
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
    </section>
    <?php endif; ?>
</div>
