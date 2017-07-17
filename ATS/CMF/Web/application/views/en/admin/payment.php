<div class="main">
	<div class="container">
		<h1>{payments_text}</h1>
		<div class="container separated">
			<div class="row filter">
				<link type="text/css" rel="stylesheet" href="{styles_url}/persian-datepicker-default.css" />
				<script type="text/javascript" src="{scripts_url}/persian-datepicker.js"></script>

				<div class="three columns ">
					<label>{payment_id_text}</label>
					<input type="text" name="payment_id" class="full-width ltr" />
				</div>

				<div class="three columns half-col-margin">
					<label>{order_number_text}</label>
					<input type="text" name="order_id" class="full-width ltr" />
				</div>

				<div class="three columns half-col-margin">
					<label>{payment_method_text}</label>
					<select type="text" name="method" class="full-width">
						<option value=''>&nbsp;</option>
						<?php 
							foreach($payment_methods as $p)
							{
								$name=${'payment_method_'.$p.'_text'};
								echo "<option value='$p'>$name</option>";
							}
						?>
					</select>
				</div>

				<div class="three columns">
					<label>{start_date_text}</label>
					<input type="text" name="start_date" class="date full-width ltr" />
				</div>

				<div class="three columns half-col-margin">
					<label>{end_date_text}</label>
					<input type="text" name="end_date" class="date full-width ltr" />
				</div>

				<div class="three columns half-col-margin ">
					<label>{customer_name_text}</label>
					<input type="text" name="name" class="full-width" />
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
		<?php 
			if($total)
				foreach($payments_info as $p)
				{
		?>
			<div class="row even-odd-bg" >
				<div class="three columns">
					<label>{payment_id_text}</label>
					<span class=''><?php echo $p['payment_id'];?></span>
				</div>
				<div class="three columns">
					<label>{order_id_text}</label>
					<span class=""><?php echo $p['payment_order_id'];?></span>
				</div>
				<div class="two columns">
					<label>{date_text}</label>
					<span class='date'><?php echo $p['payment_date'];?></span>
				</div>
				<div class="two columns">
					<label>{method_text}</label>
					<span class='date'><?php echo ${'payment_method_'.$p['payment_method'].'_text'};?></span>
				</div>
				<div class="two columns">
					<label>{customer_name_text}</label>
					<span >
						<a href="<?php echo get_admin_customer_details_link($p['customer_id']);?>" target="_blank">
							<?php 
								echo $p['customer_name']."<br>";
								if($p['customer_name'] !== $p['customer_email']) 
									echo $p['customer_email'];
							?>
						</a>
					</span>
				</div>				
				<div class="three columns">
					<label>{total_text}</label>
					<span><?php echo price_separator($p['payment_total'])." ".$currency_text;?></span>
				</div>
				<div class='three columns'>
					<label>{status_text}</label>
					<span>
						<?php echo ${'payment_status_'.$p['payment_status'].'_text'};?>
					</span>
				</div>
				<div class='three columns'>
					<label>{reference_code_text}</label>
					<span>
						<?php echo $p['payment_reference'];?>
					</span>
				</div>
				<div class="three columns align-center">
					<span>
						<label>&nbsp;</label>
						<a target="_blank" 
							href="<?php echo get_admin_order_details_link($p['payment_order_id']).'#payment'; ?>"
							class="button button-primary sub-primary full-width"
						>
							{view_text}
						</a>
					</span>
				</div>
			</div>
		<?php
				 } 
		?>
	</div>
</div>