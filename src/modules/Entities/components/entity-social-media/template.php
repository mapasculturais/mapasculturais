<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-collapsible
    mc-title
    mc-icon
');
?>

<mc-collapsible :open="show" :classes="classes">
    <template #header>
        <mc-title tag="h4" size="medium" class="bold">
            <?= i::__("Redes sociais") ?>
        </mc-title>
    </template>

    <template #body>
        <div class="entity-social-media">
            <div v-if="!editable" class="entity-social-media__links">
                <div v-if="entity.instagram" class="entity-social-media__links--link" classes="col-6 sm:col-12">
                    <mc-icon name="instagram"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('instagram')">{{entity.instagram}}</a>
                </div>

                <div v-if="entity.twitter" class="entity-social-media__links--link">
                    <mc-icon name="twitter"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('twitter')">{{entity.twitter}}</a>
                </div>

                <div v-if="entity.facebook" class="entity-social-media__links--link">
                    <mc-icon name="facebook"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('facebook')">{{entity.facebook}}</a>
                </div>

                <div v-if="entity.youtube" class="entity-social-media__links--link">
                    <mc-icon name="youtube"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('youtube')">{{entity.youtube}}</a>
                </div>

                <div v-if="entity.linkedin" class="entity-social-media__links--link">
                    <mc-icon name="linkedin"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('linkedin')">{{entity.linkedin}}</a>
                </div>

                <div v-if="entity.vimeo" class="entity-social-media__links--link">
                    <mc-icon name="vimeo"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('vimeo')">{{entity.vimeo}}</a>
                </div>

                <div v-if="entity.spotify" class="entity-social-media__links--link">
                    <mc-icon name="spotify"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('spotify')">{{entity.spotify}}</a>
                </div>

                <div v-if="entity.pinterest" class="entity-social-media__links--link">
                    <mc-icon name="pinterest"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('pinterest')">{{entity.pinterest}}</a>
                </div>

                <div v-if="entity.tiktok" class="entity-social-media__links--link">
                    <mc-icon name="tiktok"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('tiktok')">{{entity.tiktok}}</a>
                </div>

                <div v-if="entity.fediverso" class="entity-social-media__links--link">
                    <mc-icon name="fediverso"></mc-icon>
                    <a target="_blank" :href="buildSocialMediaLink('fediverso')">{{entity.fediverso}}</a>
                </div>
            </div>

            <p v-if="editable" class="entity-social-media__description">
                <?= i::__("Os dados inseridos abaixo serão exibidos para todos os usuários da plataforma.") ?>
            </p>

            <div v-if="editable" class="entity-social-media__edit grid-12">
                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="instagram" type="socialMedia" icon="instagram" :label="'Instagram'" :placeholder="'<?= i::__('nomedousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="twitter" type="socialMedia" icon="twitter" :label="'X (antigo twitter)'" :placeholder="'<?= i::__('nomedousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="facebook" type="socialMedia" icon="facebook" :label="'Facebook'" :placeholder="'<?= i::__('nomedousuario ou iddousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="vimeo" type="socialMedia" icon="vimeo" :label="'Vimeo'" :placeholder="'<?= i::__('nomedousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="youtube" type="socialMedia" icon="youtube" :label="'YouTube'" :placeholder="'<?= i::__('iddocanal') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="linkedin" type="socialMedia" icon="linkedin" :label="'Linkedin'" :placeholder="'<?= i::__('nomedousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="spotify" type="socialMedia" icon="spotify" :label="'Spotify'" :placeholder="'<?= i::__('nomedousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="pinterest" type="socialMedia" icon="pinterest" :label="'Pinterest'" :placeholder="'<?= i::__('nomedousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="tiktok" type="socialMedia" icon="tiktok" :label="'Tiktok'" :placeholder="'<?= i::__('nomedousuario') ?>'"></entity-field>
                </div>

                <div class="entity-social-media__edit--link col-4 sm:col-12">
                    <entity-field :entity="entity" prop="fediverso" type="socialMedia" icon="fediverso" :label="'Fediverso'" :placeholder="'<?= i::__('https://nomedoservidor.com.br/@nomedousuario') ?>'"></entity-field>
                </div>
            </div>
        </div>
    </template>
</mc-collapsible>
