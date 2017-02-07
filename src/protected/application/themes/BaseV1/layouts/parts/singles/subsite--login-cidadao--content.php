<?php
$entity = $this->controller->getRequestedEntity();
?>
<div id="login-cidadao" class="aba-content">
    <p class="alert info"> 
        <?php \MapasCulturais\i::_e('Use esta seção para configurar as chaves utilizada pelo subsite na autenticação com o Login Cidadão.'); ?> <br>
        <strong><?php \MapasCulturais\i::_e('A configuração será aplicada somente se ambos os campos abaixo forem preenchidos.'); ?></strong>
    </p>
    <section class="filter-section">

        <p>
            <label>Client ID</label>:
            <span class="js-editable" data-edit="login_cidaddao__id" data-original-title="Client ID" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Utilizando a configuração da instalação principal (clique para configurar)'); ?>"><?php echo $entity->login_cidaddao__id; ?></span>
        </p>

        <p>
            <label>Client Secret</label>:
            <span class="js-editable" data-edit="login_cidaddao__secret" data-original-title="Client Secret" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Utilizando a configuração da instalação principal (clique para configurar)'); ?>"><?php echo $entity->login_cidaddao__secret; ?></span>
        </p>

        <div class=" clear"></div>
    </section>
</div>
