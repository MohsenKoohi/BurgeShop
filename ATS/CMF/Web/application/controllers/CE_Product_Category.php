<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Product_Category extends Burge_CMF_Controller {
	protected $hit_level=2;

	function __construct()
	{
		parent::__construct();
		$this->load->model("product_category_manager_model");
	}

	public function index($category_id,$category_hash,$category_name="",$page=1)
	{	
		$category_info=$this->product_category_manager_model->get_info((int)$category_id,$this->selected_lang);
		if(!$category_info || ($category_info['pc_hash']!== $category_hash))
			redirect(get_link("home_url"));

		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$category_link=get_customer_product_category_details_link($category_id,$category_info['pc_hash'],$category_info['pcd_url'],$page);
		if($category_info['pcd_url'])
			if(get_customer_product_category_details_link($category_id,$category_hash,urldecode($category_name),$page) !== $category_link)
				redirect($category_link,"location",301);

		//$this->lang->load('ce_category',$this->selected_lang);	

		$this->data['category_info']=$category_info;
		if($category_info['pcd_image'])
			$this->data['page_main_image']=$category_info['pcd_image'];

		$this->data['message']=get_message();

		$this->load->model("product_manager_model");

		$per_page=20;
		$filter=array(
			"lang"=>$this->selected_lang
			,"category_id"=>$category_id
			,"product_date_le"=>get_current_time()
			,"active"=>1
		);
		$total_products=$this->product_manager_model->get_total($filter);
		$total_pages=ceil($total_products/$per_page);
		$this->data['total_pages']=$total_pages;

		if($total_pages>0 && $page>$total_pages)
			redirect(get_customer_product_category_details_link($category_id,$category_hash,$category_info['pcd_url'],$total_pages));
		if($page<1)
			redirect(get_customer_product_category_details_link($category_id,$category_hash,$category_info['pcd_url']));

		$this->data['current_page']=$page;
		$base_url=get_customer_product_category_details_link($category_id,$category_hash,$category_info['pcd_url'],"page_number");
		
		$pagination_settings=array(
			"current_page"		=> $page
			,"total_pages"		=> $total_pages
			,"base_url"			=> $base_url
			,"page_text"		=> $this->lang->line("page")
		);
		//$this->data['pagination']=get_select_pagination($pagination_settings);

		$pagination_settings['base_url']=get_customer_product_category_details_link($category_id,$category_hash,$category_info['pcd_url'],"");
		$this->data['pagination']=get_link_pagination($pagination_settings);

		$filter['start']=$per_page*($page-1);
		$filter['count']=$per_page;
		$filter['order_by']="product_date DESC";

		$this->data['products']=$this->product_manager_model->get_products($filter);
		foreach($this->data['products'] as &$product_info)
		{
			if(!$product_info['pc_image'])
				if($product_info['pc_gallery'])
				{
					foreach($product_info['pc_gallery']['images'] as $img)
						break;
					$product_info['pc_image']=get_link("product_gallery_url").'/'.$img['image'];
				}
		}

		if($page>1)
			$this->data['header_prev_url']=get_customer_product_category_details_link($category_id,$category_hash,$category_info['pcd_url'],$page-1);
		if($page<$total_pages)
			$this->data['header_next_url']=get_customer_product_category_details_link($category_id,$category_hash,$category_info['pcd_url'],$page+1);

		$this->data['lang_pages']=get_lang_pages(get_customer_product_category_details_link($category_id,$category_hash,$category_info['pcd_url'],$page,TRUE));
		
		$this->data['header_title']=$category_info['pcd_name'].$this->lang->line("header_separator").$this->data['header_title'];
		$this->data['header_meta_description']=$category_info['pcd_meta_description'];
		$this->data['header_meta_keywords'].=",".$category_info['pcd_meta_keywords'];

		$this->data['header_canonical_url']=$category_link;

		$this->send_customer_output("product_category");

		return;
	}
}