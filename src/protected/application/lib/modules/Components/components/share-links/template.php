<div class="share-links">

    <h5 class="share-links--title"> {{title}} </h5>

    <div class="share-links--links">
        <a  class="fa fa-twitter" 
            title="Share on Tweet" target="_blank" 
            @click="click('twitter')">
        
            <iconify icon="akar-icons:twitter-fill"></iconify>
        </a>   
        
        <a  class="fa fa-facebook" 
            title="Share on Facebook" target="_blank" 
            @click="click('facebook')">

            <iconify icon="brandico:facebook"></iconify>
        </a>

        <a  class="fa fa-whatsapp hide-mobile" 
            title="Share on WhatsApp" target="_blank"
            @click="click('whatsapp')">
        
            <iconify icon="akar-icons:whatsapp-fill"></iconify>
        </a>

        <a  class="fa fa-whatsapp hide-desktop" 
            title="Share on WhatsApp" target="_blank"
            @click="click('whatsapp-mobile')">
        
            <iconify icon="akar-icons:whatsapp-fill"></iconify>
        </a>        

        <a  class="fa fa-telegram" 
            title="Share on Telegram" target="_blank"
            @click="click('telegram')">

            <iconify icon="cib:telegram-plane"></iconify>
        </a>
    </div>

</div>