<?php
use MapasCulturais\i;
$this->import('
    mc-link
    modal
    mc-icon
');
?>

<modal :title="modalTitle" classes="create-modal" button-label="<?= i::__('Compartilhar') ?>" @open="" @close="">
    <template #default>
        <div class="content-share__socialnetwork">
            <p class="content-share__socialnetwork--title">
              <?= i::__("Nas redes sociais") ?>
            </p>
            <div class="content-share__socialnetwork--icons">
                <div class="icon">
                    <mc-icon width="24px" name="instagram"></mc-icon>
                </div>
                <div class="icon">
                    <mc-icon width="24px" name="twitter"></mc-icon>
                </div>
                <div class="icon">
                    <mc-icon width="24px" name="whatsapp"></mc-icon>
                </div>
                <div class="icon">
                    <mc-icon width="24px" name="telegram"></mc-icon>
                </div>
            </div>
        </div>
        <div class="content-share__share">
            <p class="content-share__share--label">
                <?= i::__("Ou copie o link") ?>
            </p>
            <div class="content-share__share--field">
                <span>l1nq.com/daMmQ</span>
                <div class="content-share__share--button">
                    <mc-icon name="copy" class="content-share__share--icon"></mc-icon>
                    <a href="#"><?= i::__("Copiar") ?></a>
                </div>
            </div>
        </div>
    </template>
    <template #button="modal">
        <label class="label" @click="modal.open"><?= i::__('Compartilhar') ?></label>
    </template>
</modal>