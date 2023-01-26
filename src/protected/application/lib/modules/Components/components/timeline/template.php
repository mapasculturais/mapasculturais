<?php
$this->import('timeline-item');
?>

  <section class="timeline">
    <div v-if="hasItems" class="wrapper-timeline">
      <div
        v-for="(timelineContent, timelineIndex) in dataTimeline"
        :key="timelineIndex"
        :class="wrapperItemClass(timelineIndex)"
      >
        <timeline-item
          :item-timeline="timelineContent"
          :date-locale="dateLocale"
          :color-dots="colorDots"
          >
        </timeline-item>
      </div>
    </div>
    <p v-else>{{ messageWhenNoItems }}</p>
  </section>