<?php
use MapasCulturais\i;

$this->import('entities entity-card');
?>

<div class="home-opportunities">
    <div class="home-opportunities__content">
        <div class="home-opportunities__content--title">
            <label> <?php i::_e('Oportunidades do momento')?> </label>

            <div class="actions">
                <button class="actions--btn" @click="left()">
                    <iconify icon="akar-icons:arrow-left"></iconify>
                </button>

                <button class="actions--btn" @click="right()">
                    <iconify icon="akar-icons:arrow-right"></iconify>
                </button>
            </div>
        </div>

        <div class="home-opportunities__content--description">
            Lorem ipsum dolor sit amet, consectetur adipiscing elit. In interdum et, rhoncus semper et, nulla.
        </div>

        <div class="home-opportunities__content--cards">
            <entities type="opportunity" :select="select" :query="query">
                <template #header="{entities}">

                    <div class="home-opportunities__filter">
                        <form class="home-opportunities__filter--form" @submit="entities.refresh(); $event.preventDefault();">
                            <input v-model="query['@keyword']" class="input" type="text" name="search" />
                            
                            <button class="button" type="submit">
                                <iconify icon="ant-design:search-outlined"></iconify>
                            </button>    
                        </form>

                        <button class="button button--primary button--icon filter">
                            <iconify icon="ic:baseline-filter-alt"></iconify>
                            <?php i::_e('Filtrar')?>
                        </button>
                    </div>

                </template>

                <template #default="{entities}">

                    <ul class="home-opportunities__content--cards-list">
                        <li v-for="entity in entities"> 
                            <entity-card :entity="entity"></entity-card> 
                        </li>
                    </ul>

                </template>
            </entities>
        </div>
    </div>
</div>