<?php $this->applyTemplateHook('opportunity-support', 'before'); ?>
<div ng-controller='Support'>
    <div class="aba-content" id="support">
        <?php $this->applyTemplateHook('opportunity-support', 'begin'); ?>
        
        <header></header>
        <div class="support-body"></div>
        <footer></footer>

        <?php $this->applyTemplateHook('opportunity-support', 'end'); ?>
    </div>
</div>
<?php $this->applyTemplateHook('opportunity-support', 'after'); ?>