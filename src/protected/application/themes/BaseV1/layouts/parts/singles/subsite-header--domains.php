            <div>
                <span class="icon"></span><span class="label"><?php \MapasCulturais\i::_e('Domínio Principal:'); ?></span>
                <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"url") ? 'required': '');?>" data-edit="url" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Domínio Principal'); ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Ex: mapas.cultura.gov.br'); ?>"><?php echo $entity->url; ?></span>
            </div>

            <div>
                <span class="icon"></span><span class="label"><?php \MapasCulturais\i::_e('Domínio Secundário:'); ?></span>
                <span class="js-editable" data-edit="aliasUrl" data-original-title="Domínio Secundário" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Ex: mapas.cultura.gov.br'); ?>"><?php echo $entity->aliasUrl; ?></span>
            </div>
