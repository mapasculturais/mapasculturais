<?php

namespace MapasCulturais\Traits;

use MapasCulturais\App;

trait ControllerPrivateEntity
{
    function ALL_makePrivate()
    {
        /** @var \MapasCulturais\Controller $this */
        $this->requireAuthentication();

        $app = App::i();
        if (!key_exists('id', $this->urlData))
            $app->pass();

        $entity = $this->requestedEntity;

        if (!$entity)
            $app->pass();

        if ($errors = $entity->validationErrors) {
            $this->errorJson($errors);
        }

        $entity->makePrivate(true);

        if ($this->isAjax()) {
            $this->json($entity);
        } else {
            //e redireciona de volta para o referer
            $app->redirect($app->request->getReferer());
        }
    }
}
