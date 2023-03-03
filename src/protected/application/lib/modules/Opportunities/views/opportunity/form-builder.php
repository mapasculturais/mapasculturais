<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-header
')
?>

<div class="main-app form-builder">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>

    <div class="form-builder__content">
        <div class="grid-12 form-builder__bg-content">
            <div class="col-8 form-builder__title">
                <p class="opportunity__color">1. Nome da etapa</p>
            </div>
            <div class="col-2 form-builder__period">
                <h5 class="period_label">Data de início</h5>
                <h5 class="opportunity__color">05/03/2022</h5>
            </div>
            <div class="col-2 form-builder__period">
                <h5 class="period_label">Data final</h5>
                <h5 class="opportunity__color">05/03/2022</h5>
            </div>
        </div>

        <div class="grid-12 form-builder__label-btn">
            <div class="col-12">
                <h3>Configuração de formulário de coleta de dados</h3>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-builder__bg-content form-builder__bg-content--spacing">
                <h4>Categorias de inscrição</h4>
                <span class="subtitle">Crie opções para as pessoas escolherem na hora de se inscrever, como, por exemplo, "categorias" ou "modalidades".</span>
                <div>
                    <p>Grupo de categorias</p>
                    <input type="text" />
                </div>
                <div>
                    <p>Descrição do grupo</p>
                    <input type="text" />
                </div>
                <div>
                    <p>Categorias</p>
                    <input type="text" />
                    <button class="button--primary-outline button"><mc-icon name="add"></mc-icon> Adicionar</button>
                </div>
            </div>
            <div class="form-builder__bg-content form-builder__bg-content--spacing">
                <div>
                    <h4>Permitir Agente Coletivo?</h4>
                    <span class="subtitle">Permitir inscrição de Agente Coletivo</span>
                    <div>
                        <input type="radio" name="allowAgent" value="true">
                        <label for="html">Sim</label>
                        <input type="radio" name="allowAgent" value="false">
                        <label for="css">Não</label>
                    </div>
                </div>
                <div>
                    <h4>Permitir instituição responsável?</h4>
                    <span class="subtitle">Permitir inscrição de instituições</span>
                    <div>
                        <input type="radio" name="allowEntity" value="true">
                        <label for="html">Sim</label>
                        <input type="radio" name="allowEntity" value="false">
                        <label for="css">Não</label>
                    </div>
                </div>
                <div>
                    <h4>Vagas</h4>
                    <input type="number" />
                </div>
                <div>
                    <h4>Inscrições por agente</h4>
                    <input type="number" />
                </div>
            </div>
        </div>

        <div class="grid-2">
            <div class="form-builder__bg-content form-builder__bg-content--spacing">
                <h4>Permitir vínculo de Espaço?</h4>
                <span class="subtitle">Permitir um espaço para associar à inscrição.</span>
                <div>
                    <input type="radio" name="allowSpace" value="true">
                    <label for="html">Sim</label>
                    <input type="radio" name="allowSpace" value="false">
                    <label for="css">Não</label>
                </div>
            </div>
            <div class="form-builder__bg-content form-builder__bg-content--spacing">
                <h4>Habilitar informações de Projeto?</h4>
                <span class="subtitle">Permitir que proponente vizualise informações básicas sobre um projeto.</span>
                <div>
                    <input type="radio" name="allowSpace" value="true">
                    <label for="html">Sim</label>
                    <input type="radio" name="allowSpace" value="false">
                    <label for="css">Não</label>
                </div>
            </div>
        </div>


    </div>

    <entity-actions :entity="entity" editable></entity-actions>
</div>
