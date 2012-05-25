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
	$('#popup_dialog').dialog({
		autoOpen : false,
		title : "Select sub query or a table",
		modal : true
	});

	insert_basic_select_query('select_design_area');
	init_click_events();

});

function init_click_events() {

	$('.waitting_insert')
			.on(
					'click',
					function() {
						if ($(this).hasClass('sql_table_or_sub_query')) {
							$('#ajax_content').html('');
							insert_html('ajax_content', 'waitting_icon');
							$('#popup_dialog').dialog('open');

							$('#ajax_content')
									.load(
											'/dbinfo/select_table_or_subquery',
											function() {
												// when user select a option

												$('.table_select_radio_class')
														.on(
																'click',
																function() {
																	random = $(
																			'#table_selector_table_id')
																			.attr(
																					'random');
																	radioId = $(
																			this)
																			.attr(
																					'id');

																	if (radioId
																			.indexOf('sub_query') > 0) {
																		// sub
																		// query
																		preview = "abcd";
																	} else {
																		// some
																		// table
																		preview = "tables";
																	}

																	$(
																			'#table_selecor_preview_area')
																			.html(
																					preview);

																});
											});

						}
					});

}

function insert_basic_select_query(target_div_id) {

	insert_html(target_div_id, 'basic_select_query');
}

function insert_html(htmlToDivId, htmlFromDivId) {
	$('#' + htmlToDivId).append($('#' + htmlFromDivId).html());
}
