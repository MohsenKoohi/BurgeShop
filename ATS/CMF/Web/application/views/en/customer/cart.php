<div class="main">

	<div class="container category">
		<?php if($products){ ?>
			<h1>{products_text}</h1>
			<br>

			<div class='row even-odd-bg'>
				<div class='three columns text-center'><b>{product_text}</b></div>
				<div class='two columns'><b>{price_text} ({currency_text})<b></div>
				<div class='three columns'><b>{quantity_text}</b></div>
				<div class='three columns'><b>{total_price_text}</b></div>
			</div>
			<?php
				foreach($products as $product)
				{
			?>
					<div class="row even-odd-bg">
						<div class="three columns">
							<a href="<?php echo get_customer_product_details_link($product['product_id'],$product['product_name']);?>" >
								<b><?php echo $product['product_name'];?></b>
								<ul class='dash-ul'>
									<?php 
										foreach($product['options'] as $type => $value)
											echo "<li>".$type.": ".$value."</li>";
									?>
								</ul>
							</a>
						</div>

						<div class='two columns date'>
							<span class='date'>
								<?php echo price_separator($product['price']);?>
							</span>
						</div>

						<div class='two columns'>
							<?php echo $product['quantity'];?>
						</div>

						<div class='two columns date'>
							<?php echo price_separator($product['quantity'] * $product['price']);?>
						</div>
						
					</div>
			<?php
				}
			?>
		<?php } ?>
	</div>
</div>
