<?php

use MapasCulturais\App;

App::i()->disableAccessControl();

class McCli {

    static $app = null;
    static $entityClassesShortcuts = [
        'user' => 'MapasCulturais\Entities\User',
        'agent' => 'MapasCulturais\Entities\Agent',
        'space' => 'MapasCulturais\Entities\Space',
        'project' => 'MapasCulturais\Entities\Project',
        'event' => 'MapasCulturais\Entities\Event',
        'occurrence' => 'MapasCulturais\Entities\EventOccurrence',
        'event-occurrence' => 'MapasCulturais\Entities\EventOccurrence',
    ];

    static function exec($_params) {
        $keys = [];
        $params = [];
        foreach ($_params as $param) {
            @list($key, $value) = explode('=', $param, 2);
            $keys[] = $key;
            $params[$key] = $value;
        }


        if (count($keys) === 0) {
            $keys = ['help'];
        }

        $command = $keys[0];
        
        if (method_exists(__CLASS__, 'CLI_' . $command)) {
            $params = array_slice($params, 1);
            $keys = array_slice($keys, 1);
            self::{'CLI_' . $command}($keys, $params);
        }
    }

    static $commands = [
        'help' => [
            'description' => 'print this message'
        ],
        'entities' => [
            'description' => 'list entities shortcuts to use with mc-cli commands'
        ],
        'view' => [
            'description' => '',
            'syntax' => 'view {$entity}="API QUERY" [select="id,name,etc"]',
            'examples' => [
                'view user="id=EQ(1)"',
                'view agent="id=BET(10,99)"'
            ]
        ],
        'delete' => [
            'description' => 'delete an entity',
            'syntax' => 'delete {$entity}="API QUERY"',
            'examples' => [
                'delete user="email=ILIKE(*@gmail.com)"' => 'delete users that email ends with gmail',
                'delete agent="type=EQ(2)"' => 'delete agents of type 2'
            ]
        ],
        'role' => [
            'description' => 'add a role to an user',
            'examples' => [
                'role add="superAdmin" user="id=EQ(1)"',
                'role add="superAdmin" user="email=EQ(admin@foo.com)"',
            ]
        ]
    ];

    static function CLI_help($keys, $params) {
        if (count($keys) === 0) {
            foreach (self::$commands as $command => $def) {
                $description = $def['description'];
                echo "\n{$command}:\n\t$description\n\n";
            }
        } elseif (count($keys) === 1 && isset(self::$commands[$keys[0]])) {
            print_r(self::$commands[$keys[0]]);
        }
    }

    static function CLI_entities($keys, $params) {
        foreach (self::$entityClassesShortcuts as $class => $shortcuts) {
            echo "\n {$class}:\n";
            foreach ($shortcuts as $key => $value) {
                echo "\t{$value}\n";
            }
        }
    }

    static function getEntity($params) {

        foreach (array_keys(self::$entityClassesShortcuts) as $entity) {
            if (array_key_exists($entity, $params)) {
                return $entity;
            }
        }
    }

    static function getEntityApiQuery($entity, $params) {
        $result = [];
        if (isset($params[$entity])) {
            parse_str($params[$entity], $result);
        }
        if (isset($params['select'])) {
            $result['@select'] = $params['select'];
        }
        return $result;
    }

    static function printNumResults($result, $text) {
        $num = count($result);
        echo "\n\n==============================================\n\t{$num} entities {$text} \n==============================================\n";
    }

    static function CLI_view($keys, $params, $return = false) {
        $entity = self::getEntity($params);
        $q = self::getEntityApiQuery($entity, $params);
        $controller = new McController($entity, self::$entityClassesShortcuts[$entity]);

        $result = $controller->apiQuery($q);
        if ($return) {
            return $result;
        }
        echo json_encode($result, JSON_PRETTY_PRINT);

        self::printNumResults($result, 'found');
    }

    static function CLI_delete($keys, $params) {
        $entity = self::getEntity($params);
        $class = self::$entityClassesShortcuts[$entity];

        $params['select'] = 'id';
        $q = self::getEntityApiQuery($entity, $params);
        $controller = new McController($entity, $class);

        $result = $controller->apiQuery($q);
        $repo = App::i()->repo($class);
        foreach ($result as $r) {
            $e = $repo->find($r['id']);
            $e->refresh();

            if ($e->name) {
                echo "\nDELETING {$entity} (id: {$e->id}) \"{$e->name}\" ...\n";
            } else {
                echo "\nDELETING {$entity} (id: {$e->id})\n";
            }

            $e->delete(true);
        }

        self::printNumResults($result, 'deleted');
    }

    static function CLI_role($keys, $params) {
// mc role add=superAdmin user="id=EQ(1)"
// mc role remove=staff user="email=LIKE(@hacklab.com.br)"
// mc role list=admin
        $app = App::i();
        
        $get_users = function() use($params, $app ){
            $_ids = self::CLI_view(['user', 'select'], ['user' => $params['user'], 'select' => 'id,email,roles'], true);
            $ids = array_map(function($id) {
                return $id['id'];
            }, $_ids);
            $users = [];
            $app->em->clear();
            foreach($ids as $id){
                $user = $app->repo('User')->find($id);
                $user->refresh();
                $users[] = $user;
            }
            
            return $users;
        };

        switch ($keys[0]) {
            case 'add':
                $role = $params['add'];
                $users = $get_users();
                foreach($users as $user){
                    echo "adding role $role to user (id: $user->id, email: $user->email)... \n\n";
                    $user->addRole($role);
                }
                self::printNumResults($users, 'affected');
                break;

            case 'remove':
                $role = $params['remove'];
                $users = $get_users();
                foreach($users as $user){
                    echo "removing role $role of user (id: $user->id, email: $user->email)... \n\n";
                    $user->removeRole($role);
                }
                self::printNumResults($users, 'affected');
                break;

            case 'list':
                $role = $params['list'];
                $roles = $app->repo('Role')->findBy(['name' => $role]);
                
                $ids = array_map(function($e) {
                    return $e->user->id;
                }, $roles);
                
                if($ids){
                    self::CLI_view(['user', 'select'], ['user' => 'id=IN(' . implode(',',$ids) . ')', 'select' => 'id,authUid,email,profile.id,profile.name']);
                }else{
                    self::printNumResults([], 'found');
                }
                break;
        }
    }

}

class McController {

    use \MapasCulturais\Traits\ControllerAPI,
        \MapasCulturais\Traits\MagicGetter,
        \MapasCulturais\Traits\MagicCallers;

    public $id;
    public $entityClassName;

    public function __construct($controllerId, $entityClassName) {
        $this->id = $controllerId;
        $this->entityClassName = $entityClassName;
    }

}
