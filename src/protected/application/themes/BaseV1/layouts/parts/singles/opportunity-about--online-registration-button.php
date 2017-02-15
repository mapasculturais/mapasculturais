<?php if ($entity->isRegistrationOpen() && !$this->isEditable() ) : ?>
    <a class="btn btn-primary" href="#tab=inscricoes" onclick="$('#tab-inscricoes').click()"><?php \MapasCulturais\i::_e("Inscrições online");?></a>
<?php endif; ?>