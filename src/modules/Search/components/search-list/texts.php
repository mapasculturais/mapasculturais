<?php
use MapasCulturais\i;
use PhpOffice\Common\Text;

return [    
    'text' => i::__('Este agente atua de forma '),
    'label' => i::__('TIPO: '),
    'agente' => $this->text('agents', i::__('Agentes')),
    'espaço' => $this->text('spaces', i::__('Espaços')),
    'evento' => $this->text('events', i::__('Eventos')),
    'opportunidade' => $this->text('opportunities', i::__('Oportunidades')),
    'projeto' => $this->text('projects', i::__('Projetos')),
];