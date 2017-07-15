<div class="main">
	<div class="container">
		<h1>{order_details_text} <?php echo $order_info[0]['inv_id'];?></h1>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{order_number_text}</div>
				<div class="eight columns"><?php echo $order_info[0]['inv_id']; ?></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{name_text}</div>
				<div class="eight columns">
					<a href="<?php echo get_admin_customer_details_link($order_info[0]['customer_id']);?>" >
						<?php echo $order_info[0]['customer_name']; ?>
					</a>
				</div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{date_text}</div>
				<div class="eight columns"><?php echo $order_info[0]['inv_date']; ?></div>
			</div>
			<div class="row even-odd-bg dont-magnify" >
				<div class="three columns">{reference_code_text}</div>
				<div class="eight columns"><?php echo $order_info[0]['inv_ref_code']; ?></div>
			</div>
								
			<?php	$counter=0;foreach($order_info as $order) { ?>

				<div class="row even-odd-bg dont-magnify" >
					<div class="one columns">
						<label>&nbsp;</label>
						<span class="counter">#<?php echo ++$counter;?></span>
					</div>
					<div class="two columns">
						<label>{product_name_text}</label>
						<span><?php echo $order['pr_name'];?></span>
					</div>
					<div class="two columns">
						<label>{unit_price_text}</label>
						<span><?php echo price_separator($order['pr_price']);?></span>
					</div>
					<div class="one columns">
						<label>{count_text}</label>
						<span><?php echo $order['pr_count'];?></span>
					</div>
					<div class="two columns">
						<label>{total_price_text}</label>
						<span><?php  echo price_separator($order['pr_price']*$order['pr_count']);?></span>
					</div>
					<div class="one columns">
						<label>{status_text}</label>
						<span><?php echo ${"home_status_".$order['hm_status']."_text"};?></span>
					</div>
					
					<div class="three columns">
						<?php echo $order['hm_province']." - ".$order['hm_city']." - ".$order['hm_sub_city']." - ".$order['hm_subject'];?>
						<br>
						<a target="_blank" class='button sub-primary button-type1 five columns' href='<?php echo get_admin_home_details_link($order['hm_id']);?>'>
								{view_text}
						</a>
						<div class="one column">&nbsp;</div>
						<?php if($order['hm_status'] === 'finalized') { ?>
							<a target="_blank" class='button sub-primary button-type2 five columns' href='<?php echo get_home_public_page_link($order['hm_province'],$order['hm_city'],$order['hm_id'],$order['hm_subject']);?>'>
								{public_page_text}
							</a>
						<?php } ?>

					</div>
				</div>	
			<?php } ?>

			<?php if($order['inv_agent_discount_percent']) { ?>
				<div class="row even-odd-bg dont-magnify" >
					<div class="one columns"></div>
					<div class="five columns">
						<span class="">جمع جز ({currency_text})</span>
					</div>
					<div class="two columns">
						<span><?php echo price_separator($order['inv_sub_total']); ?></span>
					</div>
				</div>
				<div class="row even-odd-bg dont-magnify" >
					<div class="one columns"></div>
					<div class="five columns">
						<span class="">تخفیف ({currency_text})</span>
					</div>
					<div class="two columns">
						<span><?php echo price_separator($order['inv_agent_discount']); ?></span>
					</div>
				</div>
			<?php } ?>
			<div class="row even-odd-bg dont-magnify" >
				<div class="one columns"></div>
				<div class="five columns">
					<span class="">جمع کل ({currency_text})</span>
				</div>
				<div class="two columns">
					<span><?php echo price_separator($order['inv_total']); ?></span>
				</div>
			</div>
	</div>
</div>