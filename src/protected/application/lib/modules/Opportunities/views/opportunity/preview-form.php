<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Entities\Registration $entity
 */

use MapasCulturais\i;

$this->layout = "entity";

$this->import('
    opportunity-header
    registration-info
    v1-embed-tool
');

?>

<div class="main-app form-preview">

    <opportunity-header :opportunity="entity"></opportunity-header>
    
    <div class="form-preview__content">

        <mc-container>
            <main class="grid-12">

                <registration-info :registration="entity" classes="col-12"></registration-info>

                <section class="col-12">
                    <v1-embed-tool iframe-id="preview-form" route="registrationformpreview" :id="entity.id"></v1-embed-tool>
                </section>
            </main>
        </mc-container>
    </div>
</div>