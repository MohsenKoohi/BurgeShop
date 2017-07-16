<div class="main">
	<div class="container">
		<h1>{order_details_text} <?php echo $order_id;?></h1>
		<div class='row'>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{order_number_text}</div>
				<div class="eight columns"><?php echo $order_id; ?></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{name_text}</div>
				<div class="eight columns">
					<a href="<?php echo get_admin_customer_details_link($order_info['order_customer_id']);?>" >
						<?php echo $order_info['customer_name']; ?>
					</a>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{date_text}</div>
				<div class="eight columns"><span class='date'><?php echo $order_info['order_date']; ?></span></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{total_text}</div>
				<div class="eight columns">
					<span class='date'><?php echo price_separator($order_info['order_total']); ?></span>
					 {currency_text}
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{status_text}</div>
				<div class="eight columns"><?php echo ${'order_status_'.$order_info['order_status']."_text"}	; ?></span></div>
			</div>
		</div>

		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#products">{products_text}</a></li>
				<li><a href="#payments">{payments_text}</a></li>
				<li><a href="#history">{history_text}</a></li>
			</ul>
			<script type="text/javascript">
				$(function(){
				   $('ul.tabs').each(function(){
						var $active, $content, $links = $(this).find('a');
						$active = $($links.filter('[href="'+location.hash+'"]')[0] || $links[0]);
						$active.addClass('active');

						$content = $($active[0].hash);

						$links.not($active).each(function () {
						   $(this.hash).hide();
						});

						$(this).on('click', 'a', function(e){
						   $active.removeClass('active');
						   $content.hide();

						   $active = $(this);
						   $content = $(this.hash);

						   $active.addClass('active');

						   $content.show();						   	

						   e.preventDefault();
						});
					});
				});
			</script>

			<div class="tab" id="products" style="">
				
				<div class='row title-row even-odd-bg'>
					<div class='four columns'>{product_name_text}</div>
					<div class='two columns'>{quantity_text}</div>
					<div class='three columns'>{unit_price_text}</div>
					<div class='three columns'>{total_price_text}</div>
				</div>

				<?php foreach($cart_info as $p){ ?>
					<div class='row even-odd-bg'>
						
					</div>
				<?php } ?>
			</div>

			<div class="tab" id="payments" style="">
			</div>

			<div class="tab" id="history" style="">
			</div>
		</div>
								
			
	</div>
</div>