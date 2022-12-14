<?php

use MapasCulturais\i;

// $this->import('

// ');
?>
<!-- 1- Verificar se o usuario tem os termos
     2- Verificar se a hash do termo atualizado existe -->
<!-- 3 - Verificar Timestamp, ip. -->
<div class="user-accepted-terms__privacy">
    <div class="user-accepted-terms__privacy--accept">
        <label class="user-accepted-terms__privacy--accept-title"><?= i::__('Aceite de termos') ?></label>
        <div v-if="user" class="user-accepted-terms__privacy--accept-title-box">

            <div class="boxterm">
                <div v-for="(term, slug) in terms">
                    <!-- {{term}} -->
                    <!-- <div v-if="user.contains('lgpd_')"> -->
                    <!-- <div v-if="user.lgpd_+' '" class="vif"></div> -->

                    <div v-for="(value, key) in user" class="entrou">
                        <div v-if="key.includes('lgpd')">
                            <div v-for="privacy in key" class="teste">
                                <div v-for="item in value" class="iteration">
                                    <div v-for="dentro in value" class="fordedentro">
                                        <label v-if="dentro == value.md5">tabababbababba</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- </div> -->

                </div>
            </div>
        </div>
    </div>
</div>