<?php defined('IN_IA') or exit('Access Denied');?><?php  if(!empty($diyitem['params']['content'])) { ?>
    <div class="diy-richtext" style="background: <?php  echo $diyitem['style']['background'];?>; padding: <?php  echo $diyitem['style']['padding'];?>px;">
        <?php  echo base64_decode($diyitem['params']['content'])?>
    </div>
<?php  } ?>
<!--6Z2S5bKb5piT6IGU5LqS5Yqo572R57uc56eR5oqA5pyJ6ZmQ5YWs5Y+454mI5p2D5omA5pyJ-->