<?php
use MapasCulturais\i;
use MapasCulturais\Entities\Registration;

return [
    Registration::STATUS_DRAFT => i::__('Rascunho'),
    Registration::STATUS_SENT => i::__('Pendente'),
    Registration::STATUS_INVALID => i::__('Inválida'),
    Registration::STATUS_NOTAPPROVED => i::__('Não selecionada'),
    Registration::STATUS_WAITLIST => i::__('Suplente'),
    Registration::STATUS_APPROVED => i::__('Selecionada'),
];