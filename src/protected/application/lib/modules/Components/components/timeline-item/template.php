<template>
  <section class="timeline-item">
    <div class="item">
      <span :style="getBackgroundColour(itemTimeline.color)" class="dot" />
      <h3 class="date-item">{{ getFormattedDate(itemTimeline) }}</h3>
      <h4 class="title-item" v-html="itemTimeline.title" />
      <p class="description-item" v-html="itemTimeline.description" />
    </div>
  </section>
</template>