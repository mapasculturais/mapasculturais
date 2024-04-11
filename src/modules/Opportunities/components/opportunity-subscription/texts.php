<?php
use MapasCulturais\i;

return [
    'selecione categoria' => $this->text('error-category', i::__('Por favor, selecione a categoria da inscrição')),
    'selecione tipo de proponente' => $this->text('error-proponent-type', i::__('Por favor, selecione o tipo de proponente da inscrição')),
    'selecione faixa' => $this->text('error-range', i::__('Por favor, selecione a faixa para inscrição')),
    'selecione agente' => i::__('Por favor, selecione o agente responsável pela inscrição'),
    'inscrições abertas' => i::__('Inscrições abertas de <strong>{startAt}</strong> a <strong>{endAt}</strong>  às <strong>{endHour}</strong>'),
    'inscrições irão abrir' => i::__('As inscrições ainda não estão abertas. O período de inscrições começará a partir do dia <strong>{startAt}</strong> às <strong>{startHour}</strong>'),
    'inscrições fechadas' => i::__('As inscrições estão <strong>encerradas</strong>'),
    'inscrições indefinidas' => i::__('O periodo de inscrição ainda não foi definido'),
    'resultado publicado' => i::__('Os resultados da oportunidade já foram publicados'),
    'limite de inscrições' => i::__('O limite de inscrições nessa oportunidade foi atingido.'),
    'limite de inscrições por usuário' => i::__('O limite de inscrições por usuário nessa oportunidade foi atingido.'),

];