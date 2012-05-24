
function closeAllOpen(){
	$('.real_query_area').hide();
	$('.title_open').show();
	$('.title_close').hide();
}

function openArea(name){
	
	closeAllOpen();
	
	areaid=name+'_query_area';
	closeid=name+ '_title_close';
	openid=name+'_title_open';
	
	$('#'+openid).hide();
	
	$('#'+closeid).show();
	$('#'+areaid).show();
	
}


function closeArea(name){
	areaid=name+'_query_area';
	closeid=name+ '_title_close';
	openid=name+'_title_open';
	
	$('#'+openid).css('display','');
	
	$('#'+closeid).css('display','none');
	$('#'+areaid).css('display','none');
	
}


$(document).ready(function (){
	
	//$('#select_query_area').load('/generator/query?job_id='+select_job_id);
	
});