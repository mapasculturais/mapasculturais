<?php

return [
    'plugins' => [
        "RegistrationPayments" => [
            "namespace" => "RegistrationPayments",
            "config" => [
                "opportunitys_cnab_active" => [5],
                'cnab240_company_data' => [
                    'nome_empresa' => 'SECRETARIA DE CULTURA PE',
                    'tipo_inscricao' => '2',
                    'numero_inscricao' => '13.270.478/0001-83',
                    'agencia' => '697',
                    'agencia_dv' => '1',
                    'conta' => '79849',
                    'conta_dv' => '5',
                    'numero_sequencial_arquivo' => 1,
                    'convenio' => '000264470',
                    'carteira' => '',
                    'situacao_arquivo' => " ",
                    'uso_bb1' => '000264470',
                    'operacao' => 'C',
                ],
                "opportunitysCnab" => [ // Configurações de oportunidades
                    "5" => [
                        "canab_bb_default_value" => 1, // Define qual valor padão representa o Banco do Brasil
                        "settings" => [ // Configurações padrões
                            "social_type" => [ // Tipo de proponente (Pessoa Fisica ou Pessoa Jurídica) Pessoa Fisica = 1 Pessoa Jurídica = 2
                                "Pessoa física" => "1", // Não utilizado neste edital, por isso id repetido
                                "Pessoa juridica" => "2",
                            ],
                            "release_type" => [
                                1 => "01", // Corrente BB
                                2 => "05", // Poupança BB
                                3 => "03", // Outros bancos
                            ],
                        ],
                        "social_type" => 1, // ID campo que define o tipo de ptoponente, (Pessoa Fisica ou Pessoa Jurídica)
                        "proponent_name" => [ // Chave 1 Pessoa física Chave 2 Pessoa Jurídica
                            "dependence" => "social_type",
                            1 => 5, // Não utilizado neste edital, por isso id repetido
                            2 => 3,
                        ],
                        "proponent_document" => [ // Chave 1 Pessoa física Chave 2 Pessoa Jurídica
                            "dependence" => "social_type",
                            1 => 4, // Não utilizado neste edital, por isso id repetido
                            2 => 2,
                        ],
                        "account_type" => 6, // ID campo que define o tipo de conta bancária do proponente
                        "bank" => 11, // ID campo que define a o banco do proponente
                        "branch" => 7, // ID campo que define a agência bancária do proponente
                        "branch_dv" => 10, // ID campo que define o DV da agência bancária do proponente
                        "account" => 9, // ID campo que define a conta bancária do proponente
                        "account_dv" => 8, // ID campo que define o DV da conta bancária do proponente
                    ],

                ],
            ]
        ]
    ]
];
