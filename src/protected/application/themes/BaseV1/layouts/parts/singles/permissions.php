<?php
if($app->user->is('guest') || $this->controller->action === 'create')
    return false;
?>

<div id="permissao" class="aba-content">
    <?php foreach($app->user->agentWithControl as $agent): ?>
        <article class="objeto clearfix">
            <h1>
                <a href="<?php echo $agent->agent->singleUrl; ?>"><?php echo $agent->agent->name; ?></a>
            </h1>
            <div class="objeto-meta">
                <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $agent->agent ]); ?>
                <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
                <div><span class="label"><?php \MapasCulturais\i::_e("Tipo");?>:</span> <?php echo $agent->agent->type->name?></div>
                <div><span class="label"><?php \MapasCulturais\i::_e("Área(s) de atuação");?>:</span> <?php echo implode(', ', $agent->agent->terms['area'])?></div>
            </div>
        </article>
    <?php endforeach; ?>
    <?php if(!$app->user->agentWithControl): ?>
        <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum agente com permissão.");?></div>
    <?php endif; ?>
</div>
