<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('
    mc-card
    password-strongness
');
?>

<div class="login">

    <!-- Login action -->

    <div v-if="!recoveryRequest && !recoveryMode" class="login__action">
        <div class="login__card">
            <div class="login__card__header">
                <h3> <?= $this->text('welcome', i::__('Boas vindas!')) ?> </h3>
                <h6> <?= sprintf($this->text('greeting', i::__('Entre na sua conta do %s')), $app->siteName) ?> </h6>
            </div>

            <div class="login__card__content">
                <form class="login__form" @submit.prevent="doLogin();">
                    <div class="login__fields">
                        <div class="field">
                            <label for="email"> <?= i::__('E-mail ou CPF') ?> </label>
                            <input type="text" name="email" id="email" v-model="email" autocomplete="off" />
                        </div>

                        <div class="field password">
                            <label for="password"> <?= i::__('Senha') ?> </label>
                            <input type="password" name="password" id="password" v-model="password" autocomplete="off" />
                            <a id="multiple-login-recover" class="login__recover-link" @click="recoveryRequest = true"> <?= i::__('Esqueci minha senha') ?> </a>
                            <div class="seePassword" @click="togglePassword('password', $event)"></div>
                        </div> 
                    </div>                     

                    <VueRecaptcha v-if="configs['google-recaptcha-sitekey']" :sitekey="configs['google-recaptcha-sitekey']" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha"></VueRecaptcha>
                    
                    <div class="login__buttons">
                        <button class=" button button--primary button--large button--md" type="submit"> <?= i::__('Entrar') ?> </button>

                        <div v-if="configs.strategies.Google?.visible || configs.strategies.govbr?.visible" class="divider"> 
                            <span class="divider__text"> <?= i::__('Ou entre com') ?> </span>
                        </div>

                        <div class="login__social-buttons" :class="{'login__social-buttons--multiple': multiple}">
                            <a v-if="configs.strategies.govbr?.visible" class="social-login--button button button--icon button--large button--md govbr" href="<?php echo $app->createUrl('auth', 'govbr') ?>">                                
                                <div class="img"> <img height="16" class="br-sign-in-img" src="<?php $this->asset('img/govbr-white.png'); ?>" /> </div>                                
                                <?= i::__('Entrar com Gov.br') ?>                            
                            </a>

                            <a v-if="configs.strategies.Google?.visible" class="social-login--button button button--icon button--large button--md google" href="<?php echo $app->createUrl('auth', 'google') ?>">                                
                                <div class="img"> <img height="16" src="<?php $this->asset('img/g.png'); ?>" /> </div>                                
                                <?= i::__('Entrar com Google') ?>
                            </a>

                            <a v-if="configs.strategies.decidim?.visible" class="social-login--button button button--icon button--large button--md govbr" href="<?php echo $app->createUrl('auth', 'decidim') ?>">                                
                                <span v-if="configs.strategies.decidim?.button_text">{{configs.strategies.decidim.button_text}}</span>
                                <span v-else><?= i::__('Entrar com ID Cacicadas') ?></span>                            
                            </a>
                        </div>
                    </div>

                    <div class="create ">
                        <h5 class="bold"> <?= sprintf($this->text('register', i::__('Ainda não tem cadastro no %s? Realize seu cadastro agora!')), $app->siteName) ?> </h5>

                        <a class=" button button--primary button--large button--md" href="<?php echo $app->createUrl('auth', 'register') ?>"> 
                            <?= $this->text('fazer-cadastro', i::__('Fazer cadastro')) ?>
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Recovery request -->
    <div v-if="recoveryRequest" class="login__recovery--request">
        <div class="login__card" v-if="!recoveryEmailSent">
            <div class="login__card__header">
                <h3> <?= i::__('Alteração de senha') ?> </h3>
                <h6> <?= i::__('Se você esqueceu a senha, não se preocupe, todo mundo passa por isso.') ?> <br> <?= i::__('Digite seu e-mail para criar uma nova.') ?> </h6>
            </div>

            <div class="login__card__content">
                <form class="grid-12" @submit.prevent="requestRecover();">
                    <div class="field col-12">
                        <label for="email"> <?= i::__('E-mail') ?> </label>
                        <input type="email" name="email" id="email" v-model="email" autocomplete="off" />
                    </div>
                    <VueRecaptcha v-if="configs['google-recaptcha-sitekey']" :sitekey="configs['google-recaptcha-sitekey']" @verify="verifyCaptcha" @expired="expiredCaptcha" @render="expiredCaptcha" class="g-recaptcha col-12"></VueRecaptcha>
                    <button class="col-12 button button--primary button--large button--md" type="submit"> <?= i::__('Alterar senha') ?> </button>
                    <a @click="recoveryRequest = false" class="col-12 button button--secondarylight button--large button--md"> <?= i::__('Voltar') ?> </a>
                </form>
            </div>
        </div>

        <div class="login__card" v-if="recoveryEmailSent">
            <div class="login__card__content">
                <div class="grid-12">
                    <div class="col-12 header">
                        <label class="header__title"> <?= i::__('Alteração de senha') ?> </label>
                        <mc-icon name="circle-checked" class="header__icon"></mc-icon>
                        <label class="header__label"> <?= i::__('Enviamos as instruções de alteração de senha para seu e-mail.') ?> </label>
                    </div>

                    <button class="col-12 button button--primary button--large button--md" type="submit"> <?= i::__('Não recebi o e-mail') ?> </button>
                    <a @click="recoveryEmailSent = false" class="col-12 button button--secondarylight button--large button--md"> <?= i::__('Voltar') ?> </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Recovery action -->
    <div v-if="recoveryMode" class="login__recovery--action">
        <div class="login__card">
            <div class="login__card__header">
                <h3> <?= i::__('Redefinir senha de acesso') ?> </h3>
            </div>

            <div class="login__card__content">
                <form class="grid-12" @submit.prevent="doRecover();">
                    <div class="field col-12 password">
                        <label for="pwd"> <?= i::__('Senha'); ?> </label>
                        <input autocomplete="off" id="pwd" type="password" name="password" v-model="password" />
                        
                    </div>

                    <div class="field col-12 password">
                        <label for="pwd"> <?= i::__('Confirme sua nova senha'); ?> </label>
                        <input autocomplete="off" id="pwd" type="password" name="confirmPassword" v-model="confirmPassword" />
                    </div>

                    <div class="col-12">
                        <password-strongness :password="password"></password-strongness>
                    </div>

                    <button class="col-12 button button--primary button--large button--md" type="submit"> <?= i::__('Redefinir senha') ?> </button>
                </form>
            </div>
        </div>
    </div>
</div>
