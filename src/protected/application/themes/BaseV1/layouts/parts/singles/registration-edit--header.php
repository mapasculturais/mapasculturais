<h3 class="registration-header">Formulário de Inscrição</h3>
<p class="registration-help">Itens com asterisco são obrigatórios.</p>
<div class="registration-fieldset">
    <h4>Número da Inscrição</h4>
    <div class="registration-id">
        <?php if($action !== 'create'): ?><?php echo $entity->number ?><?php endif; ?>
    </div>
</div>