<?php 
$breadcrumb = $this->breadcrumb ?? [];
if ($breadcrumb) {
    $app->applyHookBoundTo($this, "component(registration-edit-breadcrumb):after", [&$breadcrumb]);
    $this->jsObject['breadcrumb'] = $breadcrumb;
}