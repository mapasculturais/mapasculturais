<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-avatar
    mc-alert
    mc-container
    registration-form
    registration-info 
    support-actions
    support-steps
');
?>

<div class="support__content">
    <div class="support__steps">
        <support-steps :disabled-steps="disabledSteps" :steps="steps" v-model:step-index="stepIndex"></support-steps>
    </div>

    <mc-container>
        <main class="grid-12">
            <div class="col-12 title">
                <?= i::__('Ficha de inscrição') ?>
                <mc-alert type="helper">
                    <?= i::__('Você está realizando suporte dessa ficha de inscrição. Verifique os dados e corrija caso seja necessário.')?>
                </mc-alert>
            </div>
            
            <div class="col-12">
                
                <div class="support-agent">
                    <div class="support-agent__image">
                        <mc-avatar :entity="registration.owner" size="small"></mc-avatar>
                    </div>
                    <div class="support-agent__name">
                        {{registration.owner.name}}
                    </div>
                </div>
            </div>

            <registration-info :registration="registration" classes="col-12"></registration-info>

            <div class="col-12">
                <registration-form :registration="registration" :step="step"></registration-form>
            </div>
        </main>

        <aside>
            <support-actions :registration="registration"></support-actions>
        </aside>
    </mc-container>
</div>