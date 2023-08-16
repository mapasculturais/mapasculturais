<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div class="registration-results">  
    <mc-modal title="<?php i::_e("Pareceres da inscrição") ?> 1020304050" classes="registration-results__modal">
        <template #default>

            <div class="registration-results__content">

                <div class="registration-results__card">
                    <div class="registration-results__card-header">
                        <div class="registration-results__card-title">
                            <h4 class="bold">
                                <?= i::__('Parecerista FUNALE DE TAL') ?>
                            </h4>

                            <button class="registration-results__card-action">
                                <?= i::__('Esconder') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                            </button>
                        </div>

                        <div class="registration-results__card-status">
                            <p class="semibold">
                                <?= i::__('Resultado da avaliação documental: ') ?> 
                            </p>

                            <div class="mc-status mc-status--selected">
                                <mc-icon name="dot"></mc-icon>
                                <h5 class="bold">Selecionado</h5>
                            </div>
                        </div>
                    </div>

                    <div class="registration-results__card-content">                    
                        <div class="registration-results__opinion registration-results__opinion--document">
                            <p class="registration-results__opinion-title bold">
                                Nome do campo do formulário que foi avaliado
                            </p>

                            <div class="registration-results__opinion-status">
                                <div class="mc-status mc-status--valid">
                                    <mc-icon name="dot"></mc-icon>
                                    <h5 class="bold">Válido</h5>
                                </div>
                            </div>

                            <div class="registration-results__opinion-text">
                                <h6> Lorem ipsum dolor sit amet consectetur. Enim nibh est nisi donec lectus proin. Purus vitae lectus lacus mauris vel consectetur. </h6>
                            </div>
                        </div>
                    </div>

                    <div class="registration-results__card-content">                    
                        <div class="registration-results__opinion registration-results__opinion--document">
                            <p class="registration-results__opinion-title bold">
                                Nome do campo do formulário que foi avaliado
                            </p>

                            <div class="registration-results__opinion-status">
                                <div class="mc-status mc-status--invalid">
                                    <mc-icon name="dot"></mc-icon>
                                    <h5 class="bold">Inválido</h5>
                                </div>
                            </div>

                            <div class="registration-results__opinion-text">
                                <h6> Lorem ipsum dolor sit amet consectetur. Enim nibh est nisi donec lectus proin. Purus vitae lectus lacus mauris vel consectetur. </h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="registration-results__card">
                    <div class="registration-results__card-header">
                        <div class="registration-results__card-title">
                            <h4 class="bold">
                                <?= i::__('Parecerista FUNALE DE TAL') ?>
                            </h4>

                            <button class="registration-results__card-action">
                                <?= i::__('Esconder') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                            </button>
                        </div>

                        <div class="registration-results__card-status">
                            <p class="semibold">
                                <?= i::__('Resultado da avaliação simplificada: ') ?> 
                            </p>

                            <div class="mc-status mc-status--selected">
                                <mc-icon name="dot"></mc-icon>
                                <h5 class="bold">Selecionado</h5>
                            </div>
                        </div>
                    </div>

                    <div class="registration-results__card-content">
                        <div class="registration-results__opinion registration-results__opinion--simplified">
                            <div class="registration-results__opinion-text">
                                <h6>
                                    Lorem ipsum dolor sit amet consectetur. Faucibus pharetra ac non sagittis. Vulputate rhoncus aenean leo maecenas in eleifend tristique at id. 
                                    Nisl dictumst et blandit tincidunt. Turpis urna sapien posuere magna aenean viverra amet magna egestas. Erat mauris sed imperdiet quam leo nulla facilisis. 
                                    Libero nibh tristique viverra proin quam tristique at pellentesque. Porta potenti diam.
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="registration-results__card">
                    <div class="registration-results__card-header">
                        <div class="registration-results__card-title">
                            <h4 class="bold">
                                <?= i::__('Parecerista FUNALE DE TAL') ?>
                            </h4>

                            <button class="registration-results__card-action">
                                <?= i::__('Esconder') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                            </button>
                        </div>
                    </div>

                    <div class="registration-results__card-content">
                        <div class="registration-results__opinion registration-results__opinion--technical">
                            <div class="registration-results__opinion-punctuation">

                            </div>

                            <div class="registration-results__opinion-text">
                                <h6>
                                    Lorem ipsum dolor sit amet consectetur. Faucibus pharetra ac non sagittis. Vulputate rhoncus aenean leo maecenas in eleifend tristique at id. 
                                    Nisl dictumst et blandit tincidunt. Turpis urna sapien posuere magna aenean viverra amet magna egestas. Erat mauris sed imperdiet quam leo nulla facilisis. 
                                    Libero nibh tristique viverra proin quam tristique at pellentesque. Porta potenti diam.
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="registration-results__card">
                    <div class="registration-results__card-header">
                        <div class="registration-results__card-title">
                            <h4 class="bold">
                                <?= i::__('Parecerista FUNALE DE TAL') ?>
                            </h4>

                            <button class="registration-results__card-action">
                                <?= i::__('Esconder') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                            </button>
                        </div>
                    </div>

                    <div class="registration-results__card-content">
                        <div class="registration-results__opinion registration-results__opinion--technical">
                            <div class="registration-results__opinion-punctuation">

                            </div>

                            <div class="registration-results__opinion-text">
                                <h6>
                                    Lorem ipsum dolor sit amet consectetur. Faucibus pharetra ac non sagittis. Vulputate rhoncus aenean leo maecenas in eleifend tristique at id. 
                                    Nisl dictumst et blandit tincidunt. Turpis urna sapien posuere magna aenean viverra amet magna egestas. Erat mauris sed imperdiet quam leo nulla facilisis. 
                                    Libero nibh tristique viverra proin quam tristique at pellentesque. Porta potenti diam.
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="registration-results__card">
                    <div class="registration-results__card-header">
                        <div class="registration-results__card-title">
                            <h4 class="bold">
                                <?= i::__('Parecerista FUNALE DE TAL') ?>
                            </h4>

                            <button class="registration-results__card-action">
                                <?= i::__('Esconder') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                            </button>
                        </div>
                    </div>

                    <div class="registration-results__card-content">
                        <div class="registration-results__opinion registration-results__opinion--technical">
                            <div class="registration-results__opinion-punctuation">

                            </div>

                            <div class="registration-results__opinion-text">
                                <h6>
                                    Lorem ipsum dolor sit amet consectetur. Faucibus pharetra ac non sagittis. Vulputate rhoncus aenean leo maecenas in eleifend tristique at id. 
                                    Nisl dictumst et blandit tincidunt. Turpis urna sapien posuere magna aenean viverra amet magna egestas. Erat mauris sed imperdiet quam leo nulla facilisis. 
                                    Libero nibh tristique viverra proin quam tristique at pellentesque. Porta potenti diam.
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="registration-results__card">
                    <div class="registration-results__card-header">
                        <div class="registration-results__card-title">
                            <h4 class="bold">
                                <?= i::__('Parecerista FUNALE DE TAL') ?>
                            </h4>

                            <button class="registration-results__card-action">
                                <?= i::__('Esconder') ?> <mc-icon name="arrowPoint-down"></mc-icon>
                            </button>
                        </div>
                    </div>

                    <div class="registration-results__card-content">
                        <div class="registration-results__opinion registration-results__opinion--technical">
                            <div class="registration-results__opinion-punctuation">

                            </div>

                            <div class="registration-results__opinion-text">
                                <h6>
                                    Lorem ipsum dolor sit amet consectetur. Faucibus pharetra ac non sagittis. Vulputate rhoncus aenean leo maecenas in eleifend tristique at id. 
                                    Nisl dictumst et blandit tincidunt. Turpis urna sapien posuere magna aenean viverra amet magna egestas. Erat mauris sed imperdiet quam leo nulla facilisis. 
                                    Libero nibh tristique viverra proin quam tristique at pellentesque. Porta potenti diam.
                                </h6>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </template>

        <template #button="modal">
            <button class="button button--primary button--sm button--large" @click="modal.open()"><?php i::_e('Exibir pareceres da fase') ?></button>
        </template>
    </mc-modal>
</div>