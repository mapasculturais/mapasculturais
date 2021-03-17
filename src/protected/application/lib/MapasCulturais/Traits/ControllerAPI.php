<?php
namespace MapasCulturais\Traits;

use MapasCulturais\App;
use Doctrine\ORM\Tools\Pagination\Paginator;

use MapasCulturais\ApiQuery;

trait ControllerAPI{

    /**
     * Array with key => value of params for the API find DQL
     *
     * @var array
     */
    private $_apiFindParamList = [];

    public $_lastQueryMetadata;

    protected $_subsiteEntityIds;

    public static function usesAPI(){
        return true;
    }

    /**
     * Returns the ApiOutput Object.
     *
     * @return \MapasCulturais\ApiOutput
     */
    protected function getApiOutput(){
        $app = App::i();
        $type = key_exists('@type',$this->data) ? $this->data['@type'] : 'json';
        $responder = $app->getRegisteredApiOutputById($type);

        if(!$responder){
            echo sprintf(\MapasCulturais\i::__("tipo %s não está registrado."), $type);
            App::i()->stop();
        }else{
            return $responder;
        }
    }

    /*
     * Outputs an API Error Response
     *
     */
    protected function apiErrorResponse($error_message){
        $responder = $this->getApiOutput();
        $responder->outputError($error_message);
        App::i()->stop();
    }

    protected function apiResponse($data){
        if(is_array($data))
            $this->apiArrayResponse($data);
        else
            $this->apiItemResponse($data);
    }

    /**
     * Outputs an API Array Respose
     *
     * @param array $data
     * @param string $singular_name the singular name of the response itens
     * @param string $plural_name the plural name of the response itens
     */
    protected function apiArrayResponse(array $data, $singular_name = 'Entity', $plural_name = 'Entities'){
        $responder = $this->getApiOutput();
        $responder->outputArray($data, $singular_name, $plural_name);
        App::i()->stop();
    }

    protected function apiAddHeaderMetadata($qdata, $data, $count){
        if (headers_sent())
            return;

        $response_meta = [
            'count' => intval($count),
            'page' => isset($qdata['@page']) ? intval($qdata['@page']) : 1,
            'limit' => isset($qdata['@limit']) ? intval($qdata['@limit']) : null,
            'numPages' => isset($qdata['@limit']) ? ceil($count / $qdata['@limit']) : 1,
            'keyword' => isset($qdata['@keyword']) ? $qdata['@keyword'] : '',
            'order' => isset($qdata['@order']) ? $qdata['@order'] : ''
        ];

        $this->_lastQueryMetadata = (object) $response_meta;

        header('API-Metadata: ' . json_encode($response_meta));
    }

    function getLastQueryMetadata(){
        return $this->_lastQueryMetadata;
    }

    /**
     * Ouputs an API Item Reponse
     *
     * @param mixed $data
     * @param string $singular_name the singular name of the response itens
     * @param string $plural_name the plural name of the response itens
     */
    protected function apiItemResponse($data, $singular_name = 'Entity', $plural_name = 'Entities'){
        $responder = $this->getApiOutput();
        $responder->outputItem($data, $singular_name, $plural_name);
        App::i()->stop();
    }

    /**
     * A generic API findOne method.
     *
     * This action finds one entity by the requested params and send the result to the API Responder.
     *
     * @see \MapasCulturais\ApiOutput::outputItem()
     */
   

    /**
     * @apiDefine APIfindOne
     * @apiDescription Realiza a busca por uma unica entidade de acordo com os parâmetros solicitados.
     * @apiParam {String} [@select] usado para selecionar as propriedades da entidade que serão retornadas pela api. 
     *                            Você pode retornar propriedades de entidades relacionadas. ex:( @select: id,name,
     *                            owner.name,owner.singleUrl)
     * @apiParam {String} [@order] usado para definir em que ordem o resultado será retornado. ex:( @order: name ASC, id DESC)
     * @apiParam {String} [@limit] usado para definir o número máximo de entidades que serão retornadas. ex:( @limit: 10)
     * @apiParam {String} [@page] usado em paginações em conjunto com o @limit. ex:( @limit:10, @page: 2)
     * @apiParam {String} [@or] se usado a api usará o operador lógico OR para criar a query. ex:( @or:1)
     * @apiParam {String} [@type] usado para definir o tipo de documento a ser gerado com o resultado da busca. ex:( @type: html; ou @type: json; ou @type: xml)
     * @apiParam {String} [@files] indica que é para retornar os arquivos anexos. ex:( @files=(avatar.avatarSmall,header):name,url - retorna o nome e url do thumbnail de tamanho avatarSmall da imagem avatar e a imagem header original)
     * @apiParam {String} [@seals] usado para filtrar registros que tenha selo aplicado, recebe como parâmetro o id do registro do selo. ex:( @seals: 1,10,25)
     * @apiParam {String} [@profiles] usado para filtrar os registros de agentes que estão vinculados a um perfil de usuário do sistema. ex:( @profiles:1)
     * @apiParam {String} [@permissions] usado para trazer os registros onde o agente tem permissão de acesso(visualização) e/ou edição. Para visualização, informar 'view', para controle que seria visualização e edição '@control'. _ex:(@permissions:'view')
     * @apiParam {String} [nomeCampo] campos para realizar a pesquisa na base, para filtrar os resultados o método aceita operadores. 
     *                                Para ver a lista de operadores possíveis e exemplos avançados de uso visite <a href="http://docs.mapasculturais.org/mc_config_api" rel='noopener noreferrer'>http://docs.mapasculturais.org/mc_config_api</a>)      
     *                                Para filtrar os resultados o método find aceita os seguintes operadores em qualquer das propriedades e metadados das entidades:<br/>
     *                                <table>
     *                                <tr><td>Operador</td><td>Exemplo</td></tr>
     *                                <tr><td>**EQ** (igual) </td><td> _ex:( id: EQ (10) - seleciona a entidade de id igual a 10)__ </td></tr>
     *                                <tr><td>**GT** (maior que) </td><td> _ex:( id: GT (10) - seleciona todas as entidades com id maior a 10)__ </td></tr>
     *                                <tr><td>**GTE** (maior ou igual) </td><td> _ex:( id: GTE (10) - seleciona todas as entidades com id maior ou igual a 10)__ </td></tr>
     *                                <tr><td>**LT** (menor que) </td><td> _ex:( id: LT (10) - seleciona todas as entidades com id menor a 10)__ </td></tr>
     *                                <tr><td>**LTE** (menor ou igual) </td><td> _ex:( id: LTE (10) - seleciona todas as entidades com id menor ou igual a 10)__ </td></tr>
     *                                <tr><td>**NULL** (nao definido) </td><td> _ex:( age: null() - seleciona todas as entidades com idade não definida)__ </td></tr>
     *                                <tr><td>**IN** (en) </td><td> _ex:( id: IN (10,18,33) - seleciona as entidades de id 10, 18 e 33)__ </td></tr>
     *                                <tr><td>**BET** (entre) </td><td> _ex:( id: BET (100,200) - seleciona as entidades de id entre 100 e 200)__ </td></tr>
     *                                <tr><td>**LIKE** </td><td> _ex:( name: LIKE (fael) - seleciona as entidades com nome LIKE '*fael*' (ver operador LIKE do sql))__ </td></tr>
     *                                <tr><td>**ILIKE** (LIKE ignorando maiúsculas e minúsculas) </td><td> _ex:( name: ILIKE (rafael*) seleciona as entidades com o nome começando com Rafael, rafael, RAFAEL, etc.)__ </td></tr>
     *                                <tr><td>**OR** (operador lógico OU) </td><td> _ex:( id: OR (BET (100,200), BET (300,400), IN (10,19,33)) - seleciona as entidades com id entre 100 e 200, entre 300 e 400 ou de id 10,19 ou 33)__ </td></tr>
     *                                <tr><td>**AND** (operador lógico AND) </td><td> _ex:( name: AND (ILIKE ('Rafael%'), ILIKE ('*Freitas')) - seleciona as entidades com nome começando com Rafael e terminando com Freitas (por exemplo: Rafael Freitas, Rafael Chaves Freitas, RafaelFreitas))_ </td></tr>
     *                                <tr><td>**GEONEAR** </td><td> _ex:( _geoLocation: GEONEAR (-46.6475415229797, -23.5413271705055, 700) - seleciona as entidades que estão no máximo há 700 metros do ponto de latitude -23.5413271705055 e longitude -46.6475415229797)__ </td></tr>
     *                                </table>
     *                                Veja mais exemplos de uso em <a href="http://docs.mapasculturais.org/mc_config_api/" rel='noopener noreferrer'>http://docs.mapasculturais.org/mc_config_api/</a>
     * 
     * @apiParamExample {json} Exemplo:
     *           { 
     *               "id": "EQ(37)",
     *               "@select": "id,name" 
     *           }
     *
     * 
     */
    public function API_findOne(){
        $entity = $this->apiQuery($this->getData, ['findOne' => true]);
        $this->apiItemResponse($entity);
    }

    /**
     * @apiDefine APIfind
     * @apiDescription Realiza a busca por entidades de acordo como parâmetros solicitados.
     * @apiParam {String} [@select] usado para selecionar as propriedades da entidade que serão retornadas pela api. 
     *                            Você pode retornar propriedades de entidades relacionadas. ex:( @select: id,name,
     *                            owner.name,owner.singleUrl)
     * @apiParam {String} [@order] usado para definir em que ordem o resultado será retornado. ex:( @order: name ASC, id DESC)
     * @apiParam {String} [@limit] usado para definir o número máximo de entidades que serão retornadas. ex:( @limit: 10)
     * @apiParam {String} [@page] usado em paginações em conjunto com o @limit. ex:( @limit:10, @page: 2)
     * @apiParam {String} [@or] se usado a api usará o operador lógico OR para criar a query. ex:( @or:1)
     * @apiParam {String} [@type] usado para definir o tipo de documento a ser gerado com o resultado da busca. ex:( @type: html; ou @type: json; ou @type: xml)
     * @apiParam {String} [@files] indica que é para retornar os arquivos anexos. ex:( @files=(avatar.avatarSmall,header):name,url - retorna o nome e url do thumbnail de tamanho avatarSmall da imagem avatar e a imagem header original)
     * @apiParam {String} [@seals] usado para filtrar registros que tenha selo aplicado, recebe como parâmetro o id do registro do selo. ex:( @seals: 1,10,25)
     * @apiParam {String} [@profiles] usado para filtrar os registros de agentes que estão vinculados a um perfil de usuário do sistema. ex:( @profiles:1)
     * @apiParam {String} [@permissions] usado para trazer os registros onde o agente tem permissão de acesso(visualização) e/ou edição. Para visualização, informar 'view', para controle que seria visualização e edição '@control'. _ex:(@permissions:'view')
     * @apiParam {String} [nomeCampo] campos para realizar a pesquisa na base, para filtrar os resultados o método aceita operadores. Para ver a lista de operadores possíveis e exemplos avançados de uso visite <a href="http://docs.mapasculturais.org/mc_config_api" rel='noopener noreferrer'>http://docs.mapasculturais.org/mc_config_api</a>) 
     * @apiParamExample {json} Exemplo:
     *           { 
     *               "id": "BET(100,200)",
     *               "@select": "id,name" 
     *           }
     *
     */
    public function API_find(){
        $data = $this->apiQuery($this->getData);
        $this->apiResponse($data);
    }


    /**
     * @apiDefine APIdescribe
     * @apiDescription Retorna a descrição de entidade.
     * @apiSuccessExample {json} Success-Response:
                   { 
                       "id":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":true,
                        "type":"integer",
                        "length":null
                    },
                    "location":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":true,
                        "type":"point",
                        "length":null
                    },
                    "name":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":true,
                        "type":"string",
                        "length":255
                    },
                    "shortDescription":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":false,
                        "type":"text",
                        "length":null
                    },
                    "longDescription":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":false,
                        "type":"text",
                        "length":null
                    },
                    "certificateText":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":false,
                        "type":"text",
                        "length":null
                    },
                    "createTimestamp":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":true,
                        "type":"datetime",
                        "length":null
                    },
                    "status":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":true,
                        "type":"smallint",
                        "length":null
                    },
                    "_type":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":true,
                        "type":"smallint",
                        "length":null,
                        "@select":"type"
                    },
                    "isVerified":{
                        "isMetadata":false,
                        "isEntityRelation":false,
                        "required":true,
                        "type":"boolean",
                        "length":null
                    },
                    "parent":{
                        "isMetadata":false,
                        "isEntityRelation":true,
                        "targetEntity":"Space",
                        "isOwningSide":true
                    },
                    "children":{
                        "isMetadata":false,
                        "isEntityRelation":true,
                        "targetEntity":"Space",
                        "isOwningSide":false
                    },
                    "owner":{
                        "isMetadata":false,
                        "isEntityRelation":true,
                        "targetEntity":"Agent",
                        "isOwningSide":true
                    },
                    "emailPublico":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Email Público",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "emailPrivado":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Email Privado",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "telefonePublico":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Telefone Público",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "telefone1":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Telefone 1",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "telefone2":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Telefone 2",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "acessibilidade":{
                        "required":false,
                        "type":"select",
                        "length":null,
                        "private":false,
                        "options":{
                            "":"Não Informado",
                            "Sim":"Sim",
                            "Não":"Não"
                        },
                        "label":"Acessibilidade",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "capacidade":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Capacidade",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "endereco":{
                        "required":false,
                        "type":"text",
                        "length":null,
                        "private":false,
                        "label":"Endereço",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "horario":{
                        "required":false,
                        "type":"text",
                        "length":null,
                        "private":false,
                        "label":"Horário de funcionamento",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "criterios":{
                        "required":false,
                        "type":"text",
                        "length":null,
                        "private":false,
                        "label":"Critérios de uso do espaço",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "site":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Site",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "facebook":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Facebook",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "twitter":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Twitter",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "googleplus":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Google+",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "sp_regiao":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Região",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "sp_subprefeitura":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Subprefeitura",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                    "sp_distrito":{
                        "required":false,
                        "type":"string",
                        "length":null,
                        "private":false,
                        "label":"Distrito",
                        "isMetadata":true,
                        "isEntityRelation":false
                    },
                        "@file": {
                            "header": [
                                "header"
                            ],
                            "avatar": [
                                "avatar",
                                "avatarSmall",
                                "avatarMedium",
                                "avatarBig",
                                "avatarEvent"
                            ],
                            "downloads": [
                                "downloads"
                            ],
                            "gallery": [
                                "gallery",
                                "galleryThumb",
                                "galleryFull"
                            ]
                        }
                    }
     *
     */
    public function API_describe(){
        $class = $this->entityClassName;
        $data_array = $class::getPropertiesMetadata();
        $file_groups = App::i()->getRegisteredFileGroupsByEntity( $class );

        //Show URL's shortcuts for entity:
        $entityShortcuts = strtolower(str_replace("MapasCulturais\\Entities\\", "", $class));
        foreach (App::i()->config['routes']['shortcuts'] as $key => $short) {
            if ($short[0] === $entityShortcuts) {
                $data_array[ $short[1]."Url" ] = [
                    'isMetadata' => false,
                    'isEntityRelation' => false,
                    'required'  => false,
                    'type' => "string",
                    'label' => $short[1]
                ];
            }
        }
        $image_transformations = include APPLICATION_PATH.'/conf/image-transformations.php';
        $theme_class = "\\" . App::i()->config['themes.active'] . '\Theme';
        $theme_path = $theme_class::getThemeFolder() . '/';
        if (file_exists($theme_path . 'image-transformations.php')) {
            $image_transformations = include $theme_path . 'image-transformations.php';
        }
        $array = [];
        foreach ($file_groups as $key => $value) {
            $arr = [$key];
            foreach ($image_transformations as $k => $v) {
                if ((strlen($k) > strlen($key)) && (substr($k, 0, strlen($key)) == $key)) {
                    array_push($arr, $k);
                }
            }
            $array[$key] = $arr;
        }
        $data_array["@file"] = $array;
        $this->apiResponse($data_array);
    }
}