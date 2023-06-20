<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<!DOCTYPE html>
<html lang="<?= $app->currentLCode ?>" dir="ltr">
    <head>
        <?php $this->applyTemplateHook('head','begin'); ?>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <?php $this->printDocumentMeta(); ?>
        <title><?= $this->getTitle($entity ?? null) ?></title>
        <link rel="profile" href="//gmpg.org/xfn/11" />
        <link rel='icon' type='image/*' href="<?= $this->asset($app->config['favicon.image16x16'],false)?>"/>
        <link rel='icon' type='image/*' href="<?= $this->asset($app->config['favicon.image32x32'],false)?>"/>
        <link rel="manifest" href="<?=$app->createUrl('site', 'webmanifest')?>">

        <?php $this->printStyles('vendor-v2'); ?>
        <?php $this->printStyles('app-v2'); ?>

        <?php $this->printScripts('vendor-v2'); ?>
        <?php $this->printScripts('app-v2'); ?>

        <?php $this->applyTemplateHook('head','end'); ?>
    </head>

    <body <?php $this->bodyProperties() ?> style="opacity:0" >
        <?php $this->bodyBegin() ?>