function closeAllOpen() {
	$('.real_query_area').hide();
	$('.title_open').show();
	$('.title_close').hide();
}

function openArea(name) {

	closeAllOpen();

	areaid = name + '_query_area';
	closeid = name + '_title_close';
	openid = name + '_title_open';

	$('#' + openid).hide();

	$('#' + closeid).show();
	$('#' + areaid).show();

}

function closeArea(name) {
	areaid = name + '_query_area';
	closeid = name + '_title_close';
	openid = name + '_title_open';

	$('#' + openid).css('display', '');

	$('#' + closeid).css('display', 'none');
	$('#' + areaid).css('display', 'none');

}

$(document).ready(function() {

	// $('#select_query_area').load('/generator/query?job_id='+select_job_id);
	$('#select_talbe_or_sub_query').dialog({
		autoOpen : false, 
		title: "Select sub query or a table",
		 modal: true
	});
	
	insert_basic_select_query('select_design_area');
	init_click_events();
	
	
});


function init_click_events(){
	$('.waitting_insert').on('click',function(){
		if($(this).hasClass('sql_table_or_sub_query')){
			$('#select_talbe_or_sub_query').html('');
			insert_html('select_talbe_or_sub_query', 'waitting_icon');
			$('#select_talbe_or_sub_query').dialog('open');
			
			$('#select_talbe_or_sub_query').load('/dbinfo/selectTableOrSubquery',function(){
				//alert("loaded");
			});
			
		}
	});
}

function insert_basic_select_query(target_div_id){
	
	insert_html(target_div_id,'basic_select_query');
}

function insert_html(htmlToDivId,htmlFromDivId){
	$('#'+htmlToDivId).append($('#'+htmlFromDivId).html());
}

