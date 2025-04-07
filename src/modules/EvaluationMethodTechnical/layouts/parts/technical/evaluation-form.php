<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    technical-evaluation-form
');
?>

<technical-evaluation-form :entity="entity" :form-data="formData"></technical-evaluation-form>