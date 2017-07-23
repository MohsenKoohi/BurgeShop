<div class="main">
	<div class="container">
		<h1>{orders_text}</h1>
		<div class="container">
			
			<div class="row results-count" >
				<div class="three columns">
					<label>
						{results_text} {start} {to_text} {end} - {total_results_text}: {total}
					</label>
				</div>
				<div class="three columns results-page-select">
					<select class="full-width" onchange="pageChanged($(this).val());">
						<?php 
							for($i=1;$i<=$total_pages;$i++)
							{
								$sel="";
								if($i == $current_page)
									$sel="selected";

								echo "<option value='$i' $sel>$page_text $i</option>";
							}
						?>
					</select>
				</div>
			</div>

			<script type="text/javascript">

				var rawPageUrl="{raw_page_url}";

				function pageChanged(pageNumber)
				{
					document.location=rawPageUrl+"?page="+pageNumber;
				}
			</script>
		</div>		
		<br><br>
		<div class="row" >
			<?php $i=$start; foreach($orders_info as $order) {?>
				<div class="row even-odd-bg" >
					<div class="two columns">
						<label>{order_number_text}</label>
						<span class="counter"><?php echo $order['order_id'];?></span>
					</div>
					<div class="two columns">
						<label>{date_text}</label>
						<span class='date'><?php echo $order['order_date'];?></span>
					</div>
					<div class="three columns">
						<label>{total_text}</label>
						<span><?php echo price_separator($order['order_total'])." ".$currency_text;?></span>
					</div>
					<div class='three columns'>
						<label>{status_text}</label>
						<span>
							<?php 
								$status_name='order_status_'.$order['order_status'].'_text';
								if(isset($$status_name))
									echo $$status_name;
								else
									echo $order['order_status'];
							?>
						</span>
					</div>
					<div class="two columns align-center">
						<label>&nbsp;</label>					
						<span>
							<a target="_blank" 
								href="<?php echo get_customer_order_details_link($order['order_id']); ?>"
								class="button button-primary sub-primary full-width"
							>
								{view_text}
							</a>
						</span>
					</div>
				</div>
			<?php } ?>
		</div>
	</div>
</div>