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
import * as CurrencyInput from 'vue-currency-input';
import * as Dates from 'date-fns';
import * as FloatingVue from 'floating-vue'
import MediaQuery from './media-query'
import { vMaska } from 'maska'
import { VueRecaptcha } from 'vue-recaptcha';
import Slider from '@vueform/slider'
import Vue3EasyDataTable from 'vue3-easy-data-table';



const app = Vue.createApp({})
const pinia = Pinia.createPinia()

app.use(pinia)
app.use(VueFinalModal)
app.use(FloatingVue)
app.component('Iconify', Icon)
app.component('Cropper', VueAdvancedCropper)
app.component('Datepicker', Datepicker);
app.component('Slider', Slider);
app.component('EasyDataTable', Vue3EasyDataTable);
app.directive('maska', vMaska);
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
globalThis.CurrencyInput = CurrencyInput
globalThis.Dates = Dates;
globalThis.VueRecaptcha = VueRecaptcha;
// globalThis.Vue3EasyDataTable = Vue3EasyDataTable;


globalThis.$MAPAS = typeof Mapas !== 'undefined' ? Mapas : MapasCulturais
globalThis.$DESCRIPTIONS = $MAPAS.EntitiesDescription ?? []
globalThis.$TAXONOMIES = $MAPAS.Taxonomies ?? {}

document.addEventListener('DOMContentLoaded', () => {
    app.mount('#main-app');
})
