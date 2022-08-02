<?php
// https://icon-sets.iconify.design/
$iconset = [
    'agent' => 'fa-solid:user',
    'agent-1' => 'fa-solid:user',
    'agent-2' => 'fa-solid:user-friends',
    'space' => 'clarity:building-line',
    'event' => 'ant-design:calendar-twotone',
    'project' => 'ri:file-list-2-line',
    'opportunity' => 'icons8:idea',

    'edit' => 'zondicons:edit-pencil',
    'home' => 'fluent:home-12-regular'
    // @todo completar
];

$app->applyHook('component(mc-icon).iconset', [&$iconset]);

$this->jsObject['config']['iconset'] = $iconset;