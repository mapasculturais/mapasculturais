<?php

use MapasCulturais\i;

$this->import('timeline-item');
?>

<!-- <section class="timeline">
    <div v-if="hasItems" class="wrapper-timeline">
        <div v-for="(timelineContent, timelineIndex) in dataTimeline" :key="timelineIndex" :class="wrapperItemClass(timelineIndex)">
            <timeline-item :item-timeline="timelineContent" :date-locale="dateLocale" :color-dots="colorDots">
            </timeline-item>
        </div>
    </div>
    <p v-else>{{ messageWhenNoItems }}</p>
</section> -->

<section :class="['timeline', {'center': center}, {'big': big}]">

    <div v-for="item in phases" :class="['item', {'active': isActive(item.id)}]">
        <div class="item__dot"> <span class="dot"></span> </div>

        <div class="item__content">
            <div v-if="item.isFirstPhase" class="item__content--title"> <?= i::__('Fase de inscrições') ?> </div>
            <div v-if="!item.isFirstPhase" class="item__content--title"> {{item.name}} </div>

            <div class="item__content--description">
                <?= i::__('de') ?> <span v-if="dateFrom(item.id)">{{dateFrom(item.id)}}</span>
                <?= i::__('a') ?> <span v-if="dateTo(item.id)">{{dateTo(item.id)}}</span>
                <?= i::__('às') ?> <span v-if="hour(item.id)">{{hour(item.id)}}</span>
            </div>
        </div>
    </div>

</section>