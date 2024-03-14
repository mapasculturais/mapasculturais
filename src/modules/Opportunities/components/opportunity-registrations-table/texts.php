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

    'por status descendente' => i::__('por status descendente'),
    'por status ascendente' => i::__('por status ascendente'),
    'por resultado das avaliações' => i::__('por resultado das avaliações'),
    'por resultado das avaliações CONSIDERANDO COTAS' => i::__('por resultado das avaliações CONSIDERANDO COTAS'),
    'mais antigas primeiro' => i::__('mais antigas primeiro'),
    'mais recentes primeiro' => i::__('mais recentes primeiro'),
    'enviadas a mais tempo primeiro' => i::__('enviadas a mais tempo primeiro'),
    'enviadas a menos tempo primeiro' => i::__('enviadas a menos tempo primeiro'),
    'concorrendo por cota' => i::__('Concorrendo por cota'),
    'inscrição' => i::__('Inscrição'),
    'agente' => i::__('Responsável pela inscrição'), 
    'anexos' => i::__('Anexos'), 
    'status' => i::__('Status'), 
];