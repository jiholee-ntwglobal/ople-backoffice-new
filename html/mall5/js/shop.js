// 뒤가 비치는 투명버젼
function flashview (dirNswf,fwidth,fheight,params) {
 var flashobjec="";
 flashobjec+="		<object classid=\'clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\' codebase=\'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0\' width=\'"+fwidth+"\' height=\'"+fheight+"\'>";
 flashobjec+="      <param name=\'movie\' value=\'"+dirNswf+"\'>";
 flashobjec+="      <param name=\'wmode\' value=\'transparent\'>";
 flashobjec+="      <param name=\'quality\' value=\'high\'>";
 flashobjec+="      <param name=\'menu\' value=\'false\'>";
 flashobjec+="      <embed src=\'"+dirNswf+"\' width=\'"+fwidth+"\' height=\'"+fheight+"\' quality=\'high\' pluginspage=\'http://www.macromedia.com/go/getflashplayer\' type=\'application/x-shockwave-flash\' menu=\'false\'></embed>";
 flashobjec+="    </object>";

 document.write(flashobjec);
}
// 뒤가 비치는 투명버젼끝
if (typeof(SHOP_JS) == 'undefined') { // 한번만 실행
    var SHOP_JS = true;

    // 큰이미지 창
    function popup_large_image(it_id, img, width, height, cart_dir)
    {
        var top = 10;
        var left = 10;
        url = cart_dir+"/largeimage.php?it_id=" + it_id + "&img=" + img;
        width = width + 50;
        height = height + 100;
        opt = 'scrollbars=yes,width='+width+',height='+height+',top='+top+',left='+left;
        popup_window(url, "largeimage", opt);
    }
}