<?php

/**
 * Valores iniciais de setting_meta ao criar um registro em `setting` para um subsite.
 * Manter alinhado com o seed em db-updates.php (site settings: insert default values).
 *
 * @return array<string, string>
 */
return [
    'mailer_email' => 'sysadmin@localhost',
    'mailer_host' => 'mailhog',
    'mailer_protocol' => 'LOCAL',
    'recaptcha_secret' => '6LeIxAcTAAAAAGG-vFI1TnRWxMZNFuojJ4WifJWe',
    'recaptcha_sitekey' => '6LeIxAcTAAAAAJcZVRqyHh71UMIEGNQ_MXjiZKhI',
    'logoDefaultTitle' => 'Mapas',
    'logoDefaultSubTitle' => 'Culturais',
    'primaryColor' => '#117c83',
    'secondaryColor' => '#d14526',
    'opportunitiesColor' => '#d14426',
    'agentsColor' => '#ef7b45',
    'eventsColor' => '#9c4ec7',
    'spacesColor' => '#538d08',
    'projectsColor' => '#107c83',
    'sealsColor' => '#471363',
    'logoColorPart1' => '#2fd9e4',
    'logoColorPart2' => '#107c83',
    'logoColorPart3' => '#ea9e8c',
    'logoColorPart4' => '#d14426',
    'zoom_default' => '5',
    'zoom_max' => '22',
    'zoom_min' => '0',
];
