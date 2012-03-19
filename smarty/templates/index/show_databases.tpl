{html_css src="/common/common.css"}

<div align="center">
	<form action="/index/generate" method="get">
	<table>
	{foreach from=$dbs item=i}
		<tr>
			<td> <input type="checkbox" value='{{$i.name|escape}}' name='databases[]' {$i.ch}></td>
			<td>{$i.name|escape}</td>
		</tr>
		
		
	{/foreach}
	
		<tr>
			<td colspan="2"><input type="submit" ></td>
		</tr>
	</table>
	</form>
	</form>
</div>
