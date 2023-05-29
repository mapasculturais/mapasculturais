<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-link
    mc-modal
');
?>
<mc-modal :title="modalTitle" classes="create-modal" button-label="<?= i::__('Compartilhar') ?>" @open="" @close="">
    <template #default>
        <div class="seal-content-share__socialnetwork">
            <p class="seal-content-share__socialnetwork--title">
              <?= i::__("Nas redes sociais") ?>
            </p>
            <div class="seal-content-share__socialnetwork--icons">
                <div class="icon" v-if="socialNetworks.instagram">
                    <a :href="socialNetworks.instagram"><mc-icon width="24px" name="instagram"></mc-icon></a>
                </div>
                <div class="icon" v-if="socialNetworks.twitter">
                    <a :href="socialNetworks.twitter"><mc-icon width="24px" name="twitter"></mc-icon></a>
                </div>
                <div class="icon" v-if="socialNetworks.whatsapp">
                    <a :href="socialNetworks.whatsapp"><mc-icon width="24px" name="whatsapp"></mc-icon></a>
                </div>
                <div class="icon" v-if="socialNetworks.telegram">
                    <a :href="socialNetworks.telegram"><mc-icon width="24px" name="telegram"></mc-icon></a>
                </div>
            </div>
        </div>
        <div class="seal-content-share__share">
            <p class="seal-content-share__share--label">
                <?= i::__("Ou copie o link") ?>
            </p>
            <div class="seal-content-share__share--field">
                <span>l1nq.com/daMmQ</span>
                <div class="seal-content-share__share--button">
                    <mc-icon name="copy" class="seal-content-share__share--icon"></mc-icon>
                    <a href="#"><?= i::__("Copiar") ?></a>
                </div>
            </div>
        </div>
    </template>
    <template #button="modal">
        <label class="label" @click="modal.open"><?= i::__('Compartilhar') ?></label>
    </template>
</mc-modal>