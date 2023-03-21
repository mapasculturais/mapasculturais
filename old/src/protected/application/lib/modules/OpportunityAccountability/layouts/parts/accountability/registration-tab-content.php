<?php $url = $app->createUrl('accountability', 'registration', [$entity->registration->id]); ?>

<div id="registration" class="aba-content">
    <iframe id="project-registration" src="<?= $url ?>" style="width:100%;"></iframe>
</div>

<script type="text/javascript">

    const iframeProjectRegistration = document.getElementById('project-registration');

    window.addEventListener('message', (e) => {
        if (typeof e.data.height !== "undefined") {
            iframeProjectRegistration.style.height = e.data.height + 'px';
        }
    });

    window.addEventListener('hashchange', (e) => {
        iframeProjectRegistration.contentWindow.postMessage('', MapasCulturais.baseURL);
    });

</script>