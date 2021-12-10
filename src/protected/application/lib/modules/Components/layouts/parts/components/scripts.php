<script>
var MapasCulturais, Mapas;
$TEMPLATES = Mapas?.componentTemplates || MapasCulturais?.componentTemplates || [];

window.app = Vue.createApp({});
window.pinia = Pinia.createPinia();
window.app.use(pinia);
</script>

<?php $this->printScripts('components'); ?>

<script>
    app.mount('#main-section');
</script>