import { Icon } from '@iconify/vue'
import * as Pinia from 'pinia'
import * as Vue from 'vue'
import VueFinalModal from 'vue-final-modal'
import * as VueAdvancedCropper from "vue-advanced-cropper";
import * as Vue3Carousel from "vue3-carousel";
import * as VueLeaflet from "@vue-leaflet/vue-leaflet";
import * as Leaflet from 'leaflet';
import { MarkerClusterGroup } from 'leaflet.markercluster';
import Datepicker from '@vuepic/vue-datepicker';
import * as Dates from 'date-fns';
import * as FloatingVue from 'floating-vue'
import MediaQuery from './media-query'

const app = Vue.createApp({})
const pinia = Pinia.createPinia()

app.use(pinia)
app.use(VueFinalModal)
app.use(FloatingVue)
app.component('Iconify', Icon)
app.component('Cropper', VueAdvancedCropper)
app.component('Datepicker', Datepicker);
app.use(MediaQuery)

globalThis.app = app
globalThis.Pinia = Pinia
globalThis.pinia = pinia
globalThis.Vue = Vue
globalThis.VueAdvancedCropper = VueAdvancedCropper
globalThis.Vue3Carousel = Vue3Carousel
globalThis.VueLeaflet = VueLeaflet
globalThis.MarkerClusterGroup = MarkerClusterGroup
globalThis.Leaflet = Leaflet
globalThis.Datepicker = Datepicker
globalThis.Dates = Dates;


globalThis.$MAPAS = typeof Mapas !== 'undefined' ? Mapas : MapasCulturais
globalThis.$DESCRIPTIONS = $MAPAS.EntitiesDescription ?? []
globalThis.$TAXONOMIES = $MAPAS.Taxonomies ?? {}

document.addEventListener('DOMContentLoaded', () => {
    let rawProfile = globalThis.$MAPAS.userProfile;
    if(rawProfile) {
        let profile = new Entity('agent', rawProfile.id);
        profile.populate(rawProfile);
        globalThis.$MAPAS.userProfile = profile;
    }
    
    app.mount('#main-app');
    // document.body.style.opacity = 1;

    let opacity = 0.01;
    globalThis.opacityInterval = setInterval(() => {
        if(opacity >= 1) {
            clearInterval(globalThis.opacityInterval);
        }
        document.body.style.opacity = opacity;
        opacity += 0.02;
    },5);
})
