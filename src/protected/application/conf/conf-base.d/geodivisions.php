<?php

use MapasCulturais\i;

return [
     /* Divisões geográficas
     * Veja http://docs.mapasculturais.org/mc_deploy_shapefiles/ para informações de como
     * inserir shapefiles aos bancos e cadastrá-los aqui
     *
     * coloque um underline "_" na frente do slug da division para
     * que o metadado gerado não seja exibido na página de perfil da entidade.
     *
     * Ex: '_estado'        => \MapasCulturais\i::__('Estado'),
     *
     */
    'app.geoDivisionsHierarchy' => [
        'pais'              => ['name' => i::__('País'),            'showLayer' => true],
        'regiao'            => ['name' => i::__('Região'),          'showLayer' => true],
        'estado'            => ['name' => i::__('Estado'),          'showLayer' => true],
        'mesorregiao'       => ['name' => i::__('Mesorregião'),     'showLayer' => true],
        'microrregiao'      => ['name' => i::__('Microrregião'),    'showLayer' => true],
        'municipio'         => ['name' => i::__('Município'),       'showLayer' => true],
        'zona'              => ['name' => i::__('Zona'),            'showLayer' => true],
        'subprefeitura'     => ['name' => i::__('Subprefeitura'),   'showLayer' => true],
        'distrito'          => ['name' => i::__('Distrito'),        'showLayer' => true],
        'setor_censitario'  => ['name' => i::__('Setor Censitario'),'showLayer' => false]
    ],
];