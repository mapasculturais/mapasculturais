<?php

namespace Entities;

use MapasCulturais\App;
use MapasCulturais\Entity;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\i;

class Module extends \MapasCulturais\Module{
    private function getColumnsConfigDir(): string
    {
        return BASE_PATH . 'entity-table-columns/';
    }

    private function sanitizeColumns(array $columns): array
    {
        $result = [];
        foreach ($columns as $column) {
            if (!is_string($column)) {
                continue;
            }

            $column = trim($column);
            if ($column === '') {
                continue;
            }

            if (!preg_match('/^[a-zA-Z0-9._\\-\\[\\]\\?]+$/', $column)) {
                continue;
            }

            if (!in_array($column, $result, true)) {
                $result[] = $column;
            }
        }

        return $result;
    }

    private function writeColumnsConfig(string $tableKey, array $config): void
    {
        $dir = $this->getColumnsConfigDir();
        if (!is_dir($dir)) {
            mkdir($dir, 0775, true);
        }

        $path = $dir . $tableKey . '.json';
        $tmpPath = $path . '.tmp';
        $payload = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        file_put_contents($tmpPath, $payload, LOCK_EX);
        rename($tmpPath, $path);
    }

    function __construct(array $config = [])
    {
        $app = App::i();
        if ($app->view instanceof \MapasCulturais\Themes\BaseV2\Theme) {
            parent::__construct($config);
        }
    }

    function _init(){
        $app = App::i();

        // Remove espaços múltiplos e espaços no início/fim do nome e nome completo
        $app->hook('entity(<<*>>).save:before', function() use($app) {
            if($this->name) {
                $this->name = trim(preg_replace('/\s+/', ' ', $this->name));
            }
            
            if($this->nomeCompleto) {
                $this->nomeCompleto = trim(preg_replace('/\s+/', ' ', $this->nomeCompleto));
            }
        });

        // Atualiza o campo pessoa idosa no momento de login
        $app->hook('auth.successful', function () use($app){
            if ($app->auth->isUserAuthenticated()) {
                $cache_key = "profile:idoso:{$app->user->id}";
                if(!$app->cache->contains($cache_key)){
                    $entity = $app->user->profile;
                    if($entity->dataDeNascimento){
                        $today = new \DateTime('now');
                        $calc = (new \DateTime($entity->dataDeNascimento))->diff($today);
                        $idoso = ($calc->y >= 60) ? "1" : "0";
                        if($entity->idoso != $idoso){
                            $entity->idoso = $idoso;
                            $entity->save(true);
                        }
                    }
                    $app->cache->save($cache_key,1,DAY_IN_SECONDS);
                }
            } 
        });

        $app->hook('Theme::isRequestedEntityMine', function () use($app) {
            $entity = $this->controller->requestedEntity;

            if($entity->canUser("@control")){
                if($app->user->is('admin')){
                    if($entity->ownerUser->equals($app->user)){
                        return true;
                    }
                }else{
                    return true;
                }
            }

            return false;
        });

        $self = $this;
        $app->hook('POST(<<agent|space|event|project|opportunity|registration>>.saveTableColumnsConfig)', function () use ($app, $self) {
            /** @var \MapasCulturais\Controller $this */
            $this->requireAuthentication();

            if (!$app->user->is('saasSuperAdmin')) {
                throw new PermissionDenied($app->user, null, i::__('Gerenciar configuração global das colunas'));
            }

            $tableKey = (string) ($this->data['tableKey'] ?? '');
            $order = $self->sanitizeColumns((array) ($this->data['order'] ?? []));
            $visible = $self->sanitizeColumns((array) ($this->data['visible'] ?? []));
            $required = $self->sanitizeColumns((array) ($this->data['required'] ?? []));
            $known = $self->sanitizeColumns((array) ($this->data['known'] ?? []));

            if ($tableKey === '' || !preg_match('/^[a-zA-Z0-9_\\-]+$/', $tableKey)) {
                $this->json(['error' => true, 'message' => i::__('Identificador da tabela inválido')], 400);
                return;
            }

            if (!$known) {
                $this->json(['error' => true, 'message' => i::__('Nenhuma coluna conhecida foi enviada')], 400);
                return;
            }

            $knownMap = array_flip($known);
            $order = array_values(array_filter($order, fn($item) => isset($knownMap[$item])));
            $visible = array_values(array_filter($visible, fn($item) => isset($knownMap[$item])));
            $required = array_values(array_filter($required, fn($item) => isset($knownMap[$item])));

            foreach ($known as $column) {
                if (!in_array($column, $order, true)) {
                    $order[] = $column;
                }
            }

            foreach ($required as $column) {
                if (!in_array($column, $visible, true)) {
                    $visible[] = $column;
                }
            }

            $config = [
                'order' => $order,
                'visible' => $visible,
                'updatedAt' => date(DATE_ATOM),
                'updatedBy' => $app->user->id,
            ];

            $self->writeColumnsConfig($tableKey, $config);

            $this->json([
                'error' => false,
                'tableKey' => $tableKey,
                'config' => $config,
            ]);
        });
    }

    function register(){
    }
}