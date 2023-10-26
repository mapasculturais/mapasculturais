<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity,
];

?>
<?php $this->applyTemplateHook('form','begin');?>
<?php $this->part('singles/registration-edit--fields', $_params); ?>
<?php $this->applyTemplateHook('form','end');?>

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