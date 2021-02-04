<?php
namespace RegistrationPayments;

use MapasCulturais\i;

?>
<div class="aba-content" id="reports">
    <header>   
        <p><?php i::_e("Veja abaixo os gráficos referentes a essa oportinidade"); ?></p>
    </header>

    <div class="charts-static">
        <div class="line">
            <div class="line-one ">
                <?php $this->part('registrationsByTime', ['data' => $registrationByTime]); ?>
            </div>
        </div>
    
        <div class="pie">
            <div class="line-one">
                <div>
                    <?php i::_e("Rascunhos X Enviadas"); ?> <br>
                    <?php $this->part('registrationsDraftVsSent', ['data' => $registrationsByStatus]); ?>
                </div>

                <div>
                    <?php i::_e("Inscrições por status"); ?> <br>
                    <?php $this->part('registrationsStatus', ['data' => $registrationsByStatus]); ?>
                </div>                
            </div>

            <div class="line-two">
                <div>
                    <?php i::_e("Inscrições por avaliações"); ?> <br>
                    <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation]); ?>
                </div>

                <div>
                    <?php i::_e("Inscrições por status da avaliação"); ?> <br>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus]); ?>
                </div>
            </div>
            <div class="line-three">
                
            </div>
        </div>
    </div>
</div>

