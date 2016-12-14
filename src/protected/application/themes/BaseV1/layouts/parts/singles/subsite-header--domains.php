            <div>
                <span class="icon"></span><span class="label">Domínio Principal:</span>
                <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"url") ? 'required': '');?>" data-edit="url" data-original-title="Domínio Principal" data-emptytext="Ex: mapas.cultura.gov.br"><?php echo $entity->url; ?></span>
            </div>

            <div>
                <span class="icon"></span><span class="label">Domínio Secundário:</span>
                <span class="js-editable" data-edit="aliasUrl" data-original-title="Domínio Secundário" data-emptytext="Ex: mapas.cultura.gov.br"><?php echo $entity->aliasUrl; ?></span>
            </div>