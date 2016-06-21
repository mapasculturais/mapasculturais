<div id="sobre" class="aba-content">
    <?php if($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
        <div class="highlighted-message clearfix">
            <?php if($this->isEditable() || $entity->registrationFrom): ?>
                <div class="registration-dates">
                    Inscrições abertas de
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationFrom" data-showbuttons="false" data-emptytext="Data inicial"><?php echo $entity->registrationFrom ? $entity->registrationFrom->format('d/m/Y') : 'Data inicial'; ?></strong>
                    a
                    <strong class="js-editable" data-type="date" data-yearrange="2000:+3" data-viewformat="dd/mm/yyyy" data-edit="registrationTo" data-timepicker="#registrationTo_time" data-showbuttons="false" data-emptytext="Data final"><?php echo $entity->registrationTo ? $entity->registrationTo->format('d/m/Y') : 'Data final'; ?></strong>
                    às
                    <strong class="js-editable" id="registrationTo_time" data-datetime-value="<?php echo $entity->registrationTo ? $entity->registrationTo->format('Y-m-d H:i') : ''; ?>" data-placeholder="Hora final" data-emptytext="Hora final"><?php echo $entity->registrationTo ? $entity->registrationTo->format('H:i') : ''; ?></strong>
                    .
                </div>
            <?php endif; ?>
            <?php if ($entity->useRegistrations && !$this->isEditable() ) : ?>
                <a ng-if="data.projectRegistrationsEnabled" class="btn btn-primary" href="#tab=inscricoes" onclick="$('#tab-inscricoes').click()">Inscrições online</a>
            <?php endif; ?>
            <div class="clear" ng-if="data.projectRegistrationsEnabled && data.isEditable">Inscrições online <strong><span id="editable-use-registrations" class="js-editable clear" data-edit="useRegistrations" data-type="select" data-value="<?php echo $entity->useRegistrations ? '1' : '0' ?>"
                    data-source="[{value: 0, text: 'desativadas'},{value: 1, text:'ativadas'}]"></span></strong>
            </div>

        </div>
    <?php endif; ?>
    <div class="ficha-spcultura">
        <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 400): ?>
            <div class="alert warning">O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui <?php echo strlen($entity->shortDescription) ?> caracteres. Você deve alterar seu texto ou este será cortado ao salvar.</div>
        <?php endif; ?>

        <p>
            <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
        </p>
        <?php $this->applyTemplateHook('tab-about-service','before'); ?>
        <div class="servico">
            <?php $this->applyTemplateHook('tab-about-service','begin'); ?>
            <?php if($this->isEditable() || $entity->site): ?>
                <p>
                    <span class="label">Site:</span>
                    <span ng-if="data.isEditable" class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span>
                    <a ng-if="!data.isEditable" class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                </p>
            <?php endif; ?>
            <?php $this->applyTemplateHook('tab-about-service','end'); ?>
        </div>
        <?php $this->applyTemplateHook('tab-about-service','after'); ?>
    </div>

    <?php if ( $this->isEditable() || $entity->longDescription ): ?>
        <h3>Descrição</h3>
        <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Projeto" data-emptytext="Insira uma descrição do projeto" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
    <?php endif; ?>


    <!-- Video Gallery BEGIN -->
    <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
    <!-- Video Gallery END -->

    <!-- Image Gallery BEGIN -->
    <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
    <!-- Image Gallery END -->
</div>
<!-- #sobre -->