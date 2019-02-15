<?php
$this->part('header', $render_data);
$this->part('panel-nav', $render_data);
echo $TEMPLATE_CONTENT;
$this->part('panel-settings-nav', $render_data);

$this->part('footer', $render_data);