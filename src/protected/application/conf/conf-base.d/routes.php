<?php
use MapasCulturais\i;

return [
    'routes' => [
        'default_controller_id' => 'site',
        'default_action_name' => 'index',
        'shortcuts' => [
            // exemplos de shortcut adicionando parametros
            'james-bond'                => ['agent', 'single', ['id' => 7]],
            // 'agente/007'                => ['agent', 'single', ['id' => '007')),
            // 'teste/de/shortcut/longo'   => ['agent', 'single', ['id' => 'shortcut longo')),
            //'historico' => ['entityRevision','history',['entity' => 'event','id' => '6')),
            'historico'         => ['entityRevision','history'],
            'evento'            => ['event', 'single'],
            'usuario'           => ['user', 'single'],
            'agente'            => ['agent', 'single'],
            'espaco'            => ['space', 'single'],
            'projeto'           => ['project', 'single'],
            'oportunidade'      => ['opportunity', 'single'],
            'salva-avaliacao'   => ['registration', 'saveEvaluation'],
            'instalacao'        => ['subsite', 'single'],
            'selo'              => ['seal', 'single'],
            'sair'              => ['auth', 'logout'],
            'busca'             => ['site', 'search'],
            'sobre'             => ['site', 'page', ['sobre']],
            'como-usar'         => ['site', 'page', ['como-usar']],
            'termos-de-uso'     => ['lgpd', 'accept', ['termsOfUsage']], 
            'politica-de-privacidade' => ['lgpd','accept', ['privacyPolicy']],

            // workflow actions
            'aprovar-notificacao' => ['notification', 'approve'],
            'rejeitar-notificacao' => ['notification', 'reject'],

            'inscricao' => ['registration', 'view'],
            'certificado' => ['relatedSeal','single'],

        ],
        'controllers' => [
            'painel'         => 'panel',
            'autenticacao'   => 'auth',
            'site'           => 'site',
            'eventos'        => 'event',
            'agentes'        => 'agent',
            'espacos'        => 'space',
            'arquivos'       => 'file',
            'projetos'       => 'project',
            'oportunidades'  => 'opportunity',
            'selos'          => 'seal',
            'inscricoes'     => 'registration',
            'instalacoes'    => 'subsite',
            'anexos'         => 'registrationfileconfiguration',
            'revisoes'       => 'entityRevision',
            'historico'      => 'entityRevision',
            'suporte'        => 'support',
        ],
        'actions' => [
            'lista'         => 'list',
            'apaga'         => 'delete',
            'edita'         => 'edit',
            'espacos'       => 'spaces',
            'agentes'       => 'agents',
            'eventos'       => 'events',
            'projetos'      => 'projects',
            'oportunidades' => 'oportunities',
            'subsite'       => 'subsite',
            'selos'         => 'seals',
            'inscricoes'    => 'registrations',
            'agente'        => 'agent',
            'evento'        => 'event',
            'inscricao'     => 'registration',
            'prestacoes-de-contas' => 'accountability'
        ],

        'readableNames' => [
            //controllers

            'panel'         => i::__('Painel'),
            'auth'          => i::__('Autenticação'),
            'site'          => i::__('Site'),
            'event'         => i::__('Evento'),       'events'        => i::__('Eventos'),
            'agent'         => i::__('Agente'),       'agents'        => i::__('Agentes'),
            'space'         => i::__('Espaço'),       'spaces'        => i::__('Espaços'),
            'project'       => i::__('Projeto'),      'projects'      => i::__('Projetos'),
            'opportunity'   => i::__('Oportunidade'), 'opportunities' => i::__('Oportunidades'),
            'registration'  => i::__('Inscrição'),    'registrations' => i::__('Inscrições'),
            'file'          => i::__('Arquivo'),      'files'         => i::__('Arquivos'),
            'seal'          => i::__('Selo'),         'seals'         => i::__('Selos'),
            'entityRevision'=> i::__('Histórico'),    'revisions'     => i::__('Revisões'),
            'sealrelation'  => i::__('Certificado'),
            //actions
            'subsite'       => i::__('Subsite'),
            'list'          => i::__('Listando'),
            'index'         => i::__('Índice'),
            'delete'        => i::__('Apagando'),
            'edit'          => i::__('Editando'),
            'create'        => i::__('Criando novo'),
            'search'        => i::__('Busca')
        ]
    ]
];