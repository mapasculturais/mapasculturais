
            <p ng-if="data.entity.canUserModifyRegistrationFields">
                <span class="label"><?php \MapasCulturais\i::_e("Campo de Nome de Projeto");?></span><br>
                <span class="<?php echo $editable_class ?>" data-edit="projectName" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Campo de Nome de Projeto");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Habilite o campo de Nome de Projeto");?>"><?php echo $entity->projectName ?></span>
            </p>