import { Icon } from '@iconify/vue'
import * as Pinia from 'pinia'
import * as Vue from 'vue'
import VueFinalModal from 'vue-final-modal'

const app = Vue.createApp({})
const pinia = Pinia.createPinia()

app.use(pinia)
app.use(VueFinalModal)
app.component('Iconify', Icon)

window.$MAPAS = typeof Mapas !== 'undefined' ? Mapas : MapasCulturais
window.$DESCRIPTIONS = $MAPAS?.EntitiesDescription ?? []
window.$TEMPLATES = $MAPAS?.componentTemplates ?? []

window.app = app
window.Pinia = Pinia
window.pinia = pinia
window.Vue = Vue

document.addEventListener('DOMContentLoaded', () => {
    app.mount('#main-app')
})
