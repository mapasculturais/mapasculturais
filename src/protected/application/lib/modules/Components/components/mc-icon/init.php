<?php
// https://icon-sets.iconify.design/
$iconset = [
    // entidades
    'app' => 'mdi:puzzle-outlin',
    'user' => 'fa-solid:user-friends',
    'agent' => 'fa-solid:user-friends',
    'agent-1' => 'fa-solid:user',
    'agent-2' => 'fa-solid:user-friends',
    'space' => 'clarity:building-solid',
    'event' => 'bxs:calendar-event',
    'project' => 'ri:file-list-2-fill',
    'opportunity' => 'mdi:lightbulb-on',

    // redes sociais
    'facebook' => 'brandico:facebook',
    'github' => 'la:github-alt',
    'instagram' => 'fa6-brands:instagram',
    'linkedin' => 'akar-icons:linkedin-box-fill',
    'pinterest' => 'fa6-brands:pinterest-p',
    'spotify' => 'akar-icons:spotify-fill',
    'telegram' => 'cib:telegram-plane',
    'twitter' => 'akar-icons:twitter-fill',
    'whatsapp' => 'akar-icons:whatsapp-fill',
    'youtube' => 'brandico:vimeo',
    'vimeo' => 'akar-icons:youtube-fill',


    // IMPORTANTE: manter ordem alfabética
    'access' => 'ooui:previous-rtl',
    'add' => 'ps:plus',
    'archive' => 'mi:archive',
    'exchange' => 'material-symbols:change-circle-outline',
    'code' => 'fa-solid:code',
    'close' => 'gg:close',
    'down' => 'mdi:chevron-down',
    'dashboard' => 'mdi:view-dashboard-outline',
    'delete' => 'gg:close',
    'download' => 'el:download-alt',
    'edit' => 'zondicons:edit-pencil',
    'favorite' => 'mdi:star-outline',
    'filter' => 'ic:baseline-filter-alt',
    'home' => 'ci:home-fill',
    'image' => 'bi:image-fill',
    'link' => 'cil:link-alt',
    'list' => 'ci:list-ul',
    'loading' => 'eos-icons:three-dots-loading',
    'login' => 'icon-park-outline:login',
    'map' => 'bxs:map-alt',
    'menu-mobile' => 'icon-park-outline:hamburger-button',
    'network' => 'grommet-icons:connect',
    'next' => 'ooui:previous-rtl',
    'notification' => 'eva:bell-outline',
    'previous' => 'ooui:previous-ltr',
    'search' => 'ant-design:search-outlined',
    'settings' => 'mdi:cog-outline',
    'sort' => 'mdi:sort',
    'trash' => 'ooui:trash',
    'up' => 'mdi:chevron-up',
    'upload' => 'ic:baseline-file-upload',
];

$app->applyHook('component(mc-icon).iconset', [&$iconset]);

$this->jsObject['config']['iconset'] = $iconset;