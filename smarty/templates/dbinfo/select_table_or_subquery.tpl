
<div id="table_selecor_preview_area" class="preview_area">

</div>

<div>
	<table id="table_selector_table_id" random={$random}>
		<caption></caption>
		<thead>
			<tr>
				<th></th>
				<th></th>
				<th>Alias</th>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td><input name="table_selector" type="radio"
					class="table_select_radio_class"
					id="{$random}_table_selector_radio_sub_query">
				</td>
				<td>Sub query</td>
				<td><input name="table_selector" type="text"
					id="{$random}_table_selector_alias_sub_query">
				</td>
			</tr>
			{foreach from=$tables item=i name=foo}

			<tr>
				<td><input name="table_selector" type="radio"
					class="table_select_radio_class"
					id="{$random}_table_selector_radio_t{$smarty.foreach.foo.index}"
					table_name='{$i.name}'>
				</td>
				<td>{$i.name}</td>
				<td><input name="table_selector" type="text"
					id="{$random}_table_selector_alias_t{$smarty.foreach.foo.index}"
					value='{$i.name}'>
				</td>
			</tr>


			{/foreach}
		</tbody>

	</table>
</div>


