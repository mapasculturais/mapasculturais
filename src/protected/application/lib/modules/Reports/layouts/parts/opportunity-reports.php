<?php
namespace RegistrationPayments;

use MapasCulturais\i;

?>
<div class="aba-content" id="reports">
    <header>
        <p><?php i::_e("Veja abaixo os gráficos referentes a essa oportinidade");?></p>
    </header>

    <div class="charts-static">
        <div class="line">
            <div class="line-one ">
                <?php if(isset($registrationsByTime)){?>
                    <?php $this->part('registrationsByTime', ['data' => $registrationsByTime, 'color' => $color]);?>
                <?php } ?>
            </div>
        </div>

        <div class="pie">
            <div class="line-one">
                <div>
                <?php if(isset($registrationsByStatus)){?>
                    <?php $this->part('registrationsDraftVsSent', ['data' => $registrationsByStatus, 'color' => $color]);?>
                <?php } ?>
                </div>

                <div>
                <?php if(isset($registrationsByStatus)){?>
                    <?php $this->part('registrationsStatus', ['data' => $registrationsByStatus, 'color' => $color]);?>
                <?php } ?>
                </div>
            </div>

            <div class="line-two">
                <div>
                <?php if(isset($registrationsByEvaluation)){?>
                    <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation, 'color' => $color]);?>
                <?php } ?>
                </div>

                <div>
                <?php if(isset($registrationsByEvaluationStatus)){?>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus, 'color' => $color]);?>
                <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <footer>
        <button class="btn btn-default add"><?php i::_e('Criar novo gráfico'); ?></button>
    </footer>
</div>
