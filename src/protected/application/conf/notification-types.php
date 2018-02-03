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
                '1' => 'Denúncia',
                '2' => 'Contato'
            )
        ],
        'compliant_type' => [
            'label' => 'Tipo',
            'type' => 'select',
            'options' => array(
                "0" => "Conteúdo ofensivo ou inadequado",
                "1" => "Conteúdo incorreto ou calunioso",
                "2" => "Conteúdo que viola direitos",
                "3" => "Outros"
            )
        ],

        'suggestion_type' => [
            'label' => 'Tipo',
            'type' => 'select',
            'options' => array(
                "0" => "Sugestão",
                "1" => "Crítica",
                "2" => "Comentários",
                "3" => "Reclamações",
                "4" => "Outros"
            )
        ]
    )
);
