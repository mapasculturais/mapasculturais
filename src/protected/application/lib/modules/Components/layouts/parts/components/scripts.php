<script>
var MapasCulturais, Mapas;
$TEMPLATES = Mapas?.componentTemplates || MapasCulturais?.componentTemplates || [];

window.app = Vue.createApp({});
window.pinia = Pinia.createPinia();
window.app.use(pinia);
window.app.use(VueFinalModal.vfmPlugin);
</script>

<?php $this->printScripts('components'); ?>

<script>
    app.mount('#main-app');
</script>
