<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
$this->import('
   mc-card
   mc-avatar
   mc-title
   mc-entities
');
?>

<div v-if="opportunities.length>0" class="opportunity-list">
    <mc-title tag="h4" class="bold"><?php i::esc_attr_e('Lista de oportunidades vinculadas'); ?></mc-title>
    <div class="opportunity-list__content">
        <ul class="opportunity-list__list">
            <div class="col-12 opportunity-list__container">
                <li v-for="opp in opportunities">
                    <div class="col-12 grid-12 opportunity-list__card">
                        <div class="col-12 opportunity-list__cardheader">
                            <mc-avatar :entity="opp" size="xsmall"></mc-avatar>
                            <p class="opportunity-list__name opportunity__color bold"> {{opp.name}}</p>
                        </div>
                        <div v-if="!opp.isLastPhase && opp.registrationFrom.isFuture()" class="col-12">
                            <p v-if="opp.registrationTo" class="semibold opportunity-list__registration"> <?= i::__('Inscrições de') ?> <span v-if="opp.registrationFrom"> {{opp.registrationFrom.date('2-digit year')}}</span> <?= i::__('até') ?> {{opp.registrationTo.date('2-digit year')}} às {{opp.registrationTo.hour('2-digit')}}h</p>

                        </div>
                        <div v-if="opp.registrationTo.isFuture() && opp.registrationFrom.isPast()">
                            <p v-if="opp.registrationTo" class="semibold opportunity-list__registration"><?= i::__('Inscrições encerrarão no dia') ?> {{opp.registrationTo.date('2-digit year')}} <?= i::__('às') ?> {{opp.registrationTo.hour('2-digit')}}h
                            </p>
                        </div>
                        <div v-if="opp.registrationTo.isPast()">
                            <p v-if="opp.registrationTo" class="semibold opportunity-list__registration"><?= i::__('As inscriçoes estão encerradas desde') ?> {{opp.registrationTo.date('2-digit year')}} às {{opp.registrationTo.hour('2-digit')}}h</p>
                        </div>
                        <div class="col-12 opportunity-list__cardlink primary__color">
                            <mc-link :entity="opp" class="opportunity-list__link primary__color bold"><?php i::esc_attr_e('Acessar') ?><mc-icon name="arrowPoint-right" class="opportunity-list__icon"></mc-icon></mc-link>
                        </div>
                    </div>
                </li>
            </div>
        </ul>
    </div>
</div>