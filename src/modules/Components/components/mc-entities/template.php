<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-loading
');
?>
<slot 
    name="header" 
    :entities="entities" 
    :load-more="loadMore" 
    :query="query" 
    :refresh="refresh">
</slot>

<slot v-if="entities.loading" name="loading" :entities="entities">
    <mc-loading :condition="entities.loading"></mc-loading>
</slot>
<template v-if="!entities.loading">
    <slot 
        v-if="entities.length > 0" 
        :entities="entities" 
        :load-more="loadMore" 
        :query="query" 
        :refresh="refresh"></slot>
    <slot v-else name="empty">
        <div class="panel__row noEntity">
            <p><?= i::__('Nenhuma entidade encontrada') ?></p>
        </div>
    </slot>
</template>

<slot v-if="showLoadMore()" name="load-more" :entities="entities" :load-more="loadMore">
    <div class="load-more">
        <mc-loading :condition="entities.loadingMore"></mc-loading>
        <button class="button--large button button--primary-outline" v-if="!entities.loadingMore" @click="loadMore()"><?php i::_e('Carregar Mais') ?></button>
    </div>
</slot>
<slot name="createNew"></slot>