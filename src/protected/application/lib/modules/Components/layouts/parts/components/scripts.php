<script>
var MapasCulturais, Mapas;
$MAPAS = Mapas ?? MapasCulturais
$TEMPLATES = $MAPAS?.componentTemplates ||  [];
$DESCRIPTIONS = $MAPAS?.EntitiesDescription || [];

window.app = Vue.createApp({});
window.pinia = Pinia.createPinia();
window.app.use(pinia);
window.app.use(VueFinalModal.vfmPlugin);
window.app.component('iconify', Icon.Icon);
</script>

<?php $this->printScripts('components'); ?>

<script>
    app.mount('#main-app');
</script>
