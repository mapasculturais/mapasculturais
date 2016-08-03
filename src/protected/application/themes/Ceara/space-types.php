<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'emailPublico' => array(
            'label' => 'Email Público',
            'validations' => array(
                'v::email()' => 'O email público não é um email válido.'
            )
        ),

        'emailPrivado' => array(
            'label' => 'Email Privado',
            'validations' => array(
                'v::email()' => 'O email privado não é um email válido.'
            )
        ),

        'telefonePublico' => array(
            'label' => 'Telefone Público',
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe o telefone público no formato (xx) xxxx-xxxx.'
            )
        ),

        'telefone1' => array(
            'label' => 'Telefone 1',
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe o telefone 1 no formato (xx) xxxx-xxxx.'
            )
        ),


        'telefone2' => array(
            'label' => 'Telefone 2',
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe o telefone 2 no formato (xx) xxxx-xxxx.'
            )
        ),

        /*
        'virtual_fisico' => array(
            'label' => 'Virtual ou físico',
            'type' => 'select',
            'options' => array(
                '' => 'Físico',
                'virtual' => 'Virtual'
            )
        ),
        */
        'acessibilidade' => array(
            'label' => 'Acessibilidade',
            'type' => 'select',
            'options' => array(
                '' => 'Não Informado',
                'Sim' => 'Sim',
                'Não' => 'Não'
            )
        ),
        'acessibilidade_fisica' => array(
            'label' => 'Acessibilidade física',
            'type' => 'multiselect',
            'allowOther' => true,
            'allowOtherText' => 'Outros',
            'options' => array(
                'Banheiros adaptados',
                'Rampa de acesso',
                'Elevador',
                'Sinalização tátil',
                
                // vindos do sistema de museus.cultura.gov.br
                'Bebedouro adaptado',
                'Cadeira de rodas para uso do visitante',
                'Circuito de visitação adaptado',
                'Corrimão nas escadas e rampas',
                'Elevador adaptado',
                'Rampa de acesso',
                'Sanitário adaptado',
                'Telefone público adaptado',
                'Vaga de estacionamento exclusiva para deficientes',
                'Vaga de estacionamento exclusiva para idosos'
            )
        ),
        'capacidade' => array(
            'label' => 'Capacidade',
            'validations' => array(
                "v::intVal()->positive()" => "A capacidade deve ser um número positivo."
            )
        ),

        'endereco' => array(
            'label' => 'Endereço',
            'type' => 'text'
        ),
        

        'En_CEP' => [
            'label' => 'CEP',
        ],
        'En_Nome_Logradouro' => [
            'label' => 'Logradouro',
        ],
        'En_Num' => [
            'label' => 'Número',
        ],
        'En_Complemento' => [
            'label' => 'Complemento',
        ],
        'En_Bairro' => [
            'label' => 'Bairro',
        ],
        'En_Municipio' => [
            'label' => 'Município',
        ],
        'En_Estado' => [
            'label' => 'Estado',
            'type' => 'select',
            'options' => array(
                'AC'=>'Acre',
                'AL'=>'Alagoas',
                'AP'=>'Amapá',
                'AM'=>'Amazonas',
                'BA'=>'Bahia',
                'CE'=>'Ceará',
                'DF'=>'Distrito Federal',
                'ES'=>'Espírito Santo',
                'GO'=>'Goiás',
                'MA'=>'Maranhão',
                'MT'=>'Mato Grosso',
                'MS'=>'Mato Grosso do Sul',
                'MG'=>'Minas Gerais',
                'PA'=>'Pará',
                'PB'=>'Paraíba',
                'PR'=>'Paraná',
                'PE'=>'Pernambuco',
                'PI'=>'Piauí',
                'RJ'=>'Rio de Janeiro',
                'RN'=>'Rio Grande do Norte',
                'RS'=>'Rio Grande do Sul',
                'RO'=>'Rondônia',
                'RR'=>'Roraima',
                'SC'=>'Santa Catarina',
                'SP'=>'São Paulo',
                'SE'=>'Sergipe',
                'TO'=>'Tocantins',
            )
        ],

        'horario' => array(
            'label' => 'Horário de funcionamento',
            'type' => 'text'
        ),

        'criterios' => array(
            'label' => 'Critérios de uso do espaço',
            'type' => 'text'
        ),

        'site' => array(
            'label' => 'Site',
            'validations' => array(
                "v::url()" => "A url informada é inválida."
            )
        ),
        'facebook' => array(
            'label' => 'Facebook',
            'validations' => array(
                "v::url('facebook.com')" => "A url informada é inválida."
            )
        ),
        'twitter' => array(
            'label' => 'Twitter',
            'validations' => array(
                "v::url('twitter.com')" => "A url informada é inválida."
            )
        ),
        'googleplus' => array(
            'label' => 'Google+',
            'validations' => array(
                "v::url('plus.google.com')" => "A url informada é inválida."
            )
        )
    ),

/**
 * Equipamentos Culturais
 */

    'items' => array(
        'Espaços de Exibição de Filmes' => array(
            'range' => array(10,19),
            'items' => array(
                10 => array( 'name' => 'Cine itinerante' ),
                11 => array( 'name' => 'Cineclube' ),
                12 => array( 'name' => 'Drive-in' ),
                13 => array( 'name' => 'Espaço Público Para Projeção de Filmes' ),
                14 => array( 'name' => 'Sala de cinema' ),
            )
        ),

        'Bibliotecas' => array(
            'range' => array(20,29),
            'items' => array(
                20 => array( 'name' => 'Biblioteca Pública' ),
                21 => array( 'name' => 'Biblioteca Privada' ),
                22 => array( 'name' => 'Biblioteca Comunitária (incluí­dos os pontos de leitura)'),
                23 => array( 'name' => 'Biblioteca Escolar'),
                24 => array( 'name' => 'Biblioteca Nacional'),
                25 => array( 'name' => 'Biblioteca Universitária'),
                26 => array( 'name' => 'Biblioteca Especializada')
            )
        ),

        'Teatros' => array(
            'range' => array(30,39),
            'items' => array(
                30 => array('name' => 'Teatro Público'),
                31 => array('name' => 'Teatro Privado')
            )
        ),

        'Circos' => array(
            'range' => array(90,99),
            'items' => array(
                90 => array( 'name' => 'Circo Itinerante' ),
                91 => array( 'name' => 'Circo Fixo' ),
                92 => array( 'name' => 'Terreno para Circo' ),
                93 => array( 'name' => 'Circo Tradicional' ),
                94 => array( 'name' => 'Circo Moderno' ),
            )
        ),

       'Centros Culturais' => array(
            'range' => array(40,49),
            'items' => array(
                40 => array( 'name' => 'Centro Cultural Público' ),
                41 => array( 'name' => 'Centro Cultural Privado' ),
            )
        ),

       'Arquivos' => array(
            'range' => array(50,59),
            'items' => array(
                50 => array( 'name' => 'Arquivo Público' ),
                51 => array( 'name' => 'Arquivo Privado' ),
            )
        ),

       'Museus' => array(
            'range' => array(60,69),
            'items' => array(
                60 => array( 'name' => 'Museu Público' ),
                61 => array( 'name' => 'Museu Privado' ),
            )
        ),

        'Demais Equipamentos Culturais' => array(
            'range' => array(100,199),
            'items' => array(
                100 => array( 'name' => 'Galeria de arte' ),
                101 => array( 'name' => 'Livraria' ),
                102 => array( 'name' => 'Sebo' ),
                103 => array( 'name' => 'Videolocadora' ),
                104 => array( 'name' => 'Lan-house' ),
                105 => array( 'name' => 'Antiquário' ),
                106 => array( 'name' => 'Ateliê' ),
                107 => array( 'name' => 'Centro de artesanato' ),
                108 => array( 'name' => 'Casa de espetáculo' ),
                109 => array( 'name' => 'Danceteria' ),
                110 => array( 'name' => 'Estúdio' ),
                111 => array( 'name' => 'Concha acústica' ),
                112 => array( 'name' => 'Banca de jornal' ),
                113 => array( 'name' => 'Espaço para Eventos' ),
                114 => array( 'name' => 'Creative Bureau' ),
                115 => array( 'name' => 'Ponto de Leitura Afro' ),
                116 => array( 'name' => 'Centro Comunitário' ),
                117 => array( 'name' => 'Casa do Patrimônio' ),
                125 => array( 'name' => 'Ponto de Cultura' ),
                118 => array( 'name' => 'Usina Cultural' ),
                119 => array( 'name' => 'Praça dos esportes e da cultura' ),
                120 => array( 'name' => 'Espaço Mais Cultura' ),
                121 => array( 'name' => 'Sala de dança' ),
                122 => array( 'name' => 'Gafieira' ),
                123 => array( 'name' => 'Espaço para apresentação de dança' ),
                126 => array( 'name' => 'Centro cultural itinerante' ),
                127 => array( 'name' => 'Trio elétrico' ),
                128 => array( 'name' => 'Clube social' ),
                129 => array( 'name' => 'Centro de tradições' ),
                130 => array( 'name' => 'Sala Multiuso' ),
                124 => array( 'name' => 'Rádio Comunitária' ),
                131 => array( 'name' => 'Audioteca' ),
                132 => array( 'name' => 'Centro de Artes e Esportes Unificados - CEUs' ),
                133 => array( 'name' => 'Coreto' ),
                134 => array( 'name' => 'Ginásio Poliesportivo' ),
                135 => array( 'name' => 'Sala de Leitura' ),
                
                199 => array( 'name' => 'Outros Equipamentos Culturais' ), // adicionado na importação dos dados do Ceará para receber as endidades do tipo "equipamento"
            )
        ),

       'Centros de Documentação' => array(
            'range' => array(70,79),
            'items' => array(
                70 => array( 'name' => 'Centro de Documentação Público' ),
                71 => array( 'name' => 'Centro de Documentação Privado' ),
            )
        ),

       'Espaços Religiosos' => array(
            'range' => array(80,89),
            'items' => array(
                80 => array( 'name' => 'Templo' ),
                81 => array( 'name' => 'Terreiro' ),
                82 => array( 'name' => 'Mesquitas' ),
                83 => array( 'name' => 'Sinagoga' ),
                84 => array( 'name' => 'Igreja' ),
                85 => array( 'name' => 'Centro Espírita' ),
              )
        ),

/**
 * Espaços de Formação Cultural
 */

       'Instituições Públicas de Ensino Regular' => array(
            'range' => array(300,399),
            'items' => array(
                300 => array( 'name' => 'Instituição Pública de Ensino Regular Federal' ),
                301 => array( 'name' => 'Instituição Pública de Ensino Regular Estadual' ),
                302 => array( 'name' => 'Instituição Pública de Ensino Regular Municipal' ),
                303 => array( 'name' => 'Instituição Pública de Ensino Regular Distrital' ),
              )
        ),

       ' Instituições Privadas de Ensino Regular' => array(
            'range' => array(400,499),
            'items' => array(
                400 => array( 'name' => 'Instituição Privada Particular' ),
                401 => array( 'name' => 'Instituição Privada Comunitária' ),
                402 => array( 'name' => 'Instituição Privada Confessional' ),
                403 => array( 'name' => 'Instituição Privada Filantrópica' ),
              )
        ),

       'Instituições Públicas exclusivamente voltada para formação artistica e cultural' => array(
            'range' => array(601,699),
            'items' => array(
                601 => array( 'name' => 'Instituição Pública Federal exclusivamente voltada para formação artistica e cultural' ),
                602 => array( 'name' => 'Instituição Pública Estadual exclusivamente voltada para formação artistica e cultural' ),
                603 => array( 'name' => 'Instituição Pública Municipal exclusivamente voltada para formação artistica e cultural' ),
                604 => array( 'name' => 'Instituição Pública Distrital exclusivamente voltada para formação artistica e cultural' ),
              )
        ),

       'Instituições Privadas exclusivamente voltada para formação artistica e cultural' => array(
            'range' => array(700,799),
            'items' => array(
                700 => array( 'name' => 'Instituição Privada Particular exclusivamente voltada para formação artistica e cultural' ),
                701 => array( 'name' => 'Instituição Privada Comunitária exclusivamente voltada para formação artistica e cultural' ),
                702 => array( 'name' => 'Instituição Privada Confessional exclusivamente voltada para formação artistica e cultural' ),
                703 => array( 'name' => 'Instituição Privada Filantrópica exclusivamente voltada para formação artistica e cultural' ),
              )
        ),

       'Escolas livres' => array(
            'range' => array(800,899),
            'items' => array(
                800 => array( 'name' => 'Escola livre de Artes Cênicas' ),
                801 => array( 'name' => 'Escola livre de Artes Visuais' ),
                802 => array( 'name' => 'Escola livre de Audiovisual' ),
                803 => array( 'name' => 'Escola livre de Hip Hop' ),
                804 => array( 'name' => 'Escola livre de Cultura Digital' ),
                805 => array( 'name' => 'Escola livre de Música' ),
                806 => array( 'name' => 'Escola livre de Cultura Popular' ),
                807 => array( 'name' => 'Escola livre de Gestão Cultural' ),
                808 => array( 'name' => 'Escola livre de Pontinhos de cultura' ),
                809 => array( 'name' => 'Escola livre de Patrimônio' ),
                810 => array( 'name' => 'Escola livre de Design' ),
              )
        ),

/**
 * Patrimônios Culturais
 */

        'Bens culturais de natureza material' => array(
            'range' => array(200, 299),
            'items' => array(
                200 => array( 'name' => 'Bens culturais de natureza material' ),
                201 => array( 'name' => 'Bem Imóvel' ),
                202 => array( 'name' => 'Bem Arqueológico' ),
                203 => array( 'name' => 'Bem Paisagístico' ),
                204 => array( 'name' => 'Bem Móvel ou Integrado' ),
                205 => array( 'name' => 'Sitio Histórico' ),
                206 => array( 'name' => 'Documentação' ),
                207 => array( 'name' => 'Coleções' ),
                
                210 => array( 'name' => 'Bens culturais de natureza imaterial' ), // adicionado na importação dos dados do Ceará para receber as endidades do tipo "patrimonio-imaterial"
                
                299 => array( 'name' => 'Outros' ) // adicionado na importação dos dados do Ceará para receber as endidades do tipo "post"
            )
        ),

        'Temporário' => array(
            'range' => array(500,600),
            'items' => array(
                501 => array( 'name' => 'Palco de Rua' ),
            )
        ),
    )
    /* EXEMPLOS DE METADADOS:

    'cnpj' => array(
        'label' => 'CNPJ',
        'type' => 'text',
        'validations' => array(
            'unique' => 'Este CNPJ já está cadastrado em nosso sistema.',
            'v::cnpj()' => 'O CNPJ é inválido.'
        )
    ),
    'cpf' => array(
        'label' => 'CPF',
        'type' => 'text',
        'validations' => array(
            'required' => 'Por favor, informe o CPF.',
            'v::cpf()' => 'O CPF é inválido.'
        )
    ),
    'radio' => array(
        'label' => 'Um exemplo de input radio',
        'type' => 'radio',
        'options' => array(
            'valor1' => 'Label do valor 1',
            'valor2' => 'Label do valor 2',
        ),
        'default_value' => 'valor1'
    ),
    'checkboxes' => array(
        'label' => 'Um exemplo de grupo de checkboxes',
        'type' => 'checkboxes',
        'options' => array(
            'valor1' => 'Label do Primeiro checkbox',
            'valor2' => 'Label do Primeiro checkbox'
        ),
        'default_value' => array(),
        'validations' => array(
            'v::arrayType()->notEmpty()' => 'Você deve marcar ao menos uma opção.'
        )
    ),
    'checkbox' => array(
        'label' => 'Um exemplo de campo booleano com checkbox.',
        'type' => 'checkbox',
        'input_value' => 1,
        'default_value' => 0
    ),
    'email' => array(
        'label' => 'Email público para contato',
        'type' => 'text',
        'validations'=> array(
            'v::email()' => 'O email informado é inválido.'
        )
    ),
    'site' => array(
        'label' => 'Site',
        'type' => 'text',
        'validations'=> array(
            'v::url()' => 'A URL informada é inválida.'
        )
    ),
    'estado' => array(
        'label' => 'Estado de Residência',
        'type' => 'select',
        'options' => array(
            ''   => '',
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AM' => 'Amazonas',
            'AP' => 'Amapá',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MG' => 'Minas Gerais',
            'MS' => 'Mato Grosso do Sul',
            'MT' => 'Mato Grosso',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'PR' => 'Paraná',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'RS' => 'Rio Grande do Sul',
            'SC' => 'Santa Catarina',
            'SE' => 'Sergipe',
            'SP' => 'São Paulo',
            'TO' => 'Tocantins',
            ''   => '',
            'OUT'   => 'Resido Fora do Brasil'
        ),

        'validations' => array(
            "v::stringType()->in('AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO','OUT')" => 'O estado informado não existe.'
        )
    )
     */
);
