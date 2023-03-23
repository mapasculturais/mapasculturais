<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity,
];

?>
<?php $this->part('singles/registration-edit--fields', $_params); ?>
<script>

function sendSectionTops() {
        const tops = [];
        for(let section of document.getElementsByClassName('title_section')) {
            let label = section.getElementsByClassName('label');
            tops.push({
                top: section.offsetParent.offsetTop,
                label: label[0].innerHTML.trim()
            });
        }

        window.parent.postMessage({
            type: 'section.tops',
            data: tops
        }, '*');
    }

    setInterval(sendSectionTops, 50);
</script>