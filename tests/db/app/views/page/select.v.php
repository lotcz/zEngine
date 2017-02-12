<table>
	<thead>
		<th>ID</th>
		<th>Value</th>
	</thead>
	<tbody>
		<?php

			$items = $this->data['items'];
			
			foreach ($items as $item) {
				echo sprintf('<tr><td>%s</td><td>%s</td></tr>', $item->val('test_id'), $item->val('test_value'));
			}
		?>
	</tbody>
</table>