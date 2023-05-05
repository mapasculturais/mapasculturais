<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;
?>
<template v-if="global.auth.isLoggedIn">
    <img v-if="avatarUrl" :src="avatarUrl" />
    <mc-icon v-if="!avatarUrl" name="user"></mc-icon>
</template>
