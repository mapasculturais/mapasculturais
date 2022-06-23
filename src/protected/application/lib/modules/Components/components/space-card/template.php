<?php use MapasCulturais\i;
$this->layout = 'entity'; 
$this->import('card');
?>

<div class="space-card">
    <card class="space-card__card">
        <template #profile>
        <div class="profile">
        <iconify icon="bi:image-fill" />
        </div>
        </template>
        <template #title>
                <h3 class="card-event__title--title"><?php i::_e("Nome do Evento")?></h3>
                <p class="card-event__title--description"><?php i::_e("Evento")?></p>
        </template>
        <template #content>


        </template>
    </card>
</div>