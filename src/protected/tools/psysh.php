<?php
require __DIR__ . '/../application/bootstrap.php';

$em = $app->em;

function mb_str_pad($str, $pad_len, $pad_str = ' ', $dir = STR_PAD_RIGHT, $encoding = NULL)
{
    $encoding = $encoding === NULL ? mb_internal_encoding() : $encoding;
    $padBefore = $dir === STR_PAD_BOTH || $dir === STR_PAD_LEFT;
    $padAfter = $dir === STR_PAD_BOTH || $dir === STR_PAD_RIGHT;
    $pad_len -= mb_strlen($str, $encoding);
    $targetLen = $padBefore && $padAfter ? $pad_len / 2 : $pad_len;
    $strToRepeatLen = mb_strlen($pad_str, $encoding);
    $repeatTimes = ceil($targetLen / $strToRepeatLen);
    $repeatedString = str_repeat($pad_str, max(0, $repeatTimes)); // safe if used with valid utf-8 strings
    $before = $padBefore ? mb_substr($repeatedString, 0, floor($targetLen), $encoding) : '';
    $after = $padAfter ? mb_substr($repeatedString, 0, ceil($targetLen), $encoding) : '';
    return $before . $str . $after;
}

function _array_to_print($rs, $prefix = ''){
    $sizes = [];
    $result = [];
    
    foreach($rs as $_item){
        $item = [];
        if(is_array($_item)){
            foreach($_item as $key => $value){
                $key = $prefix . $key;
                if(is_array($value)){
                    $r = _array_to_print([$value], "$key.");
                    $item += $r['result'][0];
                    foreach($r['sizes'] as $mk => $size){
                        if( !isset($sizes[$mk]) || $size > $sizes[$mk]){
                            $sizes[$mk] = $size;
                        }
                    }
                } else {
                    $item[$key] = $value;
                    if(!isset($sizes[$key]) || $sizes[$key] < mb_strlen("$value")){
                        $sizes[$key] =  mb_strlen("$value") + 2;
                    }
                }
            }
            $result[] = $item;
        }
    }
    
    return ['result' => $result, 'sizes' => $sizes];
}

function print_table($rs){
    $first = true;
    
    $rs = _array_to_print($rs);
    $sum = 0;
    $line1 = [];
    $line2 = [];
    foreach($rs['sizes'] as $k => $v){
        $v = intval($v);
        if($v < mb_strlen($k) + 2){
            $v = mb_strlen($k) + 2;
            $rs['sizes'][$k] = $v;
        }
        $line1[] = str_pad($k, $v, ' ', STR_PAD_BOTH);
        $line2[] = str_pad('', $v, '-');
        $sum += $v;
    }
    echo "\n " . implode('| ', $line1);
    echo "\n " . implode('+-', $line2);
    
    foreach($rs['result'] as $item){
        $line = [];
        foreach($item as $k => $v){
            $line[] = mb_str_pad($v, $rs['sizes'][$k]);
        }
        
        echo "\n " . implode('| ', $line);
    }
}

function login($user_id){
    $app = MapasCulturais\App::i();
    $app->auth->login($user_id);
}

function api($entity, $_params, $print=true){
    if(is_string($_params)){
        parse_str($_params,$params);
    } else {
        $params = $_params;
    }
    $rs = new MapasCulturais\ApiQuery("MapasCulturais\Entities\\$entity", $params);
    if($print){
        print_table($rs->find());
    } else {
        return $rs;
    }
}

function get_user($user){
    $app = MapasCulturais\App::i();

    if($user instanceof \MapasCulturais\Entities\User){
        return $user;
    } else if(is_numeric($user)){
        return $app->repo('User')->find($user);
    } else if(is_string($user)){
        return $app->repo('User')->findOneBy(['email' => $user]);
    }
    return null;
}

class role{
    
    static function add($user, $role, $subsite_id = null){
        $app = MapasCulturais\App::i();

        if($user = get_user($user)){
            $app->disableAccessControl();
            $user->addRole($role, $subsite_id);
            $app->enableAccessControl();
        }
    }

    static function remove($user, $role, $subsite_id = null){
        $app = MapasCulturais\App::i();
        
        if($user = get_user($user)){
            $app->disableAccessControl();
            $user->removeRole($role, $subsite_id);
            $app->enableAccessControl();
        }
    }
    
    static function ls($role = null, $subsite_id = false){
        $params = ['@SELECT' => 'name,user.{id,email,profile.name},subsite.name'];
        if($role){
            $params['name'] = "ILIKE($role)";
        }
        if($subsite_id !== false){
            if(is_null($subsite_id)){
                $params['subsite'] = "NULL()";
            } else {
                $params['subsite'] = "EQ($subsite_id)";
            }
        }
        
        $rs = api('role', $params)->find();
        print_table($rs);
    }
}


echo "
================================
VARIÁVEIS DISPONÍVEIS: 
  \$app, \$em
  
para logar: login(id do usuário);

para criar uma ApiQuery: \033[33mapi(\$entity, \$params);\033[0m (exemplo: api('agent', ['@select' => 'id,name']))

para adicionar uma role a um usuário: \033[33mrole::add(\$user_id, 'roleName', \$subsite_id = null);\033[0m (exemplo role::add(1, 'saasSuperAdmin'))
para remover uma role a um usuário: \033[33mrole::remove(\$user_id, 'roleName', \$subsite_id = null);\033[0m (exemplo role::remove(1, 'saasSuperAdmin'))

";

eval(\psy\sh());
