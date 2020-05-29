
function view_cover(id, formid, nurl, divtype, cover)
{
	if(!id) id = "LayLoginForm";
	if(!divtype) divtype = true;
	if(!cover) cover = true;

	if(cover == true) {
		if(!document.getElementById('div_cover')){
			create_cover();
		}else{
			document.getElementById('div_cover').style.width = '100%';

			if(document.body.clientHeight > document.body.scrollHeight) document.getElementById('div_cover').style.height = '100%';
			else document.getElementById('div_cover').style.height = document.body.scrollHeight;
			document.getElementById('div_cover').style.display = 'block';
		}
	}

	var w = parseInt(document.getElementById(id).style.width);
	var h = parseInt(document.getElementById(id).style.height);
	var window_left = (document.body.clientWidth-w)/2;
	var window_top = (document.body.clientHeight-h)/2;
	this.Lw = h/2;

	if(id) {
		this.Lid = id;
		document.getElementById(id).style.display = '';
		document.getElementById(id).style.top = window_top;
		document.getElementById(id).style.left = window_left;
		if(divtype == true) CheckUIElements();
	}

    document.lay_login_form.mb_id.focus(); 

//	if(formid) document.getElementById('formid').value = formid;
//	if(nurl) document.getElementById('nurl').value = nurl;

	//return true;
}

function CheckUIElements() 
{
    var yMenuFrom, yMenuTo, yButtonFrom, yButtonTo, yOffset, timeoutNextCheck;

    yMenuFrom   = parseInt (document.getElementById(this.Lid).style.top, 10);
    if ( window.document.layers ) 
        yMenuTo = top.pageYOffset + 0;
    else if ( window.document.getElementById ) 
        yMenuTo = document.body.scrollTop + parseInt('30');

    timeoutNextCheck = 500;

    if ( Math.abs (yButtonFrom - (yMenuTo + 152)) < 6 && yButtonTo < yButtonFrom )
     {
        setTimeout ("CheckUIElements()", timeoutNextCheck);
        return;
    }

    if ( yMenuFrom != yMenuTo )
    {
        yOffset = Math.ceil( Math.abs( yMenuTo - yMenuFrom ) / 10 );
        if ( yMenuTo < yMenuFrom )
            yOffset = -yOffset;

        document.getElementById(this.Lid).style.top = (parseInt(document.getElementById(this.Lid).style.top) + yOffset) + 20;

        timeoutNextCheck = 10;
    }

    setTimeout ("CheckUIElements()", timeoutNextCheck);
}

function cover_off(id){
	if(document.getElementById('div_cover')) document.getElementById('div_cover').style.display = 'none';
	if(id) document.getElementById(id).style.display = 'none';
}

function create_cover(){

	var color = '#FFFFFF';
	var opacity = '80';

	var cover_div = document.createElement('div');
	cover_div.style.position = 'absolute';
	cover_div.style.top = '0px';
	cover_div.style.left = '0px';
	cover_div.style.width = '100%';
	cover_div.style.zIndex = 1;
	if(document.body.offsetHeight > document.body.scrollHeight) cover_div.style.height = '100%';
	else cover_div.style.height = document.body.scrollHeight;
	cover_div.style.backgroundColor = color;
	cover_div.style.filter = 'alpha(opacity='+opacity+')';
	cover_div.id = 'div_cover';
	document.body.appendChild(cover_div);
}

