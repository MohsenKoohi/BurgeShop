<div class="main">
	<div class="container">
		<h1>{orders_text}</h1>
		<div class="container separated">
			<div class="row filter">
				<link type="text/css" rel="stylesheet" href="{styles_url}/persian-datepicker-default.css" />
				<script type="text/javascript" src="{scripts_url}/persian-datepicker.js"></script>

				<div class="three columns">
					<label>{order_number_text}</label>
					<input type="text" name="order_id" class="full-width ltr" />
				</div>
				<div class="three columns half-col-margin">
					<label>{start_date_text}</label>
					<input type="text" name="start_date" class="date full-width ltr" />
				</div>
				<div class="three columns half-col-margin">
					<label>{end_date_text}</label>
					<input type="text" name="end_date" class="date full-width ltr" />
				</div>
				<div class="three columns ">
					<label>{name_text}</label>
					<input type="text" name="name" class="full-width" />
				</div>
				<div class="three columns half-col-margin">
					<label>{email_text}</label>
					<input type="text" name="email" class="full-width" />
				</div>				
			</div>
			<div clas="row">
				<div class="two columns results-search-again">
					<input type="button" onclick="searchAgain()" value="{search_again_text}" class="full-width button-primary" />
				</div>
			</div>
			
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

				$(function()
				{
					$(".filter input.date").persianDatepicker();
				});
					
				var initialFilters=[];
				<?php
					foreach($filter as $key => $val)
						echo 'initialFilters["'.$key.'"]="'.$val.'";';
				?>
				var rawPageUrl="{raw_page_url}";

				$(function()
				{
					$(".filter input, .filter select").keypress(function(ev)
					{
						if(13 != ev.keyCode)
							return;

						searchAgain();
					});

					for(i in initialFilters)
						$(".filter [name='"+i+"']").val(initialFilters[i]);
				
				});

				function searchAgain()
				{
					document.location=getSearchUrl(getSearchConditions());
				}

				function getSearchConditions()
				{
					var conds=[];

					$(".filter input, .filter select").each(
						function(index,el)
						{
							var el=$(el);
							if(el.val())
								conds[el.prop("name")]=el.val();

						}
					);
					
					return conds;
				}

				function getSearchUrl(filters)
				{
					var ret=rawPageUrl+"?";
					for(i in filters)
						if (filters.hasOwnProperty(i))
							ret+="&"+i+"="+encodeURIComponent(filters[i].trim().replace(/\s+/g," "));
					return ret;
				}

				function pageChanged(pageNumber)
				{
					document.location=getSearchUrl(initialFilters)+"&page="+pageNumber;
				}
			</script>
		</div>		
		<br><br>
		<div class="row even-odd-bg title-row" >
				<div class="one columns align-center">
					<label>{order_number_text}</label>
				</div>
				<div class="three columns align-center">
					<label>{customer_name_text}</label>
				</div>
				<div class="two columns align-center">
					<label>{date_text}</label>
				</div>
				<div class="two columns align-center">
					<label>{total_text}</label>
				</div>
				<div class="two columns align-center">
					<label>{status_text}</label>
				</div>
				<div class="two columns align-center">
					<label>{visit_text}</label>					
				</div>
			</div>
			
			<?php $i=$start; foreach($orders_info as $order) {?>
				<div class="row even-odd-bg" >
					<div class="one columns align-center">
						<span class="counter"><?php echo $order['order_id'];?></span>
					</div>
					<div class="three columns">
						<span >
							<a href="<?php echo get_admin_customer_details_link($order['order_customer_id']);?>" target="_blank">
								<?php 
									echo $order['customer_name']."<br>";
									if($order['customer_name'] !== $order['customer_email']) 
										echo $order['customer_email'];
								?>
							</a>
						</span>
					</div>
					<div class="two columns align-center">
						<span class='date'><?php echo $order['order_date'];?></span>
					</div>
					<div class="two columns align-left">
						<span><?php echo price_separator($order['order_total'])." ".$currency_text;?></span>
					</div>
					<div class='two columns align-center'>
						<span><?php echo ${'order_status_'.$order['order_status'].'_text'};?></span>
					</div>
					<div class="two columns align-center">
						<span>
							<a target="_blank" 
								href="<?php echo get_admin_order_details_link($order['order_id']); ?>"
								class="button button-primary sub-primary full-width"
							>
								{view_text}
							</a>
							<?php if(0){?>
								<a class="one column">&nbsp;</a>
								<a class="button button-delete sub-primary five columns" 
									onclick="deleteOrder(<?php echo $order['order_id'].','.$order['order_customer_id'];?>);"
								>
									{delete_text}
								</a>
							<?php } ?>
						</span>
					</div>
				</div>
			<?php } ?>
			<div style="display:none">
				<?php echo form_open(get_link("admin_order"),array("class"=>"delete")); ?>
					<input type="hidden" name="post_type" value="delete_order"/>
					<input type="text" name="order_id"/>
					<input type="text" name="customer_id"/>
					<input type="submit" />
				</form>
			</div>
			<script type="text/javascript">
				function deleteOrder(orderId,customerId)
				{
					if(!confirm("{are_you_sure_to_delete_this_order_text}"))
						return;

					$("form.delete input[name='order_id']").val(orderId);	
					$("form.delete input[name='customer_id']").val(customerId);	
					$("form.delete").submit();
				}
			</script>
	</div>
</div>