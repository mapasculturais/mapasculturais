<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<template v-if="global.auth.isLoggedIn">
    <img v-if="avatarUrl" :src="avatarUrl" />
    <mc-icon v-if="!avatarUrl" :entity="global.auth.user.profile"></mc-icon>
</template>
