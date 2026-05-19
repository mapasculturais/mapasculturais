<?php
namespace MapasCulturais\Controllers;

use MapasCulturais\App;
use MapasCulturais\Entities\RegistrationFileConfiguration as RegistrationFileConfigurationEntity;
use MapasCulturais\Entities\RegistrationFileConfigurationFile;
use MapasCulturais\Traits;

/**
 * Controlador para configurações de arquivos de inscrição
 * 
 * Este controlador gerencia as configurações de arquivos para inscrições
 * em oportunidades no sistema Mapas Culturais.
 * 
 * @property-read \MapasCulturais\Entities\RegistrationFileConfiguration $newEntity Nova instância vazia da entidade
 * @property-read \Doctrine\ORM\EntityRepository $repository Repositório Doctrine da entidade
 * @property-read array $fields Campos da entidade
 * @property-read \MapasCulturais\Entities\RegistrationFileConfiguration $requestedEntity Entidade solicitada na requisição atual
 * 
 * @package MapasCulturais\Controllers
 */
class RegistrationFileConfiguration extends EntityController {
    use Traits\ControllerUploads;

    public function POST_duplicate() {
        $this->requireAuthentication();

        $app = App::i();
        /** @var RegistrationFileConfigurationEntity|null $entity */
        $entity = $this->requestedEntity;

        if (!$entity) {
            $app->pass();
        }

        $entity->checkPermission('create');

        $duplicated = new RegistrationFileConfigurationEntity();
        $duplicated->owner = $entity->owner;
        $duplicated->step = $entity->step;
        $duplicated->title = $entity->title;
        $duplicated->description = $entity->description;
        $duplicated->required = $entity->required;
        $duplicated->categories = $entity->categories ?: [];
        $duplicated->displayOrder = $entity->displayOrder + 1;
        $duplicated->conditional = $entity->conditional;
        $duplicated->conditionalField = $entity->conditionalField;
        $duplicated->conditionalValue = $entity->conditionalValue;
        $duplicated->registrationRanges = $entity->registrationRanges ?: [];
        $duplicated->proponentTypes = $entity->proponentTypes ?: [];
        $duplicated->allowedFileTypes = $entity->allowedFileTypes ?: [];
        $duplicated->save(true);

        if ($template = $entity->getFile('registrationFileTemplate')) {
            $originFile = $app->repo('RegistrationFileConfigurationFile')->find($template->id);

            if ($originFile && file_exists($originFile->path)) {
                $tmp_file = sys_get_temp_dir() . '/' . uniqid('rfc-template-', true) . '-' . $template->name;
                copy($originFile->path, $tmp_file);

                $newTemplateFile = [
                    'name' => $template->name,
                    'type' => $template->mimeType,
                    'tmp_name' => $tmp_file,
                    'error' => 0,
                    'size' => filesize($tmp_file)
                ];

                $newTemplate = new RegistrationFileConfigurationFile($newTemplateFile);
                $newTemplate->owner = $duplicated;
                $newTemplate->description = $template->description;
                $newTemplate->group = $template->group;
                $newTemplate->save(true);

                if (file_exists($tmp_file)) {
                    unlink($tmp_file);
                }
            }
        }

        $this->json($duplicated->jsonSerialize());
    }

    /**
     * Redireciona requisições GET para criação
     * 
     * @api GET create
     * @return void Redireciona para a aplicação principal
     */
    function GET_create() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições GET para edição
     * 
     * @api GET edit
     * @return void Redireciona para a aplicação principal
     */
    function GET_edit() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições GET para visualização única
     * 
     * @api GET single
     * @return void Redireciona para a aplicação principal
     */
    function GET_single() {
        App::i()->pass();
    }

    /**
     * Redireciona requisições GET para listagem
     * 
     * @api GET index
     * @return void Redireciona para a aplicação principal
     */
    function GET_index() {
        App::i()->pass();
    }
}
