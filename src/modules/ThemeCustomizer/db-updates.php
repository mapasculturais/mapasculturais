<?php

use function MapasCulturais\__exec;

return [
    'create new column short_description in subsite table' => function () {
        __exec("ALTER TABLE subsite ADD short_description text;");
    },
];