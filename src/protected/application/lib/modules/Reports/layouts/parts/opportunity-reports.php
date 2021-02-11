<?php
namespace RegistrationPayments;

use MapasCulturais\i;
$dataOportunity = $opportunity->getEvaluationCommittee();
?>
<div ng-controller='Reports'>
    <div class="aba-content" id="reports">
        <header>
            <p><?php i::_e("Veja abaixo os gráficos referentes a essa oportinidade");?></p>
        </header>

        <div class="charts-static">
            <div class="line">
                <div class="line-one ">
                    <?php if (isset($registrationsByTime)) {?>
                        <?php $this->part('registrationsByTime', ['data' => $registrationsByTime, 'color' => $color, 'opportunity' => $opportunity]);?>
                    <?php }?>
                </div>
            </div>

            <div class="pie">
                <div class="line-one">
                    <div>
                    <?php if (isset($registrationsByStatus)) {?>
                        <?php $this->part('registrationsDraftVsSent', ['data' => $registrationsByStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
                    <?php }?>
                    </div>

                    <div>
                    <?php if (isset($registrationsByStatus)) {?>
                        <?php $this->part('registrationsStatus', ['data' => $registrationsByStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
                    <?php }?>
                    </div>
                </div>

                <?php if ($dataOportunity[0]->owner->type == 'technical') {?>
                    <div class="line-two chart-full">
                        <div>
                            <?php if (isset($registrationsByEvaluation)) {?>
                                <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation, 'color' => $color, 'opportunity' => $opportunity]);?>
                            <?php }?>
                        </div>

                        <div>
                            <?php if (isset($registrationsByEvaluationStatus)) {?>
                                <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
                            <?php }?>
                        </div>
                    </div>
                <?php } else {?>
                    <div class="line-two chart-half">
                        <div>
                            <?php if (isset($registrationsByEvaluation)) {?>
                                <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation, 'color' => $color, 'opportunity' => $opportunity]);?>
                            <?php }?>
                        </div>

                        <div>
                            <?php if (isset($registrationsByEvaluationStatus)) {?>
                                <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus, 'color' => $color, 'opportunity' => $opportunity]);?>
                            <?php }?>
                        </div>
                    </div>
                <?php }?>


                <div class="line-three">
                    <div>
                    <?php if (isset($registrationsByCategory)) {?>
                        <?php $this->part('registrationsByCategory', ['data' => $registrationsByCategory, 'color' => $color, 'opportunity' => $opportunity]);?>
                    <?php }?>
                    </div>
                </div>
            </div>
        </div>

        <footer>
            <button class="btn btn-default add"><?php i::_e('Criar novo gráfico');?></button>
        </footer>
    </div>
</div>
