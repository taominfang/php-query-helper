var query_str, insert_position,current_id;

var mydb;

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

	mydb={};
	// $('#select_query_area').load('/generator/query?job_id='+select_job_id);
	$('#popup_dialog').dialog({
		autoOpen : false,
		title : "Select sub query or a table",
		modal : true,
		width : 400,
		buttons : [ {
			text : "Ok",
			click : function() {
				insert_position.html(query_str);

				insert_position.removeClass('waitting_insert');

				if (!insert_position.hasClass('editable')) {
					insert_position.addClass('editable');
				}

				$(this).dialog("close");
			}
		} ]
	});

	//insert_basic_select_query('select_design_area');
	$('#select_design_area').html(query_root.toHtml());
	init_events();

});

function generate_query_clause(radioId) {
	
	console.log('radioId:'+radioId);
	if(radioId.indexOf('table_selector_radio') <0){
		return;
	}
	
	random = $('#table_selector_table_id').attr('random');

	
	if (radioId.indexOf('sub_query') > 0) {
		// sub
		// query
		query_str = "abcd";
	} else {
		// some
		// table
		tableName = $('#'+radioId).attr('table_name');
		aliasId = random + '_table_selector_alias_t' + $('#'+radioId).attr('myindex');
		aliasName = $('#' + aliasId).val();
		query_str = "`" + tableName + "` as `" + aliasName + '`';
		console.log(query_str);
	}

	$('#table_selecor_preview_area').html(query_str);
}

function inserting_table_or_sub_query(divObject) {
	$('#ajax_content').html('');
	insert_html('ajax_content', 'waitting_icon');
	query_str = "";
	insert_position = divObject;
	$('#popup_dialog').dialog('open');

	$('#ajax_content')
			.load(
					'/dbinfo/select_table_or_subquery',
					function() {
						// when user select a option

						$('.table_select_radio_class').on('click',
								function(){
							generate_query_clause($(this).attr('id'));
						});

						$('.query_table_alias_changable')
								.on(
										'change',
										function() {

											mRadioId = $(this)
													.attr('id')
													.replace(
															'table_selector_alias',
															'table_selector_radio');
											console.log(mRadioId);
											checked=$('#' + mRadioId).attr("checked");
											console.log(checked);
											if (checked == 'checked') {
												generate_query_clause(mRadioId);
											}
										});
					});

}

function init_events() {

	$('.clickable').on('click', function() {
		if ($(this).hasClass('sql_table_or_sub_query')) {

			if ($(this).hasClass('waitting_insert')) {
				inserting_table_or_sub_query($(this));
			}

		}
	});

}

function insert_basic_select_query(target_div_id) {

	insert_html(target_div_id, 'basic_select_query');
}

function insert_html(htmlToDivId, htmlFromDivId) {
	$('#' + htmlToDivId).append($('#' + htmlFromDivId).html());
}
