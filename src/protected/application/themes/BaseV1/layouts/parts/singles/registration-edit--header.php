<h3 class="registration-header"><?php \MapasCulturais\i::_e("Formulário de Inscrição");?></h3>
<p class="registration-help"><?php \MapasCulturais\i::_e("Itens com asterisco são obrigatórios.");?></p>
<div class="registration-fieldset">
    <h4><?php \MapasCulturais\i::_e("Número da Inscrição");?></h4>
    <div class="registration-id">
        <?php if($action !== 'create'): ?><?php echo $entity->number ?><?php endif; ?>
    </div>
</div>