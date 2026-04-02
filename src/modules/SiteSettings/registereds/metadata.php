<?php

use MapasCulturais\i;

$metadata = [
    // Configurações iniciais - EMAIL
    'mailer_email' => [
        'label' => i::__('Email'),
        'type' => 'string',
        'private' => true,
        'validations' => [
            'required' => \MapasCulturais\i::__("O email é obrigatório")
        ]
    ],
    'mailer_user' => [
        'label' => i::__('Usuário'),
        'type' => 'string',
        'private' => true,
        'validations' => [
            'required' => \MapasCulturais\i::__("O usuário é obrigatório")
        ]
    ],
    'mailer_host' => [
        'label' => i::__('Servidor Host'),
        'type' => 'string',
        'private' => true,
        'validations' => [
            'required' => \MapasCulturais\i::__("O servidor host é obrigatório")
        ]
    ],
    'mailer_protocol' => [
        'label' => i::__('Protocolo'),
        'type' => 'select',
        'private' => true,
        'options' => [
            'LOCAL' => 'Local',
            'SSL' => 'SSL',
            'TLS' => 'TLS',
        ],
        'validations' => [
            'required' => \MapasCulturais\i::__("O protocolo é obrigatório")
        ]
    ],
    'mailer_password' => [
        'label' => i::__('Senha'),
        'type' => 'string',
        'private' => true,
        'validations' => [
            'required' => \MapasCulturais\i::__("A senha é obrigatória")
        ]
    ],
    'mailer_repassword' => [
        'label' => i::__('Confirme a senha'),
        'type' => 'string',
        'private' => true,
        'validations' => [
            'required' => \MapasCulturais\i::__("A confirmação da senha é obrigatória")
        ]
    ],
    // Configurações iniciais - reCaptcha
    'recaptcha_secret' => [
        'label' => i::__('Chave secreta'),
        'type' => 'string',
        'private' => true,
        'validations' => [
            'required' => \MapasCulturais\i::__("A chave secreta é obrigatório")
        ]
    ],
    'recaptcha_sitekey' => [
        'label' => i::__('Chave do site'),
        'type' => 'string',
        'private' => true,
        'validations' => [
            'required' => \MapasCulturais\i::__("A chave do site é obrigatório")
        ]
    ],
    // Configurações iniciais - Georreferenciamento
    'geodivisions' => [
        'label' => i::__('Divisões geográficas'),
        'type' => 'json',
    ],
    'geoDivisionsFilters' => [
        'label' => i::__('Filtro de unidades federativas'),
        'type' => 'json',
    ],
    'zoom_default' => [
        'label' => i::__('Zoom padrão do mapa'),
        'type' => 'text',
    ],
    'zoom_max' => [
        'label' => i::__('Zoom máximo do mapa'),
        'type' => 'text',
    ],
    'zoom_min' => [
        'label' => i::__('Zoom mínimo do mapa'),
        'type' => 'text',
    ],
    'latitude' => [
        'label' => i::__('Latitude'),
        'type' => 'text',
    ],
    'longitude' => [
        'label' => i::__('Longitude'),
        'type' => 'text',
    ],
    // Redes sociais
    'socialmedia' => [
        'label' => i::__('Redes sociais'),
        'type' => 'json',
    ],
    'socialmediaData' => [
        'label' => i::__('Redes sociais configuradas'),
        'type' => 'json',
    ],
    // Textos e imagens baner
    'bannerImageData' => [
        'label' => i::__('imagem Banner home'),
        'type' => 'json',
    ],
    'bannerTitle' => [
        'label' => i::__('Título'),
        'type' => 'text',
    ],
    'bannerDescription' => [
        'label' => i::__('Texto com, no máximo, 600 caracteres.'),
        'type' => 'textarea',
    ],
    // Textos e imagens da seção entidades
    'entitiesTitle' => [
        'label' => i::__('Título da seção de entidades na página inicial, com limite de 65 caracteres.'),
        'type' => 'text',
    ],
    'entitiesDescription' => [
        'label' => i::__('Descrição da seção de entidades na página inicial, com limite de 250 caracteres.'),
        'type' => 'textarea',
    ],
    // Entidade opportunity
    'entityOpportunityDescription' => [
        'label' => i::__('Texto com, no máximo, 600 caracteres.'),
        'type' => 'textarea',
    ],
    'entitiesOpportunityImageData' => [
        'label' => i::__('imagem da entidade oportunidade na home'),
        'type' => 'json',
    ],
    // Entidade eventos
    'entityEventDescription' => [
        'label' => i::__('Texto com, no máximo, 600 caracteres.'),
        'type' => 'textarea',
    ],
    'entitiesEventImageData' => [
        'label' => i::__('imagem da entidade evento na home'),
        'type' => 'json',
    ],
    // Entidade espaços
    'entitySpaceDescription' => [
        'label' => i::__('Texto com, no máximo, 600 caracteres.'),
        'type' => 'textarea',
    ],
    'entitiesSpaceImageData' => [
        'label' => i::__('imagem da entidade espaços na home'),
        'type' => 'json',
    ],
    // Entidade agentes
    'entityAgentDescription' => [
        'label' => i::__('Texto com, no máximo, 600 caracteres.'),
        'type' => 'textarea',
    ],
    'entitiesAgentImageData' => [
        'label' => i::__('imagem da entidade agentes na home'),
        'type' => 'json',
    ],
    // Entidade projetos
    'entityProjectDescription' => [
        'label' => i::__('Texto com, no máximo, 600 caracteres.'),
        'type' => 'textarea',
    ],
    'entitiesProjectImageData' => [
        'label' => i::__('imagem da entidade projetos na home'),
        'type' => 'json',
    ],
    // Em destaque
    'featureTitle' => [
        'label' => i::__('Título da seção de Em destaque na página inicial, com limite de 65 caracteres.'),
        'type' => 'text',
    ],
    'featureDescription' => [
        'label' => i::__('Descrição da seção de Em destaque na página inicial, com limite de 250 caracteres.'),
        'type' => 'textarea',
    ],
    // Textos e imagens cadastre-se
    'registerImageData' => [
        'label' => i::__('imagem da seção cadastre-se'),
        'type' => 'json',
    ],
    'registerTitle' => [
        'label' => i::__('Título da seção de Casdastre-se na página inicial, com limite de 65 caracteres.'),
        'type' => 'text',
    ],
    'registerDescription' => [
        'label' => i::__('Descrição da seção de Casdastre-se na página inicial, com limite de 300 caracteres.'),
        'type' => 'textarea',
    ],
    // Mapa
    'mapTitle' => [
        'label' => i::__('Título da seção Mapa na página inicial, com limite de 65 caracteres.'),
        'type' => 'text',
    ],
    'mapDescription' => [
        'label' => i::__('Descrição da seção Mapa na página inicial, com limite de 250 caracteres.'),
        'type' => 'textarea',
    ],
    // Desenvolvedores
    'developerTitle' => [
        'label' => i::__('Título da seção Desenvolvedores na página inicial, com limite de 65 caracteres.'),
        'type' => 'text',
    ],
    'developerDescription' => [
        'label' => i::__('Descrição da seção Desenvolvedores na página inicial, com limite de 250 caracteres.'),
        'type' => 'textarea',
    ],
    // Imagens diversas logotipo
    'typeLogoDefinition' => [
        'label' => i::__('Defina aqui como deseja utilizar o logotipo do ambiente'),
        'type' => 'radio',
        'options' => [
            'default' => i::__('Utilizar o logotipo padrão'),
            'image' => i::__('Carregar uma imagem'),
        ],
        'default' => 'default'
    ],
    'logoDefaultTitle' => [
        'label' => i::__('Título'),
        'type' => 'text',
    ],
    'logoDefaultSubTitle' => [
        'label' => i::__('Subtítulo'),
        'type' => 'text',
    ],

    'logoColorPart1' => [
        'label' => i::__('Cor logotipo parte 1'),
        'type' => 'color',
        'default' => null,
    ],
    'logoColorPart2' => [
        'label' => i::__('Cor logotipo parte 2'),
        'type' => 'color',
        'default' => null,
    ],
    'logoColorPart3' => [
        'label' => i::__('Cor logotipo parte 3'),
        'type' => 'color',
        'default' => null,
    ],
    'logoColorPart4' => [
        'label' => i::__('Cor logotipo parte 4'),
        'type' => 'color',
        'default' => null,
    ],
    'imageLogoData' => [
        'label' => i::__('Logotipo'),
        'type' => 'json',
    ],
    // Imagens diversas Favicon SVG
    'faviconSvgData' => [
        'label' => i::__('Favicon SVG'),
        'type' => 'json',
    ],
    // Imagens diversas Favicon PNG
    'faviconPngData' => [
        'label' => i::__('Favicon PNG'),
        'type' => 'json',
    ],
    // Imagens diversas Imagem de compartilhamento
    'shareData' => [
        'label' => i::__('Imagem de compartilhamento'),
        'type' => 'json',
    ],
    // Imagens diversas Imagem de email
    'mailImageData' => [
        'label' => i::__('Imagem de email'),
        'type' => 'json',
    ],
    // Cor principal
    'primaryColor' => [
        'label' => i::__('Cor primaria do ambiente'),
        'type' => 'color',
        'default' => null,
    ],
    'secondaryColor' => [
        'label' => i::__('Cor secundária do ambiente'),
        'type' => 'color',
        'default' => null,
    ],
    // Cor Oportunidade
    'opportunitiesColor' => [
        'label' => i::__('Cor da entidade Oportunidade'),
        'type' => 'color',
        'default' => null,
    ],
     // Cor Agentes
     'agentsColor' => [
        'label' => i::__('Cor da entidade Agentes'),
        'type' => 'color',
        'default' => null,
    ],
     // Cor Eventos
     'eventsColor' => [
        'label' => i::__('Cor da entidade Eventos'),
        'type' => 'color',
        'default' => null,
    ],
     // Cor Espaços
     'spacesColor' => [
        'label' => i::__('Cor da entidade Espaços'),
        'type' => 'color',
        'default' => null,
    ],
    // Cor Projetos
    'projectsColor' => [
        'label' => i::__('Cor da entidade Projetos'),
        'type' => 'color',
        'default' => null,
    ],
     // Cor Projetos
     'sealsColor' => [
        'label' => i::__('Cor da entidade Selos'),
        'type' => 'color',
        'default' => null,
    ],
    'enabledCacheColor' => [
        'label' => i::__('Cache de cor desabilitado'),
        'type' => 'boolean',
        'default' => true,
    ],
];

return $metadata;
