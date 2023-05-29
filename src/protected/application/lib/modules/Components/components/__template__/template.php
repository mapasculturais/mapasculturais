<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<slot :fullname="fullname" :displayName="displayName" :compareDisplayName="compareDisplayName" :compareFullname="compareFullname" >
    <p>{{entity.name}} {{compareDisplayName}} {{displayName}}</p>
    <p>{{entity.nomeCompleto}} {{compareFullname}} {{fullname}}</p>
</slot>
<button @click="defineNames()"><?= i::__('Definir Nomes') ?></button>