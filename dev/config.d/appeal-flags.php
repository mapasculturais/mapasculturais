<?php
// Local dev only: enables the appeal epic (#7) feature flags.
// Several checks read env() directly (ignoring module config), so set both.
$_ENV['APPEAL_TWO_STAGE_PUBLISH'] = 'true';
$_ENV['APPEAL_SCORE_CORRECTION'] = 'true';

return [
    'module.OpportunityAppealPhase' => [
        'featureFlag.appealTwoStagePublish' => true,
        'featureFlag.appealScoreCorrection' => true,
    ],
];
