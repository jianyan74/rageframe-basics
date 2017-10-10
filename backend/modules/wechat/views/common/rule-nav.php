<?php
use yii\helpers\Url;

?>

<li <?php if($nav_type == 1){ ?>class="active"<?php } ?>><a href="<?= Url::to(['rule/index'])?>"> 关键字自动回复</a></li>
<li <?php if($nav_type == 2){ ?>class="active"<?php } ?>><a href="<?= Url::to(['setting/special-message'])?>"> 非文字自动回复</a></li>
<li <?php if($nav_type == 3){ ?>class="active"<?php } ?>><a href="<?= Url::to(['reply-default/index'])?>"> 关注/默认回复</a></li>