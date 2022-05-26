<div class="entity-gallery">

    <h2> {{title}} </h2>

    <div class="entity-gallery__images">   
        <div @click="open" v-for="(img, index) in images" class="entity-gallery__images--img" >
            <img @click="openImg(index)" :src="img.transformations.galleryFull?.url" :imgId="img.id" :title="img.description"/>
        </div>
    </div>

    <div class="entity-gallery__full" :class="{ 'active': galleryOpen }">
        <div @click="close" class="entity-gallery__full--overlay"> </div>

        <div class="entity-gallery__full--image">
            <img v-if="actualImg" :src="actualImg?.url" :imgId="actualImg?.id" :title="actualImg?.description"/>
            <iconify icon="eos-icons:bubble-loading" />
            <div class="description">{{actualImg?.description}}</div>
        </div>

        <div class="entity-gallery__full--buttons">
            <div @click="prev" class="btnPrev"> <iconify icon="ooui:previous-ltr" /> </div>
            <div @click="next" class="btnNext"> <iconify icon="ooui:previous-rtl" /> </div>
        </div>
    </div>

</div>



