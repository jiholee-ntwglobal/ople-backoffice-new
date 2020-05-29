<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-27
 * File: item_name_block_tpl.php
 */?>
<section style="border-bottom: solid 1px #e5effc;">
	<h1 style="padding:25px 0 0 25px;margin:0;color:#7fb2e2;font-size:1.2rem;float:left;vertical-align:top;border-top:solid 2px #86b2e4;min-width:18%;">
		제품명
	</h1>
	<div style="padding: 25px;display: inline-block;">
		<p style="padding:0;margin:0;"><?php echo $item_name; ?></p>
		<p style="padding:0;margin:0;">
            <img src="<?php echo $item_img; ?>" width="450" height="450"/>
            <!--  NO img로 고정된 것 - KSJ <img src="http://www.ople.com/mall5/shop/img/no_image_400.gif" alt="sample" width="450" height="450"/> -->
        </p>
	</div>
</section>