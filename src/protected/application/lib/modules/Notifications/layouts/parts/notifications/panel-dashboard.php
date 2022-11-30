<?php
/** @var MapasCulturais\Theme $this */

use Doctrine\ORM\Query\Expr\Select;
use MapasCulturais\i;

$this->import('
    tab
    notifications-list
    mapas-card
');

?>

<tab label="<?php i::esc_attr_e('Notificações') ?>" slug="notifications">

<notifications-list></notifications-list>
</tab>