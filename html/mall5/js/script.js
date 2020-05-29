$(function(){
	// 김선용 201207 :
    $('.auto-search').autocomplete(g4_path+'/data.php?mode=sql', {
		minChars : 2,
		//autoFill : true,
		selectFirst : false,
        width: 100,
        max: 10
    });
	$('.auto-search-kwan').autocomplete(g4_path+'/data.php?kwan=ok&mode=sql', {
		minChars : 2,
		//autoFill : true,
		selectFirst : false,
        width: 100,
        max: 10
    });
	
});
