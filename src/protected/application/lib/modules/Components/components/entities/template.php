<?php

use MapasCulturais\i;

$this->import('loading');
?>
<slot name="header"
      :entities="entities"
      :load-more="loadMore"
      :query="query"
      :refresh="refresh">
</slot>

<slot v-if="entities.loading" name="loading" :entities="entities">
    <loading :condition="entities.loading"></loading>
</slot>
<template v-if="!entities.loading">
    <slot v-if="(entities.length > 0) || ('@keyword' in query)"
        :entities="entities"
        :load-more="loadMore"
        :query="query"
        :refresh="refresh"></slot>
    <slot v-if="entities.length === 0" name="empty">
        <div class="panel__row">
            <p><?=i::__('Nenhuma entidade encontrada')?></p>
        </div>
    </slot>
</template>

<slot v-if="showLoadMore()" name="load-more" :entities="entities">
    <loading :condition="entities.loadingMore"></loading>
    <button class="button button--large button--primary" v-if="!entities.loadingMore" @click="loadMore()"><?php i::_e('Carregar Mais') ?></button>
</slot>
