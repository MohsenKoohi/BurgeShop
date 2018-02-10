<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Product extends Burge_CMF_Controller {
	protected $hit_level=2;

	function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			"product_manager_model"
			,"product_category_manager_model"
		));
	}

	public function index($product_id,$product_name="")
	{	
		$product_info_array=$this->product_manager_model->get_product((int)$product_id,array(
			"lang"=> $this->selected_lang
			,"product_date_le"=>get_current_time()
			,"active"=>1
			));
		$product_info=$product_info_array[0];
		if(!$product_info)
			redirect(get_link("home_url"));

		if($product_info['product_allow_comment'])
			if($this->input->post("post_type") == 'add_comment')
				return $this->add_comment($product_id, $product_info);

		$this->data['product_gallery']=$product_info['pc_gallery']['images'];

		$cat_ids=explode(',',$product_info['categories']);
		$this->data['pcategories']=$this->product_category_manager_model->get_categories_short_desc($cat_ids,$this->selected_lang);

		$product_link=get_customer_product_details_link($product_id,$product_info['pc_title']);		
		if($product_info['pc_title'] && $product_name)
			if(get_customer_product_details_link($product_id,urldecode($product_name)) !== $product_link)
				redirect($product_link,"location",301);

		$this->data['product_info']=$product_info;
		$this->data['product_id']=$product_id;
		$this->data['page_link']=$product_link;

		if($this->input->post("post_type")==="add_to_cart")
			return $this->add_to_cart();

		if($product_info['pc_image'])
			$this->data['page_main_image']=$product_info['pc_image'];
		else
			if($this->data['product_gallery'])
			{
				foreach($this->data['product_gallery'] as $img)
					break;
				$this->data['page_main_image']=get_link("product_gallery_url").'/'.$img['image'];
				$this->data['product_info']['pc_image']=$this->data['page_main_image'];
			}

		if($product_info['product_allow_comment'])
		{
			$comments=$this->product_manager_model->get_comments(array("comment_product"=>$product_id));
			if($this->product_manager_model->show_product_comment_after_verification())
			{
				foreach($comments as $index => $comment)
					if($comment['pcom_status'] != 'verified')
						unset($comments[$index]);
			}
			else
				foreach($comments as $index => $comment)
					if($comment['pcom_status'] == 'not_verified')
						unset($comments[$index]);

			$this->data['comments']=$comments;
		}
			
		$this->data['message']=get_message();

		$this->data['lang_pages']=get_lang_pages(get_customer_product_details_link($product_id,"",TRUE));
		
		$this->data['header_title']=$product_info['pc_title'].$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$product_info['pc_description'];
		$this->data['header_meta_keywords'].=",".$product_info['pc_keywords'];

		$this->data['header_canonical_url']=$product_link;

		$this->send_customer_output("product");

		return;
	}

	private function add_to_cart()
	{
		$product_info=$this->data['product_info'];
		$product_id=$this->data['product_id'];
		$options=$this->input->post("options");
		$quantity=$this->input->post("quantity");

		$price=$this->product_manager_model->get_product_price($product_id, $options);

		if($price<0)
		{
			set_message($this->lang->line("options_has_not_been_selected_properly"));
			return redirect($this->data['page_link']);
		}
			
		$this->load->model("cart_manager_model");
		$this->cart_manager_model->add_item($product_id,$options,$quantity,$price);

		set_message("<a href='".get_link("customer_cart")."'>".$this->lang->line("product_added_successfully_to_your_cart")."</a>");
		return redirect($this->data['page_link']);
	}

	private function add_comment($product_id, $product_info)
	{
		$page_link=get_customer_product_details_link($product_id,$product_info['pc_title']);

		$text=trim(strip_tags($this->input->post("text")));
		$name=trim(strip_tags($this->input->post("name")));
		if(!$text || !$name)
		{
			set_message($this->lang->line("please_fill_all_fields"));
			return redirect($page_link);
		}

		$ip=$this->input->ip_address();

		$this->product_manager_model->add_comment($product_id, array(
			"name"		=> $name
			,"text"		=> $text
			,"ip"			=> $ip
		));

		set_message($this->lang->line("your_comment_submitted_successfully"));
		
		return redirect($page_link);
	}
}