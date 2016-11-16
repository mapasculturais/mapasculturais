<div id="certificate-model1">
    <img class ="background" src="<?php $view->asset('img/modelo_certificado_01.jpg') ?>"/>
    <p class="certificate-content"><?php echo nl2br($msg) ?></p>
    <div class="footer">
        <div class="entity-url">
            <a  href="<?php echo $relation->owner->getSingleUrl(); ?>"
                title="<?php echo $relation->owner->name ?>"><?php echo $relation->owner->getSingleUrl(); ?></a>
        </div>
        <div class="footer-signatures">
            <div class="certificate-seal-owner">
                <p><?php echo $relation->seal->agent->name; ?><br>
                <?php echo $relation->seal->agent->shortDescription; ?></p>
            </div>
            <?php $avatar = $relation->seal->getAvatar();
                if ($avatar){ ?>
                <div class="footer-img certificate-seal-avatar">
                    <img src="<?php echo $avatar->url; ?>"
                        alt="<?php echo $relation->seal->name ?>">
                </div>
            <?php } ?>
            <div class="footer-img certificate-logo-site">
                <img src="<?php $view->asset('img/logo-site.png', true, true) ?>">
            </div>
        </div>
    </div>
</div>
