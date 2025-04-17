<?php 
$breadcrumb = $this->breadcrumb ?? [];
if ($breadcrumb) {
    $this->jsObject['breadcrumb'] = $breadcrumb;
}