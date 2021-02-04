<?php
$result = [];
foreach ($legends as $key_l => $legend){   
   $result[$legend] = $colors[$key_l];   
}


?>
<div class="legends-chats">
 
<?php foreach ($result as $key => $value){?>
    
    <span style="background-color:<?=$value?>"><?=$key?></span>  <br>
<?php } ?>

</div>