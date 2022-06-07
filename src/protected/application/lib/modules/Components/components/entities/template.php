<?php

use MapasCulturais\i;

$this->import('loading');
?>
<slot name="header1"
      :entities="entities"
      :load-more="loadMore"
      :query="query"
      :refresh="refresh">
</slot>

<loading :condition="entities.loading"></loading>
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
    <a href="" class="button button--large button--primary" v-if="!entities.loadingMore" @click="loadMore()"><?php i::_e('Carregar Mais') ?></a>
</slot>
