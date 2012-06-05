 {html_css src="generate"} {html_css src="/common/common"} {html_script
src="generate"}


<script type="text/javascript">
<!--
var select_job_id='{$select_job_id}';
//-->
</script>

<div>

	<!-- -----------select_area --------------------------- -->
	<div id='select_area' class="query_area">
		<h2 id="select_title_open" class="title_open">
			<a href="javascript:openArea('select');">+Select</a>
		</h2>
		<h2 id="select_title_close" class="title_close hide">
			<a href="javascript:closeArea('select');">-Select</a>
		</h2>
		<div id="select_query_area" class="real_query_area">

			<div id="select_result_area">
				<textarea id="select_result_text"></textarea>
			</div>
			<div id="select_represent_area"></div>
			<div id="select_design_area"></div>
		</div>
	</div>

	<!-- end select_area -->
	<br />


	<!-- -----------update_area --------------------------- -->
	<div id='update_area' class="query_area">
		<h2 id="update_title_open" class="title_open">
			<a href="javascript:openArea('update');">+Update</a>
		</h2>
		<h2 id="update_title_close" class="title_close" style="display: none;">
			<a href="javascript:closeArea('update');">-Update</a>
		</h2>
		<div id="update_query_area" class="real_query_area"
			style="display: none;"></div>
	</div>
	<!-- end update_area -->

	<br />
	<!-- -----------insert_area --------------------------- -->
	<div id='insert_area' class="query_area">
		<h2 id="insert_title_open" class="title_open">
			<a href="javascript:openArea('insert');">+Insert</a>
		</h2>
		<h2 id="insert_title_close" class="title_close" style="display: none;">
			<a href="javascript:closeArea('insert');">-Insert</a>
		</h2>
		<div id="insert_query_area" class="real_query_area"
			style="display: none;"></div>
	</div>
	<!-- end insert_area -->

	<br />
	<!-- -----------delete_area --------------------------- -->
	<div id='delete_area' class="query_area">
		<h2 id="delete_title_open" class="title_open">
			<a href="javascript:openArea('delete');">+Delete</a>
		</h2>
		<h2 id="delete_title_close" class="title_close" style="display: none;">
			<a href="javascript:closeArea('delete');">-Delete</a>
		</h2>
		<div id="delete_query_area" class="real_query_area"
			style="display: none;"></div>
	</div>
	<!-- end delete_area -->
	<br />
</div>


<div id="popup_dialog">
	<div id="ajax_content" class=""></div>
</div>



<div id="basic_select_query" class="hide">
	<div class="sql_clause float_left_element">
		<div class="sql_keyword float_left_element sql_element uneditable">SELECT</div>
		<div class="sql_column float_left_element sql_element editable">*</div>
		<div class="sql_keyword float_left_element sql_element uneditable">FROM</div>
		<div
			class="sql_table_or_sub_query float_left_element sql_element waitting_insert clickable required"></div>
	</div>
</div>

<div id='waitting_icon' class="hide">
	<img alt="" src="/images/loading.gif" width="50px" height="50px">
</div>
