<h3 class="registration-header"><?php \MapasCulturais\i::_e("Formulário de Inscrição");?></h3>
<div class="registration-fieldset">
    <h4><?php \MapasCulturais\i::_e("Número da Inscrição");?></h4>
    <div class="registration-id">
        <?php if($action !== 'create'): ?><?php echo $entity->number ?><?php endif; ?>
    </div>
</div>

<?php
$opportunity = $entity->opportunity;

if ($opportunity->projectName):
    ?>
    <div class="registration-fieldset">
        <div id="projectName">
            <span class="label"> 
                <?php \MapasCulturais\i::_e("Nome do Projeto"); ?>
                <?php if ($opportunity->projectName == 2) echo " <span> obrigatório </span>"; ?>   
            </span>
            <div class="attachment-description"><?php \MapasCulturais\i::esc_attr_e("Informe o nome do projeto"); ?></div>            
            <div>
            <!-- TODO: ng-required="requiredField(field)" nao utilizado, deve refatorar para poder utilizar -->
                <input ng-model="entity['projectName']" type="text" value="<?php echo $entity->projectName ?>" ng-blur="saveField({fieldName:'projectName'}, entity['projectName'])" />
            </div>
        </div>
    </div>
<?php endif; ?>
