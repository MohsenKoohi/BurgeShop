<div class="main">
	<div class="container">
		<h1>{coupon_text} {coupon_id}
			<?php 
			if($info && $info['coupon_name']) 
				echo $comma_text." ".$info['coupon_name']
			?>
		</h1>		
		<?php 
			if(!$info) {
		?>
			<h4>{not_found_text}</h4>
		<?php 
			}else{ 
		?>
			<div class="container">
				<div class="row general-buttons">
					<?php if(!$orders){ ?>
						<div class="two columns button sub-primary button-type2" onclick="deleteCoupon()">
							{delete_text}
						</div>
					<?php } ?>
				</div>
				<br><br>
				<div class="tab-container">
					<ul class="tabs">
						<li><a href="#props">{properties_text}</a></li>		
						<li><a href="#orders">{orders_text}</a></li>				
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

					<div class="tab" id="props">

						<?php echo form_open('')?>
							<input type='hidden' name='post_type' value='edit_coupon'/>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{name_text}</span>
								</div>
								<div class="eight columns">
									<input type='text' class='full-width' name='name' value='<?php echo $info['coupon_name'];?>'/>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{code_text}</span>
								</div>
								<div class="eight columns">
									<input type='text' class='full-width lang-en' name='code' value='<?php echo $info['coupon_code'];?>'/>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{active_text}</span>
								</div>
								<div class="eight columns">
									<input type='checkbox' class='graphical' name='active' <?php if($info['coupon_active']) echo 'checked';?>/>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{min_price_text} ({currency_text})</span>
								</div>
								<div class="eight columns">
									<input type='text' class='full-width ltr' name='min_price' value='<?php echo $info['coupon_min_price'];?>'/>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{expiration_date_text}</span>
								</div>
								<div class="eight columns">
									<input type='text' class='full-width ltr' name='expiration_date' value='<?php echo $info['coupon_expiration_date'];?>'/>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{discount_type_text}</span>
								</div>
								<div class="eight columns">
									<select type='text' class='full-width' name='value_type'>
										<option value='currency'
											 <?php if($info['coupon_value_type']=='currnecy') echo 'selected';?>
										>{currency_text}</option>

										<option value='percent'
											 <?php if($info['coupon_value_type']=='percent') echo 'selected';?>
										>{percent_text}</option>
									</select>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{discount_value_text}</span>
								</div>
								<div class="eight columns">
									<input type='text' class='full-width ltr' name='value' value='<?php echo $info['coupon_value'];?>'/>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{usage_number_text}</span>
									<br><span class='ltr-inb'>-1</span> = {unlimited_text}
								</div>
								<div class="eight columns">
									<input type='text' class='full-width ltr' name='usage_number' value='<?php echo $info['coupon_usage_number'];?>'/>
								</div>
							</div>

							<div class='row even-odd-bg'>
								<div class='three columns'>
									<span>{to_be_used_by_text}</span>
								</div>
								<div class="nine columns">
									<select class='six columns' name='customers_type' onchange='customersTypeChanged(this);'>
										<option value='-1'
											<?php if($customers == -1) echo 'selected';?>
											>{all_customers_text}</option>
										<option value='0' 
											<?php if($customers != -1) echo 'selected'?>
											>{selected_customers_text}</option>
									</select>
									<div class='full-width' id='select-customers'>
										<br><br>
										<input type='text' id="customer" class='six columns' placeholder="{search_customer_name_text}"/>
										<br>
										<div class='row separated aclist' id="customers">
										</div>
									</div>
								</div>
							</div>
							<br><br>
							<div class="row">
								<div class="four columns">&nbsp;</div>
								<input type="submit" class=" button-primary four columns" value="{submit_text}"/>
							</div>
						<?php echo form_close();?>

						<link rel="stylesheet" type="text/css" href="{styles_url}/jquery-ui.min.css" />
						<script src="{scripts_url}/jquery-ui.min.js"></script>
	
						<script type="text/javascript">
							function customersTypeChanged(el)
							{
								if($(el).val() == -1)
									$("#select-customers").hide();
								else
									$("#select-customers").show();
							}

							$(function()
							{
								customersTypeChanged($("[name=customers_type]"));

								<?php
									if($customers && $customers != -1)
										foreach($customers as $c)
											echo "addCustomer(".$c['customer_id'].",'".$c['customer_name']."');";
								?>
							
						      var searchUrl="{customers_search_url}";

					      	$('#customer').autocomplete({
						         source: function(request, response)
						         {
						            var term=request["term"];
						            $.get(searchUrl+"/"+encodeURIComponent(term),
						              function(res)
						              {
						                var rets=[];
						                for(var i=0;i<res.length;i++)
						                  rets[rets.length]=
						                    {
						                      label:res[i].name
						                      ,name:res[i].name
						                      ,id:res[i].id						                      
						                      ,value:term
						                    };

						                response(rets); 

						                return;       
						              },"json"
						            ); 
						          },
						          delay:700,
						          minLength:1,
						          select: function(event,ui)
						          {
						            var item=ui.item;
						            var id=item.id;
						            var name=item.name;

						            if(!$("div[data-id="+id+"]",$("#customers")).length)
						            	addCustomer(id, name);
						            
						            $("#customer").val("");
						            return false;
						          }
						      });

						   });

						   function addCustomer(id, name)
						   {
						      var html=
						      	"<div class='four columns' data-id='"+id+"'>"
						      		+name
						      		+"<span class='anti-float' onclick='$(this).parent().remove();'></span>"
						      		+"<input type='hidden' name='customer_ids[]' value='"+id+"'/>"
						      	+"</div>"
						      	;
						      $("#customers").append($(html));
						   }

						</script>


					</div>

					<div class="tab" id="orders">
						<?php foreach($orders as $o){ ?>
							<div class='row even-odd-bg'>
								<div class='three columns'>
									<label>{customer_text}</label>
									<span>
										<a href="<?php echo get_admin_customer_details_link($o['customer_id']);?>">
											<?php echo $o['customer_name'];?>
										</a>
									</span>
								</div>
								<div class='three columns'>
									<label>{order_text}</label>
									<span>
										<a href="<?php echo get_admin_order_details_link($o['cp_order_id']);?>">
											<?php echo $o['cp_order_id'];?>
										</a>
									</span>
								</div>
								<div class='three columns'>
									<label>{payment_id_text}</label>
									<span><?php echo $o['cp_payment_id'];?></span>
								</div>
								<div class='three columns'>
									<label>{value_text}</label>
									<span><?php echo price_separator($o['cp_value'])." ".$currency_text;?></span>
								</div>
							</div>
						<?php } ?>
					</div>
				</div>

				<div style="display:none">
					<?php echo form_open("",array("id"=>"delete")); ?>
						<input type="hidden" name="post_type" value="delete_coupon"/>
						<input type="hidden" name="post_id" value="{order_id}"/>
					</form>

					<script type="text/javascript">
						
	              	function deleteCoupon()
						{
							if(!confirm("{are_you_sure_to_delete_this_coupon_text}"))
								return;

							$("form#delete").submit();
						}

					</script>
				</div>
			</div>
		<?php 
			}
		?>
	</div>
</div>
