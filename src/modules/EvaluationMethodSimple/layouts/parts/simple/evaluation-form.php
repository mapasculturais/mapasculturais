<?php

/**
  * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    simple-evaluation-form
');
?>

<simple-evaluation-form :entity='entity' :form-data="formData"></simple-evaluation-form>