<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div :class="classes" class="mc-share-links">

    <h4 class="mc-share-links--title bold"> {{title}} </h4>

    <div class="mc-share-links--links">
        <a  class="fa fa-twitter" 
            title="Share on Tweet" target="_blank" 
            @click="click('twitter')">
        
            <mc-icon name="twitter"></mc-icon>
        </a>   
        
        <a  class="fa fa-facebook" 
            title="Share on Facebook" target="_blank" 
            @click="click('facebook')">

            <mc-icon name="facebook"></mc-icon>
        </a>

        <a  class="fa fa-whatsapp hide-mobile" 
            title="Share on WhatsApp" target="_blank"
            @click="click('whatsapp')">
        
            <mc-icon name="whatsapp"></mc-icon>
        </a>

        <a  class="fa fa-whatsapp hide-desktop" 
            title="Share on WhatsApp" target="_blank"
            @click="click('whatsapp-mobile')">
        
            <mc-icon name="whatsapp"></mc-icon>
        </a>        

        <a  class="fa fa-telegram" 
            title="Share on Telegram" target="_blank"
            @click="click('telegram')">

            <mc-icon name="telegram"></mc-icon>
        </a>
    </div>

</div>