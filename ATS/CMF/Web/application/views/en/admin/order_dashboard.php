<ul class="dash-ul" style="padding:10px">
	<?php 
		foreach($orders_count as $o)
			echo "<li>".$o['name'].": ".$o['count']."</li>";
	?>
</ul>
