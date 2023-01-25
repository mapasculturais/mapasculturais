<?php
$this->import('timeline-item');
?>
<template>
  <section class="timeline">
    <div v-if="hasItems" class="wrapper-timeline">
      <div
        v-for="(timelineContent, timelineIndex) in dataTimeline"
        :key="timelineIndex"
        :class="wrapperItemClass(timelineIndex)"
      >
        <div class="section-year">
          <p v-if="hasYear(timelineContent)" class="year">
            {{ getYear(timelineContent) }}
          </p>
        </div>
        <TimelineItem
          :item-timeline="timelineContent"
          :date-locale="dateLocale"
          :color-dots="colorDots"
        />
      </div>
    </div>
    <p v-else>{{ messageWhenNoItems }}</p>
  </section>
</template>