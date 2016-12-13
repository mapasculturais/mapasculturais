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
                "0" => "Abuso",
                "1" => "Racismo, xenofobia e intolerância sexual ou religiosa",
                "2" => "Pedofilia e pornografia infantil",
                "3" => "Exploração sexual",
                "4" => "Apologia e incitação ao crime",
                "5" => "Neonazismo",
                "6" => "Apologia e incitação a práticas cruéis contra animais",
                "7" => "Calúnia, difamação, injúria e crimes contra a honra",
                "8" => "Direitos autorais",
                "9" => "Falsa identidade",
                "10" => "Propaganda política",
                "11" => "Pornografia",
                "12" => "Outros"
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
