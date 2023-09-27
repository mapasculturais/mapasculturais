<?php
$this->part('header', $render_data);
$this->part('main-header', $render_data);
echo $TEMPLATE_CONTENT;
$this->part('main-footer', $render_data);
$this->part('footer', $render_data);