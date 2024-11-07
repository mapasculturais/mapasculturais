<?php

use MapasCulturais\Utils;
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */

function compareNamesOpportunity ($item1, $item2) {
    return strcmp($item1['name'], $item2['name']);
}

$items = array(
    7 =>  array( 'name' => \MapasCulturais\i::__("Ciclo")),
    26 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Setorial")),
    27 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Nacional")),
    28 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Estadual")),
    29 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Municipal")),
    10 => array( 'name' => \MapasCulturais\i::__("Concurso")),
    6 =>  array( 'name' => \MapasCulturais\i::__("Convenção")),
    19 => array( 'name' => \MapasCulturais\i::__("Congresso")),
    23 => array( 'name' => \MapasCulturais\i::__("Curso")),
    9 =>  array( 'name' => \MapasCulturais\i::__("Edital")),
    11 => array( 'name' => \MapasCulturais\i::__("Exposição")),
    2 =>  array( 'name' => \MapasCulturais\i::__("Encontro")),
    13 => array( 'name' => \MapasCulturais\i::__("Exibição")),
    1 =>  array( 'name' => \MapasCulturais\i::__("Festival")),
    16 => array( 'name' => \MapasCulturais\i::__("Festa Popular")),
    17 => array( 'name' => \MapasCulturais\i::__("Festa Religiosa")),
    14 => array( 'name' => \MapasCulturais\i::__("Feira")),
    22 => array( 'name' => \MapasCulturais\i::__("Fórum")),
    15 => array( 'name' => \MapasCulturais\i::__("Intercâmbio Cultural")),
    25 => array( 'name' => \MapasCulturais\i::__("Jornada")),
    12 => array( 'name' => \MapasCulturais\i::__("Jornada")),
    5 =>  array( 'name' => \MapasCulturais\i::__("Mostra")),
    24 => array( 'name' => \MapasCulturais\i::__("Oficina")),
    20 => array( 'name' => \MapasCulturais\i::__("Palestra")),
    8 =>  array( 'name' => \MapasCulturais\i::__("Programa")),
    4 =>  array( 'name' => \MapasCulturais\i::__("Reunião")),
    3 =>  array( 'name' => \MapasCulturais\i::__("Sarau")),
    21 => array( 'name' => \MapasCulturais\i::__("Simpósio")),
    18 => array( 'name' => \MapasCulturais\i::__("Seminário")),
//        30 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Militar")),
//        31 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Cívico")),
//        32 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Festivo")),
//        33 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Político")),
//        34 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile de Ações Afirmativas")),

    // tipos não existentes em projetos
    40 => array( 'name' => \MapasCulturais\i::__("Abaixo-assinado")),
    41 => array( 'name' => \MapasCulturais\i::__("Campanhas")),
    42 => array( 'name' => \MapasCulturais\i::__("Pesquisa")),
    43 => array( 'name' => \MapasCulturais\i::__("Oportunidade de trabalho")),
    44 => array( 'name' => \MapasCulturais\i::__("Outros eventos")),
    45 => array( 'name' => \MapasCulturais\i::__("Outros tipos de inscrição")),
);

uasort($items, 'compareNamesOpportunity');

return array(
    'metadata' => array(

        'registrationCategTitle' => array(
            'label' => \MapasCulturais\i::__('Título das opções (ex: Categorias)'),
        ),

        'registrationCategDescription' => array(
            'label' => \MapasCulturais\i::__('Descrição das opções (ex: Selecione uma categoria)'),
        ),

        'registrationLimitPerOwner' => array(
            'type' => 'integer',
            'label' => \MapasCulturais\i::__('Limite de inscritos por agente'),
            // 'description' => \MapasCulturais\i::__('Defina o limite de inscritos por agente responsável pela avaliação.'),
            'validations' => array(
                "v::intVal()" => \MapasCulturais\i::__("O número máximo de inscrições por agente responsável deve ser um número inteiro")
            )
        ),

        'registrationLimit' => array(
            'type' => 'integer',
            'label' => \MapasCulturais\i::__('Limite de inscrições'),
            // 'description' => \MapasCulturais\i::__('Defina se haverá uma quantidade máxima de inscritos (0 = sem limites).'),
            'validations' => array(
                "v::intVal()" => \MapasCulturais\i::__("O número máximo de inscrições na oportunidade deve ser um número inteiro")
            )
        ),
        'useSpaceRelationIntituicao' => array(
            'label' => \MapasCulturais\i::__('Espaço Cultural'),
            'type' => 'select',
            'options' => (object) array(
                'dontUse' => \MapasCulturais\i::__('Não utilizar'),
                'required' => \MapasCulturais\i::__('Obrigatório'),
                'optional' => \MapasCulturais\i::__('Opcional')
            ),
        ),
        'site' => array(
            'label' => \MapasCulturais\i::__('Site'),
            'validations' => array(
                "v::url()" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),

        'facebook' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Facebook'),
            'serialize' =>function($value){
                return Utils::parseSocialMediaUser('facebook.com', $value);
            },
            'validations' => array(
                "v::oneOf(v::urlDomain('facebook.com'), v::regex('/^@?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL válida ou o nome ou id do usuário.")
            ),
            'placeholder' => "nomedousuario ou iddousuario",
            'available_for_opportunities' => true
        ),
        'twitter' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Twitter'),
            'serialize' =>function($value){
                return Utils::parseSocialMediaUser('x.com', $value);
            },
            'validations' => array(
                "v::oneOf(v::urlDomain('x.com'), v::regex('/^@?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'placeholder' => "nomedousuario",
            'available_for_opportunities' => true
        ),
        'instagram' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Instagram'),
            'available_for_opportunities' => true,
            'serialize' =>function($value){
                $result = Utils::parseSocialMediaUser('instagram.com', $value);
                if($result && $result[0] == '@'){
                    $result = substr($result,1);
                }
                return $result;
            },
            'validations' => array(
                "v::oneOf(v::urlDomain('instagram.com'), v::regex('/^@?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'placeholder' => "nomedousuario",
        ),
        'linkedin' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Linkedin'),
            'serialize' =>function($value){
                return Utils::parseSocialMediaUser('linkedin.com', $value, 'linkedin');
            },
            'validations' => array(
                "v::oneOf(v::urlDomain('linkedin.com'), v::regex('/^@?([\-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'placeholder' => "nomedousuario",
            'available_for_opportunities' => true
        ),
        'vimeo' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Vimeo'),
            'validations' => array(
                "v::oneOf(v::urlDomain('vimeo.com'), v::regex('/^@?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'serialize' =>function($value){
                return Utils::parseSocialMediaUser('vimeo.com', $value);
            },
            'placeholder' => "nomedousuario",
            'available_for_opportunities' => true
        ),
        'spotify' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Spotify'),
            'validations' => array(
                "v::oneOf(v::urlDomain('open.spotify.com'), v::regex('/^@?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'serialize' => function($value) {
                return Utils::parseSocialMediaUser('open.spotify.com', $value);
            },
            'placeholder' => "nomedousuario",
            'available_for_opportunities' => true
        ),
        'youtube' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('YouTube'),
            'validations' => array(
                "v::oneOf(v::urlDomain('youtube.com'), v::regex('/^(@|channel\/)?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'serialize' =>function($value){
                return Utils::parseSocialMediaUser('youtube.com', $value);
            },
            'placeholder' => "iddocanal",
            'available_for_opportunities' => true
        ),
        'pinterest' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Pinterest'),
            'validations' => array(
                "v::oneOf(v::urlDomain('pinterest.com'), v::regex('/^@?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'serialize' =>function($value){
                return Utils::parseSocialMediaUser('pinterest.com', $value);
            },
            'placeholder' => "nomedousuario",
            'available_for_opportunities' => true
        ),
        'tiktok' => array(
            'type' => "socialMedia",
            'label' => \MapasCulturais\i::__('Tiktok'),
            'serialize' =>function($value){
                return Utils::parseSocialMediaUser('tiktok.com', $value);
            },
            'validations' => array(
                "v::oneOf(v::urlDomain('tiktok.com'), v::regex('/^@?([-\w\d\.]+)$/i'))" => \MapasCulturais\i::__("O valor deve ser uma URL ou usuário válido.")
            ),
            'placeholder' => "nomedousuario",
            'available_for_opportunities' => true
        ),
        'registrationSeals' => array(
                'label' => \MapasCulturais\i::__('Selos'),
                'serialize' => function($value) { return json_encode($value); },
                'unserialize' => function($value) { return json_decode((string) $value); }
        ),

        /** @TODO: colocar isso na entidade Opportunity (issue: #1273) **/
        'projectName' => array(
            'label' => \MapasCulturais\i::__('Nome do Projeto'),
            'type' => 'select',
            'default' => '0',
            'options' => (object) array(
                '0' => \MapasCulturais\i::__('Não Utilizar'),
                '1' => \MapasCulturais\i::__('Opcional'),
                '2' => \MapasCulturais\i::__('Obrigatório'),
            ),

            'unserialize' => function($val){
                return intval($val);
            }
        ),

        'totalResource' => array(
            'type' => 'float',
            'field_type' => 'currency',
            'label' => \MapasCulturais\i::__('Valor total'),
            // 'description' => \MapasCulturais\i::__("Valor total que esse edital irá disponibilizar."),
        ),

        'vacancies' => array(
            'type' => 'integer',
            'label' => \MapasCulturais\i::__('Total de vagas'),
            // 'description' => \MapasCulturais\i::__("Quantidades de vagas que esse edital irá disponibilizar."),
        ),
        
        'requestAgentAvatar' => array(
            'label' => \MapasCulturais\i::__('Solicitar avatar'),
            'type' => 'radio',
            'default' => '0',
            'options' => (object) array(
                '0' => \MapasCulturais\i::__('Desabilitado'),
                '1' => \MapasCulturais\i::__('Habilitado'),
            ),
            'unserialize' => function($value) {
               return ($value == 0 || $value == "" || $value == "0") ? false : true;
            }
        ),

        'isModel' => array(
            'type' => 'integer',
            'label' => \MapasCulturais\i::__('É modelo?'),
            'default_value' => 0
        ),
        
        'isModelPublic' => array(
            'type' => 'integer',
            'label' => \MapasCulturais\i::__('É modelo público?'),
        ),
        
    ),
    'items' => $items,
    
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
