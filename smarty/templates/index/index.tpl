{html_css src="/common/common.css"}

<div align="center">

	<form action="/index/show_databases" method="post">

		<table class="table">
			<tr>
				<td class="required">Host:</td>
				<td><input type="text" name="host" size="40"
					value='{if !empty($host)}{$host}{/if}'></td>
			</tr>
			<tr>
				<td class="required">User:</td>
				<td><input type="text" name="user"
					value='{if !empty($user)}{$user}{/if}'></td>
			</tr>
			<tr>

				<td class="">Password:</td>
				<td><input type="password" name="password"></td>
			</tr>
			<tr>
				<td class="">Database:</td>
				<td><input type="text" name="database"
					value='{if !empty($database)}{$database}{/if}'></td>
			</tr>
			<tr>
				<td class="">Force using local db cache:</td>
				<td><input type="checkbox" name="usingcache"
					value='1'></td>
			</tr>

			<tr>
				<td colspan="2"><input type="submit"></td>
			</tr>
		</table>
	</form>

</div>
