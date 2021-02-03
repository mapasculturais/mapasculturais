<?php
namespace RegistrationPayments;

use MapasCulturais\i;

?>
<div class="aba-content" id="reports">
    <div class="header-reports">   
        <span>
            <?php i::_e("Veja abaixo os gráficos referentes a essa oportinidade"); ?>  <br>
        </span>
        <div>
            <h5><?php i::_e("Total de registro ao longo do tempo"); ?></h5>
        </div>

        <div>
            <button  class="btn btn-default download"> <?php i::_e("Baixar gráfico em CSV"); ?></button>
        </div>
    </div>


    <div class="charts-static">
        <div class="line">
            <div class="line-one ">
                <?php $this->part('registrationsByTime', ['data' => $registrationByTime]); ?>
            </div>
        </div>
    
        <div class="pie">
            <div class="line-one">
                <div>
                    <?php $this->part('registrationsDraftVsSent', ['data' => $registrationsByStatus]); ?>
                </div>
                
                <div>
                    <?php $this->part('registrationsEvaluation', ['data' => $registrationsByEvaluation]); ?>
                </div>
            </div>

            <div class="line-two">
                <div>
                    <?php $this->part('registrationsStatus', ['data' => $registrationsByStatus]); ?>
                </div>
            </div>
            <div class="line-three">
                <div>
                    <?php $this->part('registrationsByEvaluationStatus', ['data' => $registrationsByEvaluationStatus]); ?>
                </div>
            </div>
        </div>
    </div>
</div>

