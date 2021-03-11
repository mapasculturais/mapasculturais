<?php
namespace RegistrationPayments;

use MapasCulturais\i;
$dataOportunity = $opportunity->getEvaluationCommittee();
?>
<?php $this->applyTemplateHook('opportunity-reports', 'before'); ?>
<div ng-controller='Reports'>
<?php $this->applyTemplateHook('opportunity-reports', 'begin'); ?>
    <div class="aba-content" id="reports">
        <header>
            <p><?php i::_e("Veja abaixo os gráficos referentes a essa oportunidade");?></p>
        </header>

        <div class="charts-static">
            <?php if (isset($registrationsByTime)) {?>
                <?php $this->part('registrationsByTime', ['data' => $registrationsByTime, 'color' => $color, 'opportunity' => $opportunity]);?>
            <?php } ?>

            <?php if (isset($registrationsByStatus)) {?>
                <?php $this->part('registrationsDraftVsSent', ['data' => $registrationsByStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
            <?php } ?>

            <?php if (isset($registrationsByStatus)) {?>
                <?php $this->part('registrationsStatus', ['data' => $registrationsByStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
            <?php } ?>

            <?php if ($dataOportunity[0]->owner->type == 'technical') { ?>
                <?php if (isset($registrationsByEvaluation)) {?>
                    <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation, 'color' => $color, 'opportunity' => $opportunity]);?>
                <?php } ?>

                <?php if (isset($registrationsByEvaluationStatus)) {?>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
                <?php } ?>
            <?php } else {?>
                <?php if (isset($registrationsByEvaluation)) {?>
                    <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation, 'color' => $color, 'opportunity' => $opportunity]);?>
                <?php } ?>

                <?php if (isset($registrationsByEvaluationStatus)) {?>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
                <?php } ?>
            <?php }?>

            <?php if (isset($registrationsByCategory)) {?>
                <?php $this->part('registrationsByCategory', ['data' => $registrationsByCategory, 'color' => $color, 'opportunity' => $opportunity]);?>
            <?php } ?>
        </div><!-- /.charts-static -->

        <?php $this->applyTemplateHook('reports-footer', 'before'); ?>

        <footer>
            <button ng-click="data.reportModal=true;data.graficType=true" class="btn btn-default add" id="btnOpenModal"><?php i::_e('Criar novo gráfico');?></button>
        </footer>

        <?php $this->applyTemplateHook('reports-footer', 'and'); ?>
        <?php $this->applyTemplateHook('opportunity-reports', 'end'); ?>
    </div>

</div>
<?php $this->applyTemplateHook('opportunity-reports', 'after'); ?>