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
            'count' => $count,
            'page' => isset($qdata['@page']) ? $qdata['@page'] : 1,
            'limit' => isset($qdata['@limit']) ? $qdata['@limit'] : null,
            'numPages' => isset($qdata['@limit']) ? intval($count / $qdata['@limit']) + 1 : 1,
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
     *                                Para ver a lista de operadores possíveis e exemplos avançados de uso visite <a href="http://docs.mapasculturais.org/mc_config_api">http://docs.mapasculturais.org/mc_config_api</a>)      
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
     *                                Veja mais exemplos de uso em <a href="http://docs.mapasculturais.org/mc_config_api/">http://docs.mapasculturais.org/mc_config_api/</a>
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
     * @apiParam {String} [nomeCampo] campos para realizar a pesquisa na base, para filtrar os resultados o método aceita operadores. Para ver a lista de operadores possíveis e exemplos avançados de uso visite <a href="http://docs.mapasculturais.org/mc_config_api">http://docs.mapasculturais.org/mc_config_api</a>) 
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

    public function getApiCacheId($qdata, $options = []){
        return $this->id . '::' . md5(serialize($qdata + ['__OPTIONS__' => $options]));
    }

    public function apiCacheExists($cache_id){
        $app = App::i();

        if(!$app->config['app.useApiCache'])
            return false;

        return $app->cache->contains($cache_id);
    }

    public function apiCacheResponse($cache_id){
        if($this->apiCacheExists($cache_id)){
            $app = App::i();
                $cache = $app->cache->fetch($cache_id);

            $app->contentType($cache['contentType']);
            echo $cache['output'];

            $app->stop();
            return true;
        }else{
            return false;
        }
    }

    public function getApiCacheLifetime(){
        $app = App::i();
        $default_lifetime = $app->config['app.apiCache.lifetime'];
        $by_controller_lifetime = $app->config['app.apiCache.lifetimeByController'];

        if(isset($by_controller_lifetime[$this->id]))
            return (int) $by_controller_lifetime[$this->id];
        else
            return (int) $default_lifetime;
    }

    public function apiQuery($api_params, $options = []){
        if(isset($api_params['@version']) == 1){
            unset($api_params['@version']);
            return $this->apiQueryV1($api_params, $options);
        }
        
        $app = App::i();
        $findOne =  key_exists('findOne', $options) ? $options['findOne'] : false;
        $counting = key_exists('@count', $api_params);
        if($counting){
            unset($api_params['@count']);
        }

        $app->applyHookBoundTo($this, "API.{$this->action}({$this->id}).params", [&$api_params]);
        $query = new ApiQuery($this->entityClassName, $api_params);
        
        if($counting){
            $result = $query->getCountResult();
        } elseif( $findOne ) {
            $result = $query->getFindOneResult();
        } else {
            $result = $query->getFindResult();
            if(isset($api_params['@page']) || isset($api_params['@offset']) || isset($api_params['@limit'])){
                $count = $query->getCountResult();
            } else {
                $count = count($result);
            }
            $this->apiAddHeaderMetadata($api_params, $result, $count);
        }

        $app->applyHookBoundTo($this, "API.{$this->action}({$this->id}).result" , [&$api_params,  &$result]);
        
        return $result;
    }

    public function apiQueryV1($qdata, $options = []){
        $this->_apiFindParamList = [];
        $app = App::i();
        
        $__original_query_data = $qdata;

        $findOne =  key_exists('findOne', $options) ? $options['findOne'] : false;

        $counting = key_exists('@count', $qdata);

        $app->applyHookBoundTo($this, "API.{$this->action}({$this->id}).params", [&$qdata]);

        if($counting)
            unset($qdata['@count']);

        if(class_exists($this->entityClassName)){
            if(!$qdata && !$counting)
                $this->apiErrorResponse('no data');

            $class = $this->entityClassName;

            $entity_properties = array_keys($app->em->getClassMetadata($this->entityClassName)->fieldMappings);

            $entity_associations = $app->em->getClassMetadata($this->entityClassName)->associationMappings;

            $entity_metadata = [];
            $metadata_class = "";
            
            $profiles = false;

            $meta_num = 0;
            $taxo_num = 0;
            $dql_joins = "";
            $dql_select = [];
            $dql_select_joins = [];

            if($class::usesMetadata()){
                $metadata_class = $class::getMetadataClassName();

                $metadata_class = $class.'Meta';
                $dql_join_template = "
                        LEFT JOIN e.__metadata {ALIAS} WITH {ALIAS}.key = '{KEY}' ";

                foreach($app->getRegisteredMetadata($this->entityClassName) as $meta){
                    $entity_metadata[] = $meta->key;
                }
            }

            if($class::usesTaxonomies()){
                $taxonomies = [];
                $taxonomies_slugs = [];
                foreach($app->getRegisteredTaxonomies($class) as $obj){
                    $taxonomies[] = 'term:' . $obj->slug;
                    $taxonomies_slugs['term:' . $obj->slug] = $obj->slug;
                }

                $dql_join_term_template = "
                        LEFT JOIN e.__termRelations {ALIAS_TR} LEFT JOIN {ALIAS_TR}.term {ALIAS_T} WITH {ALIAS_T}.taxonomy = '{TAXO}'";
            }

            $keys = [];

            $append_files_cb = function(){};

            if(in_array('publicLocation', $entity_properties)){
                $select_properties = ['id','status','publicLocation'];
            }else{
                $select_properties = ['id','status'];
            }

            $select = ['id'];
            $select_metadata = [];
            $order = null;
            $op = ' AND ';
            $offset = null;
            $limit = null;
            $page = null;
            $keyword = null;
            $permissions = null;

            $seals = [];

            $dqls = [];

            foreach($qdata as $key => $val){
                $val = trim($val);
                if(strtolower($key) == '@select'){
                    $select = explode(',', $val);

                    $_joins = [];

                    foreach($select as $i => $prop){
                        $prop = trim($prop);
                        $select[$i] = $prop;
                        if(in_array($prop, $entity_properties)){
                            $select_properties[] = $prop;
                        }elseif(in_array($prop, $entity_metadata)){
                            $select_metadata[] = $prop;

                        }elseif(strpos($prop, '.') > 0){
                            $relation = substr($prop, 0, strpos($prop, '.'));
                            $relation_property = substr($prop, strpos($prop, '.') + 1);

                            if(strpos($relation_property, '.') > 0){
                                $relation_property = substr($relation_property, 0, strpos($relation_property, '.'));
                            }

                            if(isset($entity_associations[$relation])){
                                if(!isset($_joins[$relation])){
                                    $_joins[$relation] = [];
                                }

                                $_joins[$relation][] = $relation_property;
                            }
                        }
                    }

                    foreach($_joins as $j => $props){
                        $join_id = uniqid($j);
                        $dql_select_joins[] = "
                        LEFT JOIN e.{$j} {$join_id}";

                        $dql_select[] = ", {$join_id}";

                    }

                    continue;
                }elseif(strtolower($key) == '@keyword'){
                    $keyword = $val;
                    continue;
                }elseif(strtolower($key) == '@permissions'){
                    $permissions = explode(',', $val);
                    continue;
                }elseif(strtolower($key) == '@seals'){
                    $seals = explode(',', $val);
                    continue;
                }elseif(strtolower($key) == '@verified'){
                    $seals = $app->config['app.verifiedSealsIds'];
                    continue;
                }elseif(strtolower($key) == '@order'){
                    $order = $val;
                    continue;
                }elseif(strtolower($key) == '@or'){
                    $op = ' OR ';
                    continue;
                }elseif(strtolower($key) == '@offset'){
                    $offset = $val;
                    continue;
                }elseif(strtolower($key) == '@page'){
                    $page = $val;
                    continue;
                }elseif(strtolower($key) == '@limit'){
                    $limit = $val;
                    continue;
                }elseif(strtolower($key) == '@profiles'){
                    $profiles = true;
                    continue;
                }elseif(strtolower($key) == '@type'){
                    continue;
                }elseif(strtolower($key) == '@debug'){
                    continue;

                }elseif(strtolower($key) == '@files' && preg_match('#^\(([\w\., ]+)\)[ ]*(:[ ]*([\w, ]+))?#i', $val, $imatch)){

                    if($counting)
                        continue;
                    // example:
                    // @files=(avatar.smallAvatar,header.header):name,url

                    $cfg = [
                        'files' => explode(',', $imatch[1]),
                        'props' => key_exists(3, $imatch) ? explode(',', $imatch[3]) : ['url']
                    ];

                    $_join_in = [];

                    foreach($cfg['files'] as $_f){
                        if(strpos($_f, '.') > 0){
                            list($_f_group, $_f_transformation) = explode('.', $_f);
                            $_join_in[] = $_f_group;
                            $_join_in[] = 'img:' . $_f_transformation;
                        }else{
                            $_join_in[] = $_f;
                        }
                    }

                    $_join_in = array_unique($_join_in);

                    $dql_select[] = ", files, fparent";
                    $dql_select_joins[] = "
                        LEFT JOIN e.__files files WITH files.group IN ('" . implode("','", $_join_in) . "')
                        LEFT JOIN files.parent fparent";

                    $extract_data_cb = function($file, $ipath, $props){
                        $result = [];
                        if($ipath){
                            $path = explode('.', $ipath);
                            foreach($path as $transformation){
                                $file = $file->transform($transformation);
                            }
                        }
                        if(is_object($file)) {
                            foreach ($props as $prop) {
                                $result[$prop] = $file->$prop;
                            }
                        }

                        return $result;
                    };

                    $append_files_cb = function(&$result, $entity) use($cfg, $extract_data_cb){

                        $files = $entity->files;

                        foreach($cfg['files'] as $im){
                            $im = trim($im);

                            list($igroup, $ipath) = explode('.',$im, 2) + [null,null];

                            if(!key_exists($igroup, $files))
                                continue;

                            if(is_array($files[$igroup])){
                                $result["@files:$im"] = [];
                                foreach($files[$igroup] as $file)
                                    $result["@files:$im"][] = $extract_data_cb($file,$ipath,$cfg['props']);
                            }else{
                                $result["@files:$im"] = $extract_data_cb($files[$igroup],$ipath,$cfg['props']);
                            }

                        }
                    };
                    continue;
                }

                if($key === 'user' && $class::usesOwnerAgent()){
                    $dql_joins .= ' LEFT JOIN e.owner __user_agent__';
                    $keys[$key] = '__user_agent__.user';

                }elseif(key_exists($key, $entity_associations) && $entity_associations[$key]['isOwningSide']){
                    $keys[$key] = 'e.'.$key;

                }elseif(in_array($key, $entity_properties)){
                    $keys[$key] = 'e.'.$key;

                }elseif($class::usesTypes() && $key === 'type'){
                    $keys[$key] = 'e._type';

                }elseif($class::usesTaxonomies() && in_array($key, $taxonomies)){
                    $taxo_num++;
                    $tr_alias = "tr{$taxo_num}";
                    $t_alias = "t{$taxo_num}";
                    $taxonomy_id = $taxonomies_slugs[$key];

                    $keys[$key] = "$t_alias.term";
                    $dql_joins .= str_replace('{ALIAS_TR}', $tr_alias, str_replace('{ALIAS_T}', $t_alias, str_replace('{TAXO}', $taxonomy_id, $dql_join_term_template)));

                }elseif($class::usesMetadata() && in_array($key, $entity_metadata)){
                    $meta_num++;
                    $meta_alias = "m{$meta_num}";
                    $keys[$key] = "$meta_alias.value";
                    $dql_joins .= str_replace('{ALIAS}', $meta_alias, str_replace('{KEY}', $key, $dql_join_template));

                }elseif($key[0] != '_' && $key != 'callback'){
                    $this->apiErrorResponse ("property $key does not exists");

                }else{
                    continue;
                }
                $dqls[] = $this->_API_find_parseParam($keys[$key], $val);
            }

            // seals joins

            if($seals){
//                $_seals = $this->_API_find_addValueToParamList($seals);
                $seals = implode(',', $seals);
                $dql_joins .= "LEFT JOIN e.__sealRelations __sr";
                $dqls[] = $this->_API_find_parseParam('__sr.seal', "IN($seals)");
            }

            if($order){
                $new_order = [];
                foreach(explode(',',$order) as $prop){
                    $key = trim(preg_replace('#asc|desc#i', '', $prop));
                    if(key_exists($key, $keys)){
                        $new_order[] = str_ireplace($key, $keys[$key], $prop);
                    }elseif(in_array($key, $entity_properties)){
                        $new_order[] = str_ireplace($key, 'e.'.$key, $prop);

                    }elseif(in_array($key, $entity_metadata)){
                        $meta_num++;
                        $meta_alias = "m{$meta_num}";
                        $dql_joins .= str_replace('{ALIAS}', $meta_alias, str_replace('{KEY}', $key, $dql_join_template));

                        $new_order[] = str_replace($key, "$meta_alias.value", $prop);
                    }
                }
                $order = "ORDER BY " . implode(', ', $new_order);
            }

            $dql_where = implode($op, $dqls);



            if($metadata_class)
                $metadata_class = ", $metadata_class m";


            if($keyword){
                $repo = $this->repo();
                if($repo->usesKeyword()){
                    $ids = implode(',',$repo->getIdsByKeyword($keyword));
                    if($dql_where){
                        $dql_where .= $ids ? " AND e.id IN($ids)" : 'AND e.id < 0';
                    } else {
                        $dql_where .= $ids ? "e.id IN($ids)" : 'e.id < 0';
                    }
                }
            }

            if($select_metadata){
                $dql_select[] = ', meta';
                $meta_keys = implode("', '", $select_metadata);
                $dql_select_joins[] = "LEFT JOIN e.__metadata meta WITH meta.key IN ('$meta_keys')";
            }

            if(in_array('terms', $select)){
                $dql_select[] = ', termRelations, term';
                $dql_select_joins[] = "
                        LEFT JOIN e.__termRelations termRelations
                        LEFT JOIN termRelations.term term";
            }

            if(in_array('owner', $select) || array_filter($select, function($prop){ return substr($prop, 0, 6) == 'owner.'; })){
                $dql_select[] = ', _owner';
                $dql_select_joins[] = "LEFT JOIN e.owner _owner";
            }

            $select_properties = implode(',',array_unique($select_properties));


            if(in_array('type', $select)){
                $select_properties .= ',_type';
            }

            $status_where = is_array($permissions) && in_array('view', $permissions) ? 'e.status >= 0' : 'e.status > 0';
            $dql_where = $dql_where ? "{$dql_where} AND {$status_where}" : "{$status_where}";

            $app->applyHookBoundTo($this, "API.{$this->action}({$this->id}).query", [&$qdata, &$select_properties, &$dql_joins, &$dql_where]);

            $dql_where = "WHERE $dql_where";
            
            if($profiles){
                $dql_joins .= " JOIN e.user __profile_user JOIN __profile_user.profile __profile WITH __profile.id = e.id";
            }

            $final_dql = "
                SELECT
                    e
                FROM
                    $class e
                $dql_joins

                $dql_where

               $order";


            $final_dql_subqueries = preg_replace('#([^a-z0-9_])e\.#i', '$1original_e.', "SELECT
                    e.id
                FROM
                    $class original_e
                $dql_joins

                $dql_where");


            $result[] = "$final_dql";

            if($app->config['app.log.apiDql'])
                $app->log->debug("API DQL: ".$final_dql);

            $query = $app->em->createQuery($final_dql);

            if($app->user->is('superAdmin') && isset($_GET['@debug'])){
                if(isset($_GET['@type']) && $_GET['@type'] == 'html') {
                    echo '<pre style="color:red">';
                }

                echo "\nDQL Query:\n";
                echo "$final_dql\n\n";

                echo "DQL params: ";
                print_r($this->_apiFindParamList);

                echo "\n\nSQL Query\n";
                echo "\n{$query->getSQL()}\n\n";
            }

            // cache
            if($app->config['app.useApiCache'] && $this->getApiCacheLifetime()){
                $query->useResultCache(true, $this->getApiCacheLifetime());
            }

            $query->setParameters($this->_apiFindParamList);

            $sub_queries = function($rs) use($counting, $app, $class, $dql_select, $dql_select_joins, $final_dql_subqueries){
                if($counting){
                    return;
                }

                foreach($dql_select as $i => $_select){
                    $_join = $dql_select_joins[$i];

                    $dql = "
                        SELECT
                            e $_select
                        FROM
                            $class e $_join
                        WHERE
                            e.id IN($final_dql_subqueries)
                    ";

                    $q = $app->em->createQuery($dql);
                    $q->setParameters($this->_apiFindParamList);

                    if($app->config['app.log.apiDql'])
                        $app->log->debug("====================================== SUB QUERY =======================================\n\n: ".$dql);

                    $rs = $q->getResult();
                }
            };

            $processEntity = function($r) use($append_files_cb, $select){

                $entity = [];
                $append_files_cb($entity, $r);
                foreach($select as $i=> $prop){
                    $prop = trim($prop);
                    try{
                        if(strpos($prop, '.')){
                            $props = explode('.',$prop);
                            $current_object = $r;
                            foreach($props as $pk => $p){
                                if($p === 'permissionTo' && $pk === count($props) - 2){
                                    $current_object = $current_object->canUser($props[$pk + 1]);
                                    break;
                                }else{
                                    $current_object = $current_object->$p;

                                    if(!is_object($current_object))
                                        break;

                                }
                            }

                            $prop_value = $current_object;
                        }else{
                            $prop_value = $r->$prop;
                        }
                        if(is_object($prop_value) && $prop_value instanceof \Doctrine\Common\Collections\Collection)
                            $prop_value = $prop_value->toArray();

                        if(strpos($prop, '.')){
                            $props = explode('.',$prop);
                            $carray =& $entity;
                            for($i = 0; $i < count($props) -1; $i++){
                                $p = $props[$i];
                                if(!isset($carray[$p]))
                                    $carray[$p] = [];
                                $carray =& $carray[$p];
                            }
                            $carray[array_pop($props)] = $prop_value;
                        }else{
                            $entity[$prop] = $prop_value;
                        }
                    }  catch (\Exception $e){ }
                }
                return $entity;
            };
            
            if($findOne){
                $hook_action = 'findOne';
            } else {
                $hook_action = 'find';
            }

            if($findOne){
                $query->setFirstResult(0)
                      ->setMaxResults(1);

                $paginator = new Paginator($query, $fetchJoinCollection = true);
                $entity = null;

                if(is_array($paginator) && count($paginator)){
                    $r = $paginator->getIterator()->current();

                    if($permissions){
                        foreach($permissions as $perm){
                            $perm = trim($perm);
                            if($perm[0] === '!'){
                                if($r->canUser(substr($perm, 1))){
                                    $r = null;
                                    break;
                                }
                            }else{
                                if(!$r->canUser($perm)){
                                    $r = null;
                                    break;
                                }
                            }
                        }
                    }

                    if($r)
                        $entity = $processEntity($r);
                }
                $app->applyHookBoundTo($this, "API.{$hook_action}({$this->id}).result", [$__original_query_data, &$entity, $hook_action]);

                return $entity;
            }else{

                if($permissions){
                    $rs = $query->getResult();

                    $result = [];

                    $rs = array_values(array_filter($rs, function($entity) use($permissions){
                        foreach($permissions as $perm){
                            $perm = trim($perm);
                            if($perm[0] === '!'){
                                $not = true;
                                $_perm = substr($perm,1);
                            }else{
                                $not = false;
                                $_perm = $perm;
                            }
                            $can = $entity->canUser($_perm);
                            return $not ? !$can : $can;
                        }

                        return true;
                    }));

                    if(!$page){
                        $page = 1;
                    }

                    $rs_count = count($rs);

                    if($page && $limit){
                        $offset = (($page - 1) * $limit);
                        $rs = array_slice($rs, $offset, $limit);
                    }
                    $sub_queries($rs);

                }else if($limit){
                    if(!$page){
                        $page = 1;
                    }

                    $offset = ($page - 1) * $limit;

                    $query->setFirstResult($offset)
                          ->setMaxResults($limit);

                    $paginator = new Paginator($query, $fetchJoinCollection = true);

                    $rs_count = $paginator->count();

                    $rs = $paginator->getIterator()->getArrayCopy();


                    $sub_queries($rs);
                }else{
                    if($counting){
                        $rs = $query->getArrayResult();
                    } else {
                        $rs = $query->getResult();

                    }

                    $sub_queries($rs);

                    $rs_count = count($rs);
                }


                if ($counting) {
                    $app->applyHookBoundTo($this, "API.{$hook_action}({$this->id}).result", [$__original_query_data, &$rs_count, $hook_action]);
                    return $rs_count;
                }

                $this->apiAddHeaderMetadata($qdata, $rs, $rs_count);


                $result = array_map(function($entity) use ($processEntity){
                    return $processEntity($entity);
                }, $rs);
                
                $app->applyHookBoundTo($this, "API.{$hook_action}({$this->id}).result", [$__original_query_data, &$result, $hook_action]);

                return $result;
            }
        }
    }


    protected function _API_find_parseParam($key, $expression){

        if(is_string($expression) && !preg_match('#^[ ]*(!)?([a-z]+)[ ]*\((.*)\)$#i', $expression, $match)){
            $this->apiErrorResponse('invalid expression: '. ">>$expression<<");
        }else{
            $dql = '';

            $not = $match[1];
            $operator = strtoupper($match[2]);
            $value = $match[3];

            if($operator == 'OR' || $operator == 'AND'){
                $expressions = $this->_API_find_parseExpression($value);

                foreach($expressions as $expression){
                    $sub_dql = $this->_API_find_parseParam($key, $expression);
                    $dql .= $dql ? " $operator $sub_dql" : "($sub_dql";
                }
                if($dql) $dql .= ')';

            }elseif($operator == "IN"){
                $values = $this->_API_find_splitParam($value);

                $values = $this->_API_find_addValueToParamList($values);

                if(is_array($values) && count($values) < 1){
                    $this->apiErrorResponse ('expression IN expects at last one value');
                }

                $dql = $not ? "$key NOT IN (" : "$key IN (";
                $dql .= implode(', ', $values) . ')';

            }elseif($operator == "IIN"){
                $values = $this->_API_find_splitParam($value);

                $values = $this->_API_find_addValueToParamList($values);

                $values = array_map(function($e) use ($key, $not) {
                    if($not){
                        return "unaccent(lower($key)) != unaccent(lower($e))";
                    }else{
                        return "unaccent(lower($key)) = unaccent(lower($e))";
                    }
                } , $values);

                if(is_array($values) && count($values) < 1)
                    $this->apiErrorResponse ('expression IN expects at last one value');

                $dql = "\n(\n\t" . ($not ? implode("\n\t AND ", $values) : implode("\n\t OR ", $values) ) . "\n)";


            }elseif($operator == "BET"){
                $values = $this->_API_find_splitParam($value);

                if(is_array($values) && count($values) !== 2)
                    $this->apiErrorResponse ('expression BET expects 2 arguments');

                elseif($values[0][0] === '@' || $values[1][0] === '@')
                    $this->apiErrorResponse ('expression BET expects 2 string or integer arguments');

                $values = $this->_API_find_addValueToParamList($values);

                $dql = $not ?
                        "$key NOT BETWEEN {$values[0]} AND {$values[1]}" :
                        "$key BETWEEN {$values[0]} AND {$values[1]}";

            }elseif($operator == "LIKE"){
                $value = str_replace('*', '%', $value);
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "unaccent($key) NOT LIKE unaccent($value)" :
                        "unaccent($key) LIKE unaccent($value)";

            }elseif($operator == "ILIKE"){
                $value = str_replace('*', '%', $value);
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "unaccent(lower($key)) NOT LIKE unaccent(lower($value))" :
                        "unaccent(lower($key)) LIKE unaccent(lower($value))";

            }elseif($operator == "EQ"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key <> $value" :
                        "$key = $value";

            }elseif($operator == "GT"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key <= $value" :
                        "$key > $value";

            }elseif($operator == "GTE"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key < $value" :
                        "$key >= $value";

            }elseif($operator == "LT"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key >= $value" :
                        "$key < $value";

            }elseif($operator == "LTE"){
                $value = $this->_API_find_addValueToParamList($value);
                $dql = $not ?
                        "$key > $value" :
                        "$key <= $value";

            }elseif($operator == 'NULL'){
                $dql = $not ?
                        "($key IS NOT NULL)" :
                        "($key IS NULL)";

            }elseif($operator == 'GEONEAR'){
                $values = $this->_API_find_splitParam($value);

                if(is_array($values) && count($values) !== 3)
                    $this->apiErrorResponse ('expression GEONEAR expects 3 arguments: longitude, latitude and radius in meters');

                list($longitude, $latitude, $radius) = $this->_API_find_addValueToParamList($values);


                $dql = $not ?
                        "ST_DWithin($key, ST_MakePoint($longitude,$latitude), $radius) <> TRUE" :
                        "ST_DWithin($key, ST_MakePoint($longitude,$latitude), $radius) = TRUE";
            }

            /*
             * location=GEO_NEAR([long,lat]) //
             */
            return $dql;
        }
    }

    private function _API_find_addValueToParamList($value){
        if(is_array($value)){
            $result = [];
            foreach($value as $val)
                $result[] = $this->_API_find_addSigleValueToParamList($val);
        }else{
            $result = $this->_API_find_addSigleValueToParamList($value);
        }

        return $result;
    }

    private function _API_find_addSigleValueToParamList($value){
        $app = App::i();
        if(trim($value) === '@me'){
            $value = $app->user->is('guest') ? null : $app->user;
        }elseif(strpos($value,'@me.') === 0){
            $v = str_replace('@me.', '', $value);
            $value = $app->user->$v;
        }elseif(trim($value) === '@profile'){
            $value = $app->user->profile ? $app->user->profile : null;

        }elseif(preg_match('#@(\w+)[ ]*:[ ]*(\d+)#i', trim($value), $matches)){
            $_repo = $app->repo($matches[1]);
            $_id = $matches[2];

            $value = ($_repo && $_id) ? $_repo->find($_id) : null;

        }elseif(strlen($value) && $value[0] == '@'){
            $value = null;
        }

        $uid = uniqid('v');
        $this->_apiFindParamList[$uid] = $value;
        $result = ':' . $uid;

        return $result;
    }

    private function _API_find_splitParam($val){
        $result = explode("\n",str_replace('\\,', ',', preg_replace('#(^[ ]*|([^\\\]))\,#',"$1\n", $val)));

        if(is_array($result) && count($result) === 1 && !$result[0]){
            return [];
        }else{
            $_result = [];
            foreach($result as $r)
                if($r)
                    $_result[] = $r;
            return $_result;
        }
    }

    private function _API_find_parseExpression($val){

        $open = false;
        $nopen = 0;
        $counter = 0;

        $results = [];
        $last_char = '';

        foreach(str_split($val) as $index => $char){
            $next_char = strlen($val) > $index + 1 ? $val[$index + 1] : '';

            if(!key_exists($counter, $results))
                $results[$counter] = '';

            if($char !== '\\' ||  $next_char === '(' || $next_char === ')')
                if($open || $char !== ',' || $last_char === '\\')
                    $results[$counter] .= $char;

            if($char === '(' && $last_char !== '\\'){
                $open = true;
                $nopen++;
            }

            if($char === ')' && $last_char !== '\\' && $open){
                $nopen--;
                if($nopen === 0){
                    $open = false;
                    $counter++;
                }
            }

            $last_char = $char;
        }

        return $results;
    }
}