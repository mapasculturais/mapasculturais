<?php
use MapasCulturais\i;
$this->import('loading');
?>
<slot 
    name="header" 
    :entities="entities" 
    :load-more="loadMore" 
    :query="query" 
    :refresh="refresh">
</slot>

<slot v-if="entities.loading" name="loading" :entities="entities">
    <loading :condition="entities.loading"></loading>
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

<slot v-if="showLoadMore()" name="load-more" :entities="entities">
    <div class="col-9 search-list__loadMore">
        <loading :condition="entities.loadingMore"></loading>
        <button class="button--large button button--primary-outline" v-if="!entities.loadingMore" @click="loadMore()"><?php i::_e('Carregar Mais') ?></button>
    </div>
</slot>