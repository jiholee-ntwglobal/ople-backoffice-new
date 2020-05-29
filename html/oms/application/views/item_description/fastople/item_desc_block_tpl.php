<?php
/**
 * Created by PhpStorm.
 * User: DEV_KKI
 * Date: 2018-05-27
 * File: item_desc_block_tpl.php
 */?>
<section style="border-bottom: solid 1px #e5effc;">
	<h1 style="padding:25px 0 0 25px;margin:0;color:#7fb2e2;font-size:1.2rem;float:left;vertical-align:top;border-top:solid 2px #86b2e4;min-width:18%;">
		<?php echo $block_name; ?>
	</h1>
	<div style="padding: 25px;display: inline-block; max-width:73%; line-height: 1.2rem;">
		<style type="text/css"> span {font-size:15px!important;} p {margin:0 !important;padding:0 !important; font-size:15px}</style>
		<?php echo $description; ?>
        <?php echo $add_description;?>
	</div>
</section>