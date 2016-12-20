<?php

namespace MapasCulturais;

class ApiQuery {

    use Traits\MagicGetter;

    protected $controller;
    protected $className;
    public $select = "";
    public $joins = "";
    public $where = "";
    protected $params = [];
    protected $queryParams = [];
    
    protected $_select = [];
    protected $_selectProperties = [];
    protected $_selectMetadata = [];
    
    protected $_subqueriesSelect = [];

    public function __construct(Controllers\EntityController $controller, $query_params) {
        $this->queryParams = $params;
        $this->controller = $controller;
        $this->className = $controller->entityClassName;

        $this->parseQueryParams();
    }

    public function getFindDQL() {
        $dql = "SELECT\n\t{$this->select}\nFROM {$this->className} e {$this->joins}";
        if ($this->where) {
            $dql .= "\nWHERE\n\t{$this->where}";
        }

        return $dql;
    }

    public function getCountDQL() {
        $dql = "SELECT\n\tCOUNT(e.id)\nFROM {$this->className} e {$this->joins}";
        if ($this->where) {
            $dql .= "\nWHERE\n\t{$this->where}";
        }

        return $dql;
    }

    public function getSubDQL($prop = 'id') {
        // @TODO: se estiver paginando, rodar a consulta pegando somente os ids e retornar uma lista de ids 
        $alias = 'e_' . uniqid();
        $dql = "SELECT\n\t{$alias}.{$prop}\nFROM {$this->className} {$alias} {$this->joins}";
        if ($this->where) {
            $dql .= "\nWHERE\n\t{$this->where}";
        }

        return preg_replace('#([^a-z0-9_])e\.#i', "{$alias}.", $dql);
    }

    public function addMultipleParams(array $values) {
        $result = [];
        foreach ($values as $value) {
            $result[] = $this->addSingleParams($values);
        }

        return $result;
    }

    public function addSingleParam($values) {
        $app = App::i();
        if (trim($value) === '@me') {
            $value = $app->user->is('guest') ? null : $app->user;
        } elseif (strpos($value, '@me.') === 0) {
            $v = str_replace('@me.', '', $value);
            $value = $app->user->$v;
        } elseif (trim($value) === '@profile') {
            $value = $app->user->profile ? $app->user->profile : null;
        } elseif (preg_match('#@(\w+)[ ]*:[ ]*(\d+)#i', trim($value), $matches)) {
            $_repo = $app->repo($matches[1]);
            $_id = $matches[2];

            $value = ($_repo && $_id) ? $_repo->find($_id) : null;
        } elseif (strlen($value) && $value[0] == '@') {
            $value = null;
        }

        $uid = uniqid('v');
        $this->params[$uid] = $value;

        $result = ':' . $uid;

        return $result;
    }

    protected function parseParam($key, $expression) {
        if (is_string($expression) && !preg_match('#^[ ]*(!)?([a-z]+)[ ]*\((.*)\)$#i', $expression, $match)) {
            throw new Exceptions\Api\InvalidExpression($expression);
        } else {
            $dql = '';

            $not = $match[1];
            $operator = strtoupper($match[2]);
            $value = $match[3];

            if ($operator == 'OR' || $operator == 'AND') {
                $expressions = $this->parseExpression($value);

                foreach ($expressions as $expression) {
                    $sub_dql = $this->parseParam($key, $expression);
                    $dql .= $dql ? " $operator $sub_dql" : "($sub_dql";
                }
                if ($dql) {
                    $dql .= ')';
                }
            } elseif ($operator == "IN") {
                $values = $this->splitParam($value);

                $values = $this->addSingleParam($values);

                if (count($values) < 1) {
                    throw new Exceptions\Api\InvalidArgument('expression IN expects at last one value');
                }

                $dql = $not ? "$key NOT IN (" : "$key IN (";
                $dql .= implode(', ', $values) . ')';
            } elseif ($operator == "BET") {
                $values = $this->splitParam($value);

                if (count($values) !== 2) {
                    throw new Exceptions\Api\InvalidArgument('expression BET expects 2 arguments');
                } elseif ($values[0][0] === '@' || $values[1][0] === '@') {
                    throw new Exceptions\Api\InvalidArgument('expression BET expects 2 string or integer arguments');
                }

                $values = $this->addSingleParam($values);

                $dql = $not ?
                        "$key NOT BETWEEN {$values[0]} AND {$values[1]}" :
                        "$key BETWEEN {$values[0]} AND {$values[1]}";
            } elseif ($operator == "LIKE") {
                $value = str_replace('*', '%', $value);
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "unaccent($key) NOT LIKE unaccent($value)" :
                        "unaccent($key) LIKE unaccent($value)";
            } elseif ($operator == "ILIKE") {
                $value = str_replace('*', '%', $value);
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "unaccent(lower($key)) NOT LIKE unaccent(lower($value))" :
                        "unaccent(lower($key)) LIKE unaccent(lower($value))";
            } elseif ($operator == "EQ") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key <> $value" :
                        "$key = $value";
            } elseif ($operator == "GT") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key <= $value" :
                        "$key > $value";
            } elseif ($operator == "GTE") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key < $value" :
                        "$key >= $value";
            } elseif ($operator == "LT") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key >= $value" :
                        "$key < $value";
            } elseif ($operator == "LTE") {
                $value = $this->addSingleParam($value);
                $dql = $not ?
                        "$key > $value" :
                        "$key <= $value";
            } elseif ($operator == 'NULL') {
                $dql = $not ?
                        "($key IS NOT NULL)" :
                        "($key IS NULL)";
            } elseif ($operator == 'GEONEAR') {
                $values = $this->splitParam($value);

                if (count($values) !== 3) {
                    throw new Exceptions\Api\InvalidArgument('expression GEONEAR expects 3 arguments: longitude, latitude and radius in meters');
                }

                list($longitude, $latitude, $radius) = $this->addSingleParam($values);


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

    private function splitParam($val) {
        $result = explode("\n", str_replace('\\,', ',', preg_replace('#(^[ ]*|([^\\\]))\,#', "$1\n", $val)));

        if (count($result) === 1 && !$result[0]) {
            return [];
        } else {
            $_result = [];
            foreach ($result as $r)
                if ($r)
                    $_result[] = $r;
            return $_result;
        }
    }

    protected function parseExpression($val) {

        $open = false;
        $nopen = 0;
        $counter = 0;

        $results = [];
        $last_char = '';

        foreach (str_split($val) as $index => $char) {
            $next_char = strlen($val) > $index + 1 ? $val[$index + 1] : '';

            if (!key_exists($counter, $results))
                $results[$counter] = '';

            if ($char !== '\\' || $next_char === '(' || $next_char === ')')
                if ($open || $char !== ',' || $last_char === '\\')
                    $results[$counter] .= $char;

            if ($char === '(' && $last_char !== '\\') {
                $open = true;
                $nopen++;
            }

            if ($char === ')' && $last_char !== '\\' && $open) {
                $nopen--;
                if ($nopen === 0) {
                    $open = false;
                    $counter++;
                }
            }

            $last_char = $char;
        }

        return $results;
    }
    
    
    
    protected function parseQueryParams() {
        foreach ($this->queryParams as $key => $val) {
            $val = trim($val);
            if (strtolower($key) == '@select') {
                $this->_parseSelect($val);
                continue;
                
                
            } elseif (strtolower($key) == '@order') {
                $order = $val;
                continue;
            } elseif (strtolower($key) == '@offset') {
                $offset = $val;
                continue;
            } elseif (strtolower($key) == '@page') {
                $page = $val;
                continue;
            } elseif (strtolower($key) == '@limit') {
                $limit = $val;
                continue;
                
                
            } elseif (strtolower($key) == '@keyword') {
                $keyword = $val;
                continue;
                
                
            } elseif (strtolower($key) == '@permissions') {
                $permissions = explode(',', $val);
                continue;
            } elseif (strtolower($key) == '@seals') {
                $seals = explode(',', $val);
                continue;
            } elseif (strtolower($key) == '@verified') {
                $seals = $app->config['app.verifiedSealsIds'];
                continue;
                
                
            } elseif (strtolower($key) == '@or') {
                $op = ' OR ';
                continue;
            } elseif (strtolower($key) == '@type') {
                continue;
            } elseif (strtolower($key) == '@files' && preg_match('#^\(([\w\., ]+)\)[ ]*(:[ ]*([\w, ]+))?#i', $val, $imatch)) {

                if ($counting)
                    continue;
                // example:
                // @files=(avatar.smallAvatar,header.header):name,url

                $cfg = [
                    'files' => explode(',', $imatch[1]),
                    'props' => key_exists(3, $imatch) ? explode(',', $imatch[3]) : ['url']
                ];

                $_join_in = [];

                foreach ($cfg['files'] as $_f) {
                    if (strpos($_f, '.') > 0) {
                        list($_f_group, $_f_transformation) = explode('.', $_f);
                        $_join_in[] = $_f_group;
                        $_join_in[] = 'img:' . $_f_transformation;
                    } else {
                        $_join_in[] = $_f;
                    }
                }

                $_join_in = array_unique($_join_in);

                $dql_select[] = ", files, fparent";
                $dql_select_joins[] = "
                        LEFT JOIN e.__files files WITH files.group IN ('" . implode("','", $_join_in) . "')
                        LEFT JOIN files.parent fparent";

                $extract_data_cb = function($file, $ipath, $props) {
                    $result = [];
                    if ($ipath) {
                        $path = explode('.', $ipath);
                        foreach ($path as $transformation) {
                            $file = $file->transform($transformation);
                        }
                    }
                    if (is_object($file)) {
                        foreach ($props as $prop) {
                            $result[$prop] = $file->$prop;
                        }
                    }

                    return $result;
                };

                $append_files_cb = function(&$result, $entity) use($cfg, $extract_data_cb) {

                    $files = $entity->files;

                    foreach ($cfg['files'] as $im) {
                        $im = trim($im);

                        list($igroup, $ipath) = explode('.', $im, 2) + [null, null];

                        if (!key_exists($igroup, $files))
                            continue;

                        if (is_array($files[$igroup])) {
                            $result["@files:$im"] = [];
                            foreach ($files[$igroup] as $file)
                                $result["@files:$im"][] = $extract_data_cb($file, $ipath, $cfg['props']);
                        } else {
                            $result["@files:$im"] = $extract_data_cb($files[$igroup], $ipath, $cfg['props']);
                        }
                    }
                };
                continue;
            }

            if ($key === 'user' && $class::usesOwnerAgent()) {
                $dql_joins .= ' LEFT JOIN e.owner __user_agent__';
                $keys[$key] = '__user_agent__.user';
            } elseif (key_exists($key, $entity_associations) && $entity_associations[$key]['isOwningSide']) {
                $keys[$key] = 'e.' . $key;
            } elseif (in_array($key, $entity_properties)) {
                $keys[$key] = 'e.' . $key;
            } elseif ($class::usesTypes() && $key === 'type') {
                $keys[$key] = 'e._type';
            } elseif ($class::usesTaxonomies() && in_array($key, $taxonomies)) {
                $taxo_num++;
                $tr_alias = "tr{$taxo_num}";
                $t_alias = "t{$taxo_num}";
                $taxonomy_id = $taxonomies_ids[$key];

                $keys[$key] = "$t_alias.term";
                $dql_joins .= str_replace('{ALIAS_TR}', $tr_alias, str_replace('{ALIAS_T}', $t_alias, str_replace('{TAXO}', $taxonomy_id, $dql_join_term_template)));
            } elseif ($class::usesMetadata() && in_array($key, $entity_metadata)) {
                $meta_num++;
                $meta_alias = "m{$meta_num}";
                $keys[$key] = "$meta_alias.value";
                $dql_joins .= str_replace('{ALIAS}', $meta_alias, str_replace('{KEY}', $key, $dql_join_template));
            } elseif ($key[0] != '_' && $key != 'callback') {
                $this->apiErrorResponse("property $key does not exists");
            } else {
                continue;
            }
            $dqls[] = $this->_API_find_parseParam($keys[$key], $val);
        }
    }

    protected function _parseSelect($select) {
        $select = str_replace(' ', '', $select);
        
        // create subquery to format entity.* or entity.{id,name}
        while(preg_match('#([^,\.]+)\.(\{[^\{\}]+\})#', $select, $matches)){
            $_subquery_entity_class = $matches[1];
            $_subquery_select = substr($matches[2],1,-1);
            
            $replacement = $this->_preCreateSelectSubquery($_subquery_entity_class, $_subquery_select);
            
            $select = str_replace($matches[0], $replacement, $select);
        }
        
        // create subquery to format entity.id or entity.name        
        while(preg_match('#([^,\.]+)\.([^,\.]+)#', $select, $matches)){
            $_subquery_entity_class = $matches[1];
            $_subquery_select = $matches[2];
            
            $replacement = $this->_preCreateSelectSubquery($_subquery_entity_class, $_subquery_select);
            
            $select = str_replace($matches[0], $replacement, $select);
        }
        
        $this->_select = explode(',', $select);

        foreach ($this->_select as $i => $prop) {
            if (in_array($prop, $this->_entityProperties)) {
                $this->_selectProperties[] = $prop;
                
            } elseif (in_array($prop, $this->_entityMetadata)) {
                $this->_selectMetadata[] = $prop;
                
            } elseif (in_array($prop, $this->_entityRelations)) {
                $this->_select[$i] = $this->_preCreateSelectSubquery($prop, 'id');
                
            } 
        }
    }
    
    protected function _preCreateSelectSubquery($entity, $select){
        $uid = uniqid('#sq:');
        
        $this->_subqueriesSelect[$uid] = [$entity, $select];
        
        return $uid;
    }

}
