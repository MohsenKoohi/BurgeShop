<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Product extends Burge_CMF_Controller {
	protected $hit_level=2;

	function __construct()
	{
		parent::__construct();
		$this->load->model(array(
			"product_manager_model"
			,"category_manager_model"
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

		$this->data['product_gallery']=$product_info['pc_gallery']['images'];

		$cat_ids=explode(',',$product_info['categories']);
		$this->data['product_categories']=$this->category_manager_model->get_categories_short_desc($cat_ids,$this->selected_lang);

		$product_link=get_customer_product_details_link($product_id,$product_info['pc_title'],$product_info['product_date']);
		if($product_info['pc_title'] && $product_name)
			if(get_customer_product_details_link($product_id,urldecode($product_name),$product_info['product_date']) !== $product_link)
				redirect($product_link,"location",301);

		$this->data['product_info']=$product_info;
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
			
		$this->data['message']=get_message();

		$this->data['lang_pages']=get_lang_pages(get_customer_product_details_link($product_id,"",$product_info['product_date'],TRUE));
		
		$this->data['header_title']=$product_info['pc_title'].$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$product_info['pc_description'];
		$this->data['header_meta_keywords'].=",".$product_info['pc_keywords'];

		$this->data['header_canonical_url']=$product_link;

		$this->send_customer_output("product");

		return;
	}
}