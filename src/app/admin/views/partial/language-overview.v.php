<table class="table table-sm">
	<?php
	foreach ($language_stats as $lang => $count) {
		?>
		<tr>
			<td><?=$lang?></td>
			<td><?=$count?></td>
		</tr>
		<?php
	}
	?>
</table>
