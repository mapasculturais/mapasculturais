<?php
/**
 * Banner de isenção por selos — acompanhamento público da inscrição.
 *
 * Exibe um banner informativo quando a inscrição foi dispensada
 * automaticamente de uma fase de avaliação por possuir todos os selos
 * validadores configurados como plenamente válidos (fully_valid).
 *
 * Gate (Option A — spec seção 4.5 / 3.6):
 *   - publishedRegistrations === true (segue a publicação de resultado da fase)
 *   - sealExemptionStatus === 'granted' (inscrição efetivamente isenta)
 *
 * Antes da publicação, o proponente não vê nem status nem rótulo (LGPD-safe).
 *
 * @var MapasCulturais\Entities\Registration $entity
 * @var MapasCulturais\Entities\Opportunity   $opportunity
 *
 * @see spec-c49fa0bb.md seção 4.5
 */

use MapasCulturais\i;

// Gate Option A: só após publicação do resultado da fase E inscrição isenta.
if (!$opportunity->publishedRegistrations || $entity->sealExemptionStatus !== 'granted') {
    return;
}

// Rótulo configurado na fase (do EMC.sealExemptionConfig.label) com fallback.
// Getter virtual já aplica o fallback "Isento por selos válidos" quando vazio.
$sealExemptionLabel = $entity->sealExemptionLabel ?? i::__('Isento por selos válidos');
?>

<?php $this->applyTemplateHook('registration-single--seal-exemption', 'before'); ?>
<section class="registration-fieldset registration-seal-exemption">
    <?php $this->applyTemplateHook('registration-single--seal-exemption', 'begin'); ?>
    <div class="alert info seal-exemption-banner" role="status">
        <strong class="seal-exemption-banner__label">
            <?= htmlspecialchars($sealExemptionLabel, ENT_QUOTES, 'UTF-8') ?>
        </strong>
        <p class="seal-exemption-banner__description">
            <?php i::_e('Sua inscrição foi dispensada desta fase automaticamente com base nos selos válidos do seu agente.'); ?>
        </p>
    </div>
    <?php $this->applyTemplateHook('registration-single--seal-exemption', 'end'); ?>
</section>
<?php $this->applyTemplateHook('registration-single--seal-exemption', 'after'); ?>
