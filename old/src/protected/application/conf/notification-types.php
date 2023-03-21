<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'type' => [
            'label' => 'Tipo',
            'type'  => 'select',
            'options' =>  array(
                '1' => \MapasCulturais\i::__('Denúncia'),
                '2' => \MapasCulturais\i::__('Contato')
            )
        ],
        'compliant_type' => [
            'label' => 'Tipo',
            'type' => 'select',
            'options' => array(
                "0" => \MapasCulturais\i::__("Conteúdo ofensivo ou inadequado"),
                "1" => \MapasCulturais\i::__("Conteúdo incorreto ou calunioso"),
                "2" => \MapasCulturais\i::__("Conteúdo que viola direitos"),
                "3" => \MapasCulturais\i::__("Outros")
            )
        ],

        'suggestion_type' => [
            'label' => 'Tipo',
            'type' => 'select',
            'options' => array(
                "0" => \MapasCulturais\i::__("Sugestão"),
                "1" => \MapasCulturais\i::__("Crítica"),
                "2" => \MapasCulturais\i::__("Comentários"),
                "3" => \MapasCulturais\i::__("Reclamações"),
                "4" => \MapasCulturais\i::__("Outros")
            )
        ]
    )
);
