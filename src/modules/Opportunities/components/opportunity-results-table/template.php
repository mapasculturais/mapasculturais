<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    opportunity-registrations-table
');
?>

<opportunity-registrations-table :phase="lastPhase" identifier="registrationsResults" :visible-columns="columns" :avaliable-columns="visibleColumns" hide-filters hide-sort status-not-editable hide-actions hide-title hide-header></opportunity-registrations-table>