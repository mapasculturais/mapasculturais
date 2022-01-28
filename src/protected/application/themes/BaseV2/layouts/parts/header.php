<!DOCTYPE html>
<html lang="<?= $app->currentLCode ?>" dir="ltr">
    <head>
        <?php $this->applyTemplateHook('head','begin'); ?>
        <meta charset="UTF-8" />
        <?php $this->printDocumentMeta(); ?>
        <title><?= $this->getTitle($entity ?? null) ?></title>
        <link rel="profile" href="//gmpg.org/xfn/11" />
        <link rel="shortcut icon" href="<?php $this->asset('img/favicon.ico') ?>" />

        <?php $this->printStyles('vendor-v2'); ?>
        <?php $this->printStyles('app-v2'); ?>
        <?php $this->printJsObject(); ?>

        <?php $this->printScripts('vendor-v2'); ?>
        <?php $this->printScripts('app-v2'); ?>

        <?php $this->applyTemplateHook('head','end'); ?>
    </head>

    <body <?php $this->bodyProperties() ?> >
        <?php $this->applyTemplateHook('body','begin'); ?>
        <section id="main-app">

