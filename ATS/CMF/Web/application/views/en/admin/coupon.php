<div class="main">
	<div class="container">
		<h1>{coupons_text}</h1>
		<div class="row general-buttons">
			<a class="two columns" onclick="$('#new-coupon').submit();">
				<div class="full-width button sub-primary button-type1 half-col-margin">
					{add_new_coupon_text}
				</div>
			</a>

			<?php 
				echo form_open($page_link,array("id"=>"new-coupon"));
				echo "<input type='hidden' name='post_type' value='add_new_coupon'/>";
				echo form_close();
			?>
		</div>
		<br><br>
		<div class="container">
			<?php 
				if($coupons)
					foreach($coupons as $c) { 
			?>
				<div class="row even-odd-bg" >
					<div class="one columns counter">
						<label >#<?php echo $c['coupon_id'];?></label>
					</div>
					<div class="three columns">
						<label>{name_text}</label>
						<span>
							<?php if($c['coupon_name']) echo $c['coupon_name']; else echo $no_title_text;?>
						</span>
					</div>
					<div class="three columns">
						<label>{code_text}</label>
						<span>
							<?php 
								echo $c['coupon_code'];
							?>
						</span>
					</div>
					<div class="three columns">
						<label>{expiration_date_text}</label>
						<span>
							<span style="display:inline-block" class="ltr"><?php  echo $c['coupon_expiration_date'];?> </span>
						</span>
					</div>
					<div class="two columns">
						<label>{details_text}</label>
						<a target="_blank" class="button button-type2 sub-primary twelve columns" href="<?php echo get_admin_coupon_details_link($c['coupon_id']);?>">
							{view_text}
						</a>
					</div>
				</div>
			<?php } ?>
		</div>

	</div>
</div>