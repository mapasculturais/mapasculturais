<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-notification
    opportunity-phase-publish-config-registration
    mc-alert
');
?>
<mc-card>
    <div class="grid-12 opportunity-phase-list-evaluation">
        <mc-alert v-if="!entity.opportunity.publishedRegistrations" class="col-12" type="helper">
            <?= i::__('Após a finalização das avaliações, você precisa acessar a <strong>lista de inscrições para aplicar os resultados dessas avaliações</strong>.') ?>
        </mc-alert>
        
        <div class="col-6 opportunity-phase-list-evaluation_action--center">
           <div class="col-6 opportunity-phase-list-evaluation_action__box">
                <div class="opportunity-phase-list-evaluation__status col-6">
                    <h4 class="bold"><?php i::_e("Resumo das inscrições") ?></h4>
                    <p v-if="entity.opportunity.summary?.registrations"><?= i::__("Quantidade inscrições:") ?> <strong>{{entity.opportunity.summary?.registrations}}</strong> <?php i::_e('inscrições') ?></p>
                    <p v-if="entity.opportunity.summary?.evaluated"><?= i::__("Quantidade de inscrições <strong>avaliadas</strong>:") ?> <strong>{{entity.opportunity.summary?.evaluated}}</strong> <?php i::_e('inscrições') ?></p>
                    <p v-if="entity.opportunity.summary?.Approved"><?= i::__("Quantidade de inscrições <strong>selecionadas</strong>:") ?> <strong>{{entity.opportunity.summary?.Approved}}</strong> <?php i::_e('inscrições') ?></p>
                    <p v-if="entity.opportunity.summary?.Waitlist"><?= i::__("Quantidade de inscrições <strong>suplentes</strong>:") ?> <strong>{{entity.opportunity.summary?.Waitlist}}</strong> <?php i::_e('inscrições') ?></p>
                    <p v-if="entity.opportunity.summary?.Invalid"><?= i::__("Quantidade de inscrições <strong>inválidas</strong>:") ?> <strong>{{entity.opportunity.summary?.Invalid}}</strong> <?php i::_e('inscrições') ?></p>
                    <p v-if="entity.opportunity.summary?.Pending"><?= i::__("Quantidade de inscrições <strong>pendentes</strong>:") ?> <strong>{{entity.opportunity.summary?.Pending}}</strong> <?php i::_e('inscrições') ?></p>
                            
                </div>   
                <div class="col-6 opportunity-phase-list-evaluation__cardfooter">
                    <div>
                        <mc-link :entity="entity.opportunity" class="opportunity-phase-list-evaluation_buttonbox button button--primary button--icon " icon="external" route="registrations" right-icon>
                            <h4 class="semibold"><?= i::__("Lista de inscrições") ?></h4>
                        </mc-link>
                    </div>
                    <div>
                        <button class="button button--primary" @click="sync()"><mc-icon name="sync" ></mc-icon></button>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-6 opportunity-phase-list-evaluation_action--center">
           <div class="col-6 opportunity-phase-list-evaluation_action__box">
                <div class="opportunity-phase-list-evaluation__status col-6">
                        <h4 class="bold"><?php i::_e("Resumo das avaliações") ?></h4>
                        <p v-for="(value, label) in entity.summary.evaluations"><?= i::__("Quantidade de inscrições") ?> <strong>{{label.toLowerCase()}}</strong>: <strong>{{value}}</strong> <?php i::_e('inscrições') ?></p>
                </div>
                <div class="col-6 opportunity-phase-list-evaluation__cardfooter">
                    <mc-link route="opportunity/allEvaluations" :params="[entity.opportunity.id, 'all']" class="opportunity-phase-list-evaluation_buttonbox button button--primary button--icon " icon="external" right-icon>
                    <h4 class="semibold"><?= i::__("Lista de avaliações") ?></h4>
                    </mc-link>
                </div>    
            </div>
        </div>
        <div class="opportunity-phase-list-evaluation__line col-12"></div>
        <opportunity-phase-publish-config-registration :phase="entity.opportunity" :phases="phases" hide-datepicker></opportunity-phase-publish-config-registration>
    </div>
</mc-card>