<?php

/**
  * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    continuous-evaluation-form
');
?>

<continuous-evaluation-form :entity='entity' :form-data="formData"></continuous-evaluation-form>