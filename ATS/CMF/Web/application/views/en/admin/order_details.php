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

		<br><br>
		<div class="tab-container">
			<ul class="tabs">
				<li><a href="#products">{products_text}</a></li>
				<li><a href="#payment">{payment_text}</a></li>
				<li><a href="#status">{status_text}</a></li>
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
						<div class='four columns'>
							<a href="<?php echo get_admin_product_details_link($p['product_id']);?>">
								<b><?php echo $p['name'];?></b>
								<br>
								<ul class='dash-ul'>
									<?php 
										foreach($p['options'] as $o)
										{
											$type=$o['type'];
											$value=$o['value'];
											echo "<li>$type: $value</li>";
										} 
									?>
								</ul>
							</a>
						</div>
						<div class='two columns align-center'>
							<?php echo $p['quantity'];?>
						</div>
						<div class='three columns align-center'>
							<?php echo price_separator($p['price']);?>
						</div>
						<div class='three columns align-center'>
							<?php echo price_separator($p['quantity']*$p['price']);?>
						</div>
					</div>
				<?php } ?>
			</div>

			<div class="tab" id="payment" style="">
				<?php foreach($payments_info as $p){ ?>
					<div class='row even-odd-bg'>
						<div class='three columns'>
							<label>{payment_method_text}</label>
							<span><?php echo ${"payment_method_".$p['method']."_text"};?></span>
							<small>(<?php echo $p['id'];?>)</small>
						</div>

						<div class='three columns'>
							<label>{date_text}</label>
							<span class='date'><?php echo $p['date'];?></span>
						</div>

						<div class='three columns'>
							<label>{status_text}</label>
							<span><?php echo ${'payment_status_'.$p['status'].'_text'};?></span>
						</div>

						<div class='three columns'>
							<label>{reference_code_text}</label>
							<span class='date'><?php echo $p['reference'];?></span>
						</div>

						<?php foreach($p['history'] as $h){ ?>
							<div class='row separated'>
								<div class='three columns'>
									<label>{date_text}</label>
									<span class='date' title='<?php echo $h['id'];?>'>
										<?php echo $h['date'];?>
									</span>
								</div>

								<div class='three columns'>
									<label>{status_text}</label>
									<span><?php echo ${'payment_status_'.$h['status'].'_text'};?></span>
								</div>

								<div class='six columns'>
									<label>{comment_text}</label>
									<span class=''>
										<?php 
											if($h['comment'])
												foreach($h['comment'] as $index => $value)
													echo ${'payment_comment_'.$index."_text"}.": ".$value."<br>";
										?>
									</span>
								</div>
							</div>
						<?php } ?>
						
					</div>
				<?php } ?>
			</div>

			<div class="tab" id="status" style="">
			</div>
		</div>
								
			
	</div>
</div>