<?php
/** @var MapasCulturais\Theme $this */

use Doctrine\ORM\Query\Expr\Select;
use MapasCulturais\i;

$this->import('
    mc-card
    notification-list
    tab
');

?>

<tab label="<?php i::esc_attr_e('Notificações') ?>" slug="notifications">
    <notification-list></notification-list>
</tab>