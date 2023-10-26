<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<?php $this->applyTemplateHook('user-accepted-terms', 'before'); ?>

<div class="user-accepted-terms__privacy">
    <?php $this->applyTemplateHook('user-accepted-terms', 'begin'); ?>
    <div class="user-accepted-terms__privacy--accept">
        <label class="user-accepted-terms__privacy--accept-title"><?= i::__('Aceite de termos') ?></label>
        <div v-if="user" class="user-accepted-terms__privacy--accept-title-box">
            <div class="boxterm">
                <div v-for="(term, slug) in terms" class="boxterm__list">
                    <div v-if="user['lgpd_'+ slug]?.[term.md5]">
                        <label class="boxterm__list-subterm">
                            <label class="boxterm__list-subterm-title"><?= i::__('{{term.title}}') ?></label>
                            <label class="boxterm__list-subterm-content"><label><?= i::__(' aceito em {{formatDate(user["lgpd_"+slug][term.md5].timestamp)}}') ?></label>
                                <label><?= i::__(' pelo ip {{user["lgpd_"+slug][term.md5].ip}}') ?></label>
                            </label>
                        </label>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php $this->applyTemplateHook('user-accepted-terms', 'end'); ?>
</div>
<?php $this->applyTemplateHook('user-accepted-terms', 'after'); ?>