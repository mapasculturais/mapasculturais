<?php
use MapasCulturais\i;

return [
    'routes' => [
        'default_controller_id' => 'site',
        'default_action_name' => 'index',
        'shortcuts' => [
            // busca
            'agentes'           => ['search', 'agents'],
            'eventos'           => ['search', 'events'],
            'espacos'           => ['search', 'spaces'],
            'oportunidades'     => ['search', 'opportunities'],
            'projetos'          => ['search', 'projects'],

            // entidades
            'evento'            => ['event', 'single'],
            'usuario'           => ['user', 'single'],
            'agente'            => ['agent', 'single'],
            'espaco'            => ['space', 'single'],
            'projeto'           => ['project', 'single'],
            'selo'              => ['seal', 'single'],
            'oportunidade'      => ['opportunity', 'single'],
            'instalacao'        => ['subsite', 'single'],
            
            'edicao-de-evento'            => ['event', 'edit'],
            'edicao-de-usuario'           => ['user', 'edit'],
            'edicao-de-agente'            => ['agent', 'edit'],
            'edicao-de-espaco'            => ['space', 'edit'],
            'edicao-de-projeto'           => ['project', 'edit'],
            'edicao-de-selo'              => ['seal', 'edit'],
            'gestao-de-oportunidade'      => ['opportunity', 'edit'],
            'edicao-de-instalacao'        => ['subsite', 'edit'],

            'configuracao-de-formulario'  => ['opportunity', 'formBuilder'],
            'lista-de-inscricoes'  => ['opportunity', 'registrations'],
            'lista-de-avaliacoes'  => ['opportunity', 'allEvaluations'],
            
            'avaliacoes'  => ['opportunity', 'userEvaluations'],

            'suporte/lista-de-inscricoes'  => ['support', 'list'],
            'suporte/formulario'  => ['support', 'form'],
            'suporte/configuracao' => ['support', 'supportConfig'],
            
            'baixar-rascunhos' => ['opportunity', 'reportDrafts'],
            'baixar-inscritos' => ['opportunity', 'report'],
            'baixar-avaliacoes' => ['opportunity', 'reportEvaluations'],

            'avaliacao' => ['registration', 'evaluation'],


            'historico'         => ['entityRevision','history'],
            
            'sair'              => ['auth', 'logout'],
            'busca'             => ['site', 'search'],
            'sobre'             => ['site', 'page', ['sobre']],
            'como-usar'         => ['site', 'page', ['como-usar']],
            
            // LGPD
            'termos-de-uso'             => ['lgpd', 'view', ['termsOfUsage']], 
            'politica-de-privacidade'   => ['lgpd','view', ['privacyPolicy']],
            'uso-de-imagem'             =>['lgpd', 'view', ['termsUse']],
            'termos-e-condicoes'        => ['lgpd','accept'],

            // painel
            'meus-agentes'             => ['panel', 'agents'],
            'meus-espacos'             => ['panel', 'spaces'],
            'meus-eventos'             => ['panel', 'events'],
            'meus-projetos'            => ['panel', 'projects'],
            'minhas-oportunidades'     => ['panel', 'opportunities'],
            'minhas-inscricoes'        => ['panel', 'registrations'],
            'minhas-avaliacoes'        => ['panel', 'evaluations'],
            'minhas-prestacoes-de-contas'        => ['panel', 'prestacoes-de-conta'],

            'aparencia'               => ['theme-customizer', 'index'],
            
            'conta-e-privacidade'        => ['panel', 'my-account'],

            'inscricao' => ['registration', 'edit'],
            'inscricao' => ['registration', 'single'],
            'inscricao' => ['registration', 'view'],

            'visualizacao-de-formulario' => ['opportunity', 'formPreview'],

            'gestao-de-usuarios' => ['panel', 'user-management'],

            'certificado' => ['relatedSeal','single'],

            'perguntas-frequentes' => ['faq', 'index'],

            'file/arquivo-privado' => ['file', 'privateFile'],

        ],
        'controllers' => [
            'painel'         => 'panel',
            'inscricoes'     => 'registration',
            'inscricoes'     => 'registration',
            'autenticacao'   => 'auth',
            'anexos'         => 'registrationfileconfiguration',
            'revisoes'       => 'entityRevision',
            'historico'      => 'entityRevision',
            'suporte'        => 'support',
        ],
        'actions' => [
            'acesso'         => 'single',
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