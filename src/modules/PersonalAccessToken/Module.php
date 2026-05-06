<?php

namespace PersonalAccessToken;

use MapasCulturais\App;
use MapasCulturais\i;
use PersonalAccessToken\AuthProviders\PATAuthProvider;
use PersonalAccessToken\Entities\PersonalAccessToken;

class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();

        $app->hook('mapas.printJsObject:before', function () {
            $this->jsObject['EntitiesDescription']['personal-access-token'] =
                Entities\PersonalAccessToken::getPropertiesMetadata();
        });

        $app->hook('view.render(<<*>>):before', function () use ($app) {
            $this->jsObject['EntityPermissionsList'] = $this->jsObject['EntityPermissionsList'] ?? [];
        });

        $app->hook('ApiQuery(PersonalAccessToken.Entities.PersonalAccessToken).params', function (&$params) {
            $params['user'] = 'EQ(@me)';
        });

        $app->hook('doctrine.emum(object_type).values', function (&$values) {
            $values['PersonalAccessToken'] = Entities\PersonalAccessToken::class;
        });

        $app->hook('can(<<*>>)', function ($user, &$result) use ($app) {
            if (!($app->auth instanceof PATAuthProvider)) {
                return;
            }

            $tokenEntity = $app->auth->getTokenEntity();
            if (!$tokenEntity) {
                $result = false;
                return;
            }

            $hookStack = $app->hooks->hookStack ?? [];
            if (empty($hookStack)) {
                return;
            }

            $currentHook = end($hookStack);
            $hookName = $currentHook->name ?? '';

            if (!preg_match('/^can\((.+)\)$/', $hookName, $matches)) {
                return;
            }

            $requestedPermission = $matches[1];

            $permissions = $tokenEntity->getPermissions();

            $result = $this->matchPermission($requestedPermission, $permissions);
        }, PHP_INT_MAX);
    }

    private function matchPermission(string $requested, array $allowed): bool
    {
        foreach ($allowed as $permission) {
            if ($permission === $requested) {
                return true;
            }

            $parts = explode('.', $permission);
            if (count($parts) === 2 && $parts[1] === '*') {
                $requestedParts = explode('.', $requested);
                if (count($requestedParts) === 2 && $requestedParts[0] === $parts[0]) {
                    return true;
                }
            }

            if ($permission === '*') {
                return true;
            }
        }

        return false;
    }

    function register()
    {
        $app = App::i();
        $app->registerController('personal-access-token', Controllers\PersonalAccessTokenController::class);

        $moduleDir = dirname((new \ReflectionClass($this))->getFileName());
        $subfolders = ['Entities', 'Controllers', 'Repositories', 'AuthProviders', 'Middleware', 'Jobs'];
        foreach ($subfolders as $sub) {
            $ns = __NAMESPACE__ . '\\' . $sub;
            if (!isset($app->config['namespaces'][$ns])) {
                $app->config['namespaces'][$ns] = $moduleDir . '/' . $sub;
            }
        }
    }
}
