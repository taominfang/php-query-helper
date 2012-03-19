
function openArea(name){
	areaid=name+'_query_area';
	closeid=name+ '_title_close';
	openid=name+'_title_open';
	
	$('#'+openid).css('display','none');
	
	$('#'+closeid).css('display','');
	$('#'+areaid).css('display','');
	
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
	
	$('#select_query_area').load('/generator/query?job_id='+select_job_id);
	
});