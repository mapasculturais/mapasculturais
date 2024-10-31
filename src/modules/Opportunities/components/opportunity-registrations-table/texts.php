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

    'classificação final' => i::__('classificação final'),
    'status descendente' => i::__('status descendente'),
    'status ascendente' => i::__('status ascendente'),
    'resultado das avaliações' => i::__('resultado das avaliações'),
    'mais antigas primeiro' => i::__('mais antigas primeiro'),
    'mais recentes primeiro' => i::__('mais recentes primeiro'),
    'enviadas a mais tempo primeiro' => i::__('enviadas a mais tempo primeiro'),
    'enviadas a menos tempo primeiro' => i::__('enviadas a menos tempo primeiro'),
    'concorrendo por cota' => i::__('Concorrendo por cota'),
    'inscrição' => i::__('Inscrição'),
    'agente' => i::__('Responsável pela inscrição'), 
    'anexos' => i::__('Anexos'), 
    'status' => i::__('Status'), 
    'data de envio' => i::__('Data de envio'),
    'data de criação' => i::__('Data de criação'),
    'Editavel para o proponente' => i::__('Editável para o proponente'),
    'resultado final' => i::__('Resultado final'),
    'Cotas aplicadas' => i::__('Cotas aplicadas'),
    'Critérios de desempate' => i::__('Critérios de desempate utilizados'),
    'Região' => i::__('Região'),
    'status alterado com sucesso' => i::__('Status alterado com sucesso'), 
    'First status change should be pending' => i::__('A inscrição está atualmente como rascunho. Para alterar o status, por favor, defina-o como Pendente primeiro'), 
    'Invalid status name' => i::__('Não foi possível alterar o status, fale com admnistrador'), 
    'pontuação final' => i::__('pontuação final'),
    'aguardando desempate' => i::__('Aguardando desempate'),
];