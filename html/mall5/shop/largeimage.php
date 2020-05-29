<?
define('NEW_WINDOW',true);
include_once("./_common.php");

$sql = " select it_name from $g4[yc4_item_table] where it_id='$it_id' ";
$row = sql_fetch_array(sql_query($sql));
if($row['it_name']){
	$row['it_name'] = get_item_name($row['it_name']);
}

if($default['de_cdn']){
	$imagefile = "http://115.68.20.84/item/$img";
}else{
	$imagefile = "$g4[path]/data/item/$img";
}
//$size = getimagesize($imagefile);

$g4[title] = "$row[it_name] ($it_id)";
include_once("$g4[path]/head.sub.php");
?>
<br>
<div align=center>
    <a href='#' onclick='window.close();'><img id='largeimage' src='<?=$imagefile?>'  alt='<?=addslashes($row['it_name']);?>' border=0 style='border:1 solid #E4E4E4;'></a>
</div>
<p>
<table width=100% cellpadding=0 cellspacing=0>
<tr>
    <td width=30% align=center><a href='#' onclick='window.close();'><img src='<? echo "$g4[shop_img_path]/" ?>/btn_close.gif' border=0 alt="창닫기"></a></td>
    <td width=70% align=right>
        <?
        for ($i=1; $i<=5; $i++)
        {
//			echo get_it_image("{$it_id}_l{$i}",50,50, 'large'.$i,"style='border:1 solid #E4E4E4;'
//                    onmouseover=\"document.getElementById('largeimage').src=document.getElementById('large{$i}').src;\"",true,false);

            if (file_exists("$g4[path]/data/item/{$it_id}_l{$i}"))
                echo "<img id='large{$i}' src='$g4[path]/data/item/{$it_id}_l{$i}' border=0 width=50 heigth=50 style='border:1 solid #E4E4E4;'
                    onmouseover=\"document.getElementById('largeimage').src=document.getElementById('large{$i}').src;\"> &nbsp;";

        }
        ?>
        &nbsp;</td>
</tr>
</table>



</script>
<?
include_once("$g4[path]/tail.sub.php");
?>