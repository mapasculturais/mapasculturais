<?php

namespace PersonalAccessToken\Controllers;

use MapasCulturais\App;
use MapasCulturais\Controllers\EntityController;
use MapasCulturais\Traits;
use PersonalAccessToken\Entities\PersonalAccessToken;

class PersonalAccessTokenController extends EntityController
{
    use Traits\ControllerSoftDelete,
        Traits\ControllerAPI {
        Traits\ControllerAPI::API_find as __API_find;
    }

    public function __construct()
    {
        $this->entityClassName = PersonalAccessToken::class;
    }

    public function POST_index($data = null)
    {
        $app = App::i();
        $this->requireAuthentication();

        $entity = new PersonalAccessToken();
        $entity->user = $app->user;

        $postData = (array) $this->postData;
        $name = trim((string) ($postData['name'] ?? ''));
        $permissions = (array) ($postData['permissions'] ?? []);

        if (empty($name) || mb_strlen($name) < 3 || mb_strlen($name) > 255) {
            $this->errorJson('O nome deve ter entre 3 e 255 caracteres', 400);
            return;
        }

        if (!is_array($permissions) || count($permissions) === 0) {
            $this->errorJson('Ao menos uma permissão deve ser informada', 400);
            return;
        }

        $permissions = $this->sanitizePermissions($permissions);
        if (empty($permissions)) {
            $this->errorJson('Permissões inválidas', 400);
            return;
        }

        $entity->name = $name;
        $entity->permissions = $permissions;

        if (isset($postData['expiresAt']) && !empty($postData['expiresAt'])) {
            try {
                $expiresAt = new \DateTime($postData['expiresAt']);
                if ($expiresAt <= new \DateTime()) {
                    $this->errorJson('A data de expiração deve ser futura', 400);
                    return;
                }
                $entity->expiresAt = $expiresAt;
            } catch (\Exception $e) {
                $this->errorJson('Data de expiração inválida', 400);
                return;
            }
        }

        $plainTextToken = $entity->createToken();

        $entity->checkPermission('create');
        $app->em->persist($entity);
        $app->em->flush();

        $app->applyHookBoundTo($entity, 'entity(PersonalAccessToken).create:after', [$entity]);

        $response = (array) $entity->simplify('id,name,permissions,expiresAt,createTimestamp,status,tokenPrefix');
        $response['plainTextToken'] = $plainTextToken;

        $this->json($response, 201);
    }

    private function sanitizePermissions(array $permissions): array
    {
        $allowedPattern = '/^[a-z][a-z0-9\-]*\.[a-z][a-z0-9\-]*$/i';
        $wildcardPattern = '/^[a-z][a-z0-9\-]*\.\*$/i';
        $globalWildcard = '*';

        $sanitized = [];
        foreach ($permissions as $perm) {
            if (!is_string($perm)) {
                continue;
            }
            $perm = trim($perm);
            if ($perm === $globalWildcard || preg_match($allowedPattern, $perm) || preg_match($wildcardPattern, $perm)) {
                $sanitized[] = $perm;
            }
        }
        return array_unique($sanitized);
    }

    public function API_find()
    {
        $app = App::i();
        $this->data['user'] = 'EQ(@me)';
        return $this->__API_find();
    }
}
