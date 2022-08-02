import { Icon } from '@iconify/vue'
import * as Pinia from 'pinia'
import * as Vue from 'vue'
import VueFinalModal from 'vue-final-modal'
import * as VueAdvancedCropper from "vue-advanced-cropper";
import * as Vue3Carousel from "vue3-carousel";
import * as VueLeaflet from "@vue-leaflet/vue-leaflet";

const app = Vue.createApp({})
const pinia = Pinia.createPinia()

app.use(pinia)
app.use(VueFinalModal)
app.component('Iconify', Icon)
app.component('Cropper', VueAdvancedCropper)

globalThis.app = app
globalThis.Pinia = Pinia
globalThis.pinia = pinia
globalThis.Vue = Vue
globalThis.VueAdvancedCropper = VueAdvancedCropper
globalThis.Vue3Carousel = Vue3Carousel
globalThis.VueLeaflet = VueLeaflet

document.addEventListener('DOMContentLoaded', () => {
    globalThis.$MAPAS = typeof Mapas !== 'undefined' ? Mapas : MapasCulturais
    globalThis.$DESCRIPTIONS = $MAPAS.EntitiesDescription ?? []
    globalThis.$TAXONOMIES = $MAPAS.Taxonomies ?? {}

    app.mount('#main-app')
})
