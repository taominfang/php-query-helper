{html_css src="/common/common.css"}

<div align="center">
	
	<table>
	<caption>Select a database as current db</caption>
	{foreach from=$dbs item=i}
		<tr>
			
			<td><a href="/index/generate?databases={$i.name|escape}"> {$i.name|escape}</a></td>
		</tr>
		
		
	{/foreach}
	
		<tr>
			
			<td><a href="/index/generate?nodb=1">No DB </a></td>
		</tr>
	</table>

</div>
