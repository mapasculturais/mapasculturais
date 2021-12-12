<?php

namespace SystemRoles;

use MapasCulturais\App;
use MapasCulturais\Definitions\Role;

class Module extends \MapasCulturais\Module {
    function _init() {
        $app = App::i();
        $app->hook('doctrine.emum(object_type).values', function(&$values) {
            $values['SystemRole'] = Entities\SystemRole::class;
        });
    }

    function register() {
        $app = App::i();

        $app->registerController('system-role', Controllers\SystemRole::class);

        $roles = $app->repo(Entities\SystemRole::class)->findBy(['status' => 1]);
        if (php_sapi_name() !== "cli") {

            foreach($roles as $role) {
                $definition = new Role($role->slug, $role->name, $role->name, $role->subsiteContext, function ($user) {
                    return $user->is('saasAdmin');
                });

                $app->registerRole($definition);

                foreach ($role->permissions as $permission) {
                    $app->hook("can($permission)", function ($user, &$result) use ($role) {
                        if($user->is($role->slug)) {
                            $result = true;
                        }
                    });
                }
            }
        }
    }
}
