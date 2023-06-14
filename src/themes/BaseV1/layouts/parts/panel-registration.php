<?php
use MapasCulturais\Entities\Registration;

$app = MapasCulturais\App::i();

$url = $registration->status == Registration::STATUS_DRAFT ? $registration->editUrl : $registration->singleUrl;
$opportunity = $registration->opportunity;
?>
<?php $this->applyTemplateHook('panel-registration', 'before', [$registration]); ?>
<article class="objeto clearfix">
    <?php $this->applyTemplateHook('panel-registration', 'begin', [$registration]); ?>
    <?php if($avatar = $opportunity->avatar): ?>
    <div class="thumb">
        <img src="<?php echo $avatar->transform('avatarSmall')->url ?>" >
    </div>
    <?php endif; ?>

    <?php $this->applyTemplateHook('panel-registration-title', 'before', [$registration]); ?>
    <h1>
        <?php $this->applyTemplateHook('panel-registration-title', 'begin', [$registration]); ?>
        <a href="<?php echo $url; ?>"><?php echo $registration->number ?> - <?php echo $opportunity->name ?></a>
        <?php $this->applyTemplateHook('panel-registration-title', 'end', [$registration]); ?>
    </h1>
    <?php $this->applyTemplateHook('panel-registration-title', 'after', [$registration]); ?>

    <?php $this->applyTemplateHook('panel-registration-meta', 'before', [$registration]); ?>
    <div class="objeto-meta">
        <?php $this->applyTemplateHook('panel-registration-meta', 'begin', [$registration]); ?>

        <div><span class="label"<?php \MapasCulturais\i::esc_attr_e("Responsável:");?>></span> <?php echo $registration->owner->name ?></div>
        <?php
        foreach($app->getRegisteredRegistrationAgentRelations() as $def):
            if(isset($registration->relatedAgents[$def->agentRelationGroupName])):
                $agent = $registration->relatedAgents[$def->agentRelationGroupName][0];
        ?>
        <div><span class="label"><?php echo $def->label ?>:</span> <?php echo $agent->name; ?></div>

        <?php
            endif;
        endforeach;
        ?>
        <?php if($opportunity->registrationCategories): ?>
        <div><span class="label"><?php echo $opportunity->registrationCategTitle ?>:</span> <?php echo $registration->category ?></div>
        <?php endif; ?>
        <?php $this->applyTemplateHook('panel-registration-meta', 'end', [$registration]); ?>
    </div>
    <?php $this->applyTemplateHook('panel-registration-meta', 'after', [$registration]); ?>

    <?php $this->applyTemplateHook('panel-registration', 'end', [$registration]); ?>
</article>
<?php $this->applyTemplateHook('panel-registration', 'aft, [$registration]er');