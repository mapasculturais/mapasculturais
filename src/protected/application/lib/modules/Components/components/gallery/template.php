<div class="gallery">

    <h4> {{title}} </h4>


    <div class="gallery__images">   
        <div @click="open = !open" v-for="img in Images" class="gallery__images--img" >   
            <img @click="actualImg = img" :src="img.transformations.galleryFull?.url" :imgId="img.id" :title="img.description"/>
        </div>
    </div>

    <div class="gallery__full" :class="{ 'active': open }">
        <div @click="open = !open" class="gallery__full--overlay"> </div>

        <div class="gallery__full--image">
            <img v-if="actualImg" :src="actualImg?.transformations.galleryFull?.url" :imgId="actualImg?.id" :title="actualImg?.description"/>
            <iconify icon="eos-icons:bubble-loading" />
        </div>
    </div>

</div>


<!-- 
<template>
  <div :id="galleryID">
    <a
      v-for="(image, key) in imagesData"
      :key="key"
      :href="image.largeURL"
      :data-pswp-width="image.width"
      :data-pswp-height="image.height"
      target="_blank"
      rel="noreferrer"
    >
      <img :src="image.thumbnailURL" alt="" />
    </a>
  </div>
</template> -->



