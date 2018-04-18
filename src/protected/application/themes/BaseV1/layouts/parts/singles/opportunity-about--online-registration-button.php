<?php if ($entity->isRegistrationOpen() && !$this->isEditable() ) : ?>
    <a class="btn btn-primary" href="#opportunity-registration" onclick="$('#tab-inscricoes').click()"><?php \MapasCulturais\i::_e("Inscrições online");?></a>
<?php endif; ?>