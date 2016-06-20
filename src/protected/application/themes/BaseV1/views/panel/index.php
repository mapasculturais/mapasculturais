<?php
$this->layout = 'panel';
?>
<div class="panel-main-content">

    <p class="highlighted-message">
        Olá, <strong><?php echo $app->user->profile->name ?></strong>, bem-vindo ao painel do <?php $this->dict('site: name'); ?>!
    </p>
    <section id="user-stats" class="clearfix">
        <?php if($app->isEnabled('events')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft">Eventos</span>
                        <div class="icon icon-event alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'events') ?>" title="Ver meus eventos"><?php echo $count->events; ?></a>
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('event', 'create'); ?>" title="Adicionar eventos"></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if($app->isEnabled('agents')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft">Agentes</span>
                        <div class="icon icon-agent alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'agents') ?>" title="Ver meus agentes"><?php echo $count->agents; ?></a>
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('agent', 'create'); ?>" title="Adicionar agentes"></a>
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
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'spaces') ?>" title="Ver <?php $this->dict('entities: my spaces') ?>"><?php echo $count->spaces; ?></a>
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('space', 'create'); ?>" title="Adicionar <?php $this->dict('entities: spaces') ?>"></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
        
        <?php if($app->isEnabled('projects')): ?>
            <div>
                <div>
                    <div class="clearfix">
                        <span class="alignleft">Projetos</span>
                        <div class="icon icon-project alignright"></div>
                    </div>
                    <div class="clearfix">
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'projects') ?>" title="Ver meus projetos"><?php echo $count->projects; ?></a>
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('project', 'create'); ?>" title="Adicionar projetos"></a>
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
                        <a class="user-stats-value hltip" href="<?php echo $app->createUrl('panel', 'seals') ?>" title="Ver meus selos"><?php echo $count->seals; ?></a>
                        <a class="icon icon-add alignright hltip" href="<?php echo $app->createUrl('seal', 'create'); ?>" title="Adicionar selos"></a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </section>
    <?php if($app->user->notifications): ?>
    <section id="activities">
        <header>
            <h2>Atividades</h2>
        </header>

        <?php foreach ($app->user->notifications as $notification): ?>
            <div class="activity clearfix">
                <p>
                    <span class="small">Em <?php echo $notification->createTimestamp->format('d/m/Y - H:i') ?></span><br/>
                    <?php echo $notification->message ?>
                </p>
                <?php if ($notification->request): ?>
                    <div>
                        <?php if($notification->request->canUser('approve')): ?><a class="btn btn-small btn-success" href="<?php echo $notification->approveUrl ?>">aceitar</a><?php endif; ?>
                        <?php if($notification->request->canUser('reject')): ?>
                            <?php if($notification->request->requesterUser->equals($app->user)): ?>
                                <a class="btn btn-small btn-default" href="<?php echo $notification->rejectUrl ?>">cancelar</a>
                                <a class="btn btn-small btn-success" href="<?php echo $notification->deleteUrl ?>">ok</a>
                            <?php else: ?>
                                <a class="btn btn-small btn-danger" href="<?php echo $notification->rejectUrl ?>">rejeitar</a>
                            <?php endif ;?>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <div><a class="btn btn-small btn-success" href="<?php echo $notification->deleteUrl ?>">ok</a></div>
                <?php endif ?>
            </div>
        <?php endforeach; ?>
    </section>
    <?php endif; ?>
</div>
