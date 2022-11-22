<?php
$props = $this->getLockedFieldsSeal();
?>
<style>
    .fields {
        padding: 5px;
        font-weight: normal;
    }

    .fields>div {
        float: left;
        width: 50%;
    }
</style>
<div id="seal-config">
    <span class="js-editable " style="display:none;" id="locked-fields" type="text" data-edit="lockedFields"></span>
    <form class="js-locked-fields">
        <div class="fields">
            <h2> Agente </h2>
            <?php foreach ($props['agent'] as  $field => $values) : ?>
                <div>
                    <label>
                        <input type='checkbox' name='lockedFields[]' value="agent.<?= $field ?>" <?= in_array("agent.{$field}", $entity->lockedFields) ?  "checked" : "" ?>>
                        <?= $values['label'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="fields">
            <h2> Espaço</h2>
            <?php foreach ($props['space'] as  $field => $values) : ?>
                <div>
                    <label>
                        <input type='checkbox' name='lockedFields[]' value="space.<?= $field ?>" <?= in_array("space.{$field}", $entity->lockedFields) ?  "checked" : "" ?>>
                        <?= $values['label'] ?>
                    </label>
                </div>
            <?php endforeach; ?>
        </div>
    </form>
    <script>
        (($) => {
            $(() => {
                $('.js-locked-fields input').on('change', () => {
                    const $form = $('.js-locked-fields')
                    let fields = $(".js-locked-fields input:checkbox:checked").map(function() {
                        return $(this).val();
                    }).get(); // <----
                    $('#locked-fields').editable('setValue', fields)

                })
            })
        })(jQuery)
    </script>
</div>