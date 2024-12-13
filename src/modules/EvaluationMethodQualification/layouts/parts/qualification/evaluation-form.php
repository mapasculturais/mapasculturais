<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
  qualification-evaluation-form
');
?>

<qualification-evaluation-form :entity="entity" :form-data="formData"></qualification-evaluation-form>