<?php

use MapasCulturais\i;

$this->import('loading');
?>
<loading :condition="entities.loading"></loading>
<slot v-if="!entities.loading" 
    :entities="entities" 
    :query="query" 
    :load-more="loadMore" 
    :refresh="refresh"></slot> 

<slot v-if="hasSlot('load-more') && showLoadMore()" :entities="entities"></slot>
<template v-if="!hasSlot('load-more') && showLoadMore()">
    <loading :condition="entities.loadingMore"></loading>
    <button v-if="!entities.loadingMore" @click="loadMore()"><?php i::_e('Carregar Mais') ?></button>
</template>