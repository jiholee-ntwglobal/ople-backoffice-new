$(function() {
	var el_id = document.getElementById("daum_juso_wrap");
	new daum.Postcode({
		oncomplete: function(data) {

			put_data2(data);
		},
		width : "100%",
		height : "100%"
		//,animation : true
	}).embed(el_id);
}); 