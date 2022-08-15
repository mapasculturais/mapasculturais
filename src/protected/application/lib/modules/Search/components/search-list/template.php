<?php
use MapasCulturais\i;
$this->import('entities mapas-card entity-card');
?>
    <entities :type="type" :select="select" :limit="limit">
        <template #header="{entities}">
            <aside>
                <mapas-card>
                    <template #title>
                        <label><?php i::_e("Filtro de Entidades"); ?></label>
                    </template>
                    <template #content>
                        <form class="grid-12">
                            <div class="col-12">
                                <div class="field">
                                    <label for="EntityState">Status da Entidade</label> 
                                    <label><input id="EntityState" type="checkbox"> Entidades oficiais</label>
                                </div>  
                            </div>

                            <div class="col-12">
                                <div class="field">
                                    <label for="EntityType">
                                    Tipo de Entidade
                                    </label>
                                    <select id="EntityType" name="Entity-type">
                                        <option value="1">Agente Individual</option>
                                        <option value="2">Agente Coletivo</option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="field">
                                    <label for="entityArea">
                                    Área de Atuação
                                    </label>
                                    <select id="entityArea" name="entity-area">
                                        <option value="1">Danca</option>
                                        <option value="2">Musica</option>
                                    </select>
                                </div>
                            </div>

                        </form>
                    </template>
                </mapas-card>
            </aside> 
        </template>

        <template  #default="{entities}">
            <main>
                <entity-card :entity="entity"  v-for="entity in entities" :key="entity.id"></entity-card> 
            </main>
        </template>
    </entities>