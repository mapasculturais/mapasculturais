<style>
    .domain-sufix {
        position: relative;
        z-index:1;
    }
    .domain-sufix:after {
        content:'<?php echo $domain_sufix ?>';
        font-family: inherit;
        margin-left:0;
        font-weight: bold;
        padding-right: 20px;
    }
    
    .domain-sufix-editable {
        z-index:0;
        margin-left:-30px;
        position: relative;
    }
</style>
    
            <div>
                <span class="icon"></span><span class="label">Subdomínio:</span>
                <span class="js-editable required domain-sufix" data-edit="url" data-original-title="Subdomínio de <?php echo $domain_sufix ?>" data-emptytext="Ex: nomedacidade"><?php echo $entity_url; ?></span>
                <span class="editable domain-sufix-editable"></span>
            </div>

            <div>
                <span class="icon"></span><span class="label">Domínio Secundário:</span>
                <span class="js-editable" data-edit="aliasUrl" data-original-title="Domínio Secundário" data-emptytext="Ex: mapas.cultura.gov.br"><?php echo $entity->aliasUrl; ?></span>
            </div>