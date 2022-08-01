import { Icon } from '@iconify/vue'
import * as Pinia from 'pinia'
import * as Vue from 'vue'
import VueFinalModal from 'vue-final-modal'
import * as VueAdvancedCropper from "vue-advanced-cropper";
import * as Vue3Carousel from "vue3-carousel";

const app = Vue.createApp({})
const pinia = Pinia.createPinia()

app.use(pinia)
app.use(VueFinalModal)
app.component('Iconify', Icon)
app.component('Cropper', VueAdvancedCropper)

globalThis.$MAPAS = typeof Mapas !== 'undefined' ? Mapas : MapasCulturais
globalThis.$DESCRIPTIONS = $MAPAS.EntitiesDescription ?? []
globalThis.$TAXONOMIES = $MAPAS.Taxonomies ?? {}

globalThis.app = app
globalThis.Pinia = Pinia
globalThis.pinia = pinia
globalThis.Vue = Vue
globalThis.VueAdvancedCropper = VueAdvancedCropper
globalThis.Vue3Carousel = Vue3Carousel

document.addEventListener('DOMContentLoaded', () => {
    app.mount('#main-app')
})
