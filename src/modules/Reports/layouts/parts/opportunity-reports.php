<?php
namespace RegistrationPayments;

use MapasCulturais\i;
$prinUrl = $app->createUrl('reports', 'printReports', array('opportunity_id' => $opportunity->id, 'status' => $statusRegistration ))?>

<?php $this->applyTemplateHook('opportunity-reports', 'before'); ?>
<div ng-controller='Reports'>
<?php $this->applyTemplateHook('opportunity-reports', 'begin'); ?>
    <div class="aba-content" id="reports">
        <header>
            <p><?php i::_e("Veja abaixo os gráficos referentes a essa oportunidade");?></p>
            <?php if (!isset($hidePrintButton)): ?>
                <a href="<?=$prinUrl?>" class="btn btn-default hltip print-reports" title="" hltitle="Baixar em CSV" target="_blank"><i class="fas fa-print"></i> <?php i::_e("Imprimir");?></a>
            <?php endif;?>
        </header>

        <div class="charts-static">
            <?php if (isset($registrationsByTime)) {?>
                <?php $this->part('registrationsByTime', ['data' => $registrationsByTime, 'opportunity' => $opportunity, 'self' => $self]);?>
            <?php } ?>

            <?php if (isset($registrationsByStatus)) {?>
                <?php $this->part('registrationsDraftVsSent', ['data' => $registrationsByStatus, 'opportunity' => $opportunity, 'self' => $self]);?>
                <?php $this->part('registrationsStatus', ['data' => $registrationsByStatus, 'opportunity' => $opportunity, 'self' => $self]);?>
            <?php } ?>

            <?php if ($opportunity->evaluationMethod && $opportunity->evaluationMethod->slug == 'technical') { ?>
                <?php if (isset($registrationsByEvaluation)) {?>
                    <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation, 'opportunity' => $opportunity, 'self' => $self, 'statusRegistration' => $statusRegistration]);?>
                <?php } ?>

                <?php if (isset($registrationsByEvaluationStatus)) {?>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus, 'opportunity' => $opportunity, 'self' => $self]);?>
                <?php } ?>
            <?php } else {?>
                <?php if (isset($registrationsByEvaluation)) {?>
                    <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation, 'opportunity' => $opportunity, 'self' => $self, 'statusRegistration' => $statusRegistration]);?>
                <?php } ?>

                <?php if (isset($registrationsByEvaluationStatus)) {?>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus, 'opportunity' => $opportunity, 'self' => $self]);?>
                <?php } ?>
            <?php }?>

            <?php if (isset($registrationsByCategory)) {?>
                <?php $this->part('registrationsByCategory', ['data' => $registrationsByCategory, 'opportunity' => $opportunity, 'self' => $self]);?>
            <?php } ?>
        </div><!-- /.charts-static -->

        <?php $this->applyTemplateHook('reports-footer', 'before'); ?>

        <footer>
            <button ng-click="data.reportModal=true;data.graficType=true;data.error=false" class="btn btn-default add" id="btnOpenModal"><?php i::_e('Criar novo gráfico');?></button>
        </footer>

        <?php $this->applyTemplateHook('reports-footer', 'end'); ?>
        <?php $this->applyTemplateHook('opportunity-reports', 'end'); ?>
    </div>

</div>
<?php $this->applyTemplateHook('opportunity-reports', 'after'); ?>