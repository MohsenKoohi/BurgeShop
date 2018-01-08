<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Coupon extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->lang->load('ae_coupon',$this->selected_lang);
		$this->load->model("coupon_manager_model");

	}

	public function index()
	{
		if($this->input->post("post_type") == 'add_new_coupon')
			return $this->add_new_coupon();

		$this->data['coupons']=$this->coupon_manager_model->get_all();
		
		$this->data['message']=get_message();
		$this->data['page_link']=get_link("admin_coupon");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_coupon",TRUE));
		$this->data['header_title']=$this->lang->line("coupons");

		$this->send_admin_output("coupon");

		return;	 
	}	

	private function add_new_coupon()
	{
		$coupon_id=$this->coupon_manager_model->add_new();
		
		set_message($this->lang->line("the_new_coupon_addedd_successfully"));

		return redirect(get_admin_coupon_details_link($coupon_id));
	}

	public function details($coupon_id)
	{
		$coupon_id = (int)$coupon_id;
		if($this->input->post("post_type")==="edit_coupon")
			return $this->edit_coupon($coupon_id);

		if($this->input->post("post_type")==="delete_coupon")
			return $this->delete_coupon($coupon_id);

		$this->data['coupon_id']=$coupon_id;
		
		list($coupon_info, $coupon_customers, $orders)=$this->coupon_manager_model->get_coupon($coupon_id);
		$this->data['info']=$coupon_info;
		$this->data['customers']=$coupon_customers;
		$this->data['orders']=$orders;

		$this->data['message']=get_message();
		$this->data['customers_search_url']=get_link("admin_customer_search");

		$this->data['lang_pages']=get_lang_pages(get_admin_coupon_details_link($coupon_id,TRUE));
		$this->data['header_title']=$this->lang->line("coupon")." ".$coupon_id.$this->lang->line("header_separator").$this->lang->line("coupons");

		$this->send_admin_output("coupon_details");

		return;
	}

	private function delete_coupon($coupon_id)
	{
		$res=$this->coupon_manager_model->delete($coupon_id);

		set_message($this->lang->line('coupon_deleted_successfully'));
		
		return redirect(get_link("admin_coupon"));
	}

	private function edit_coupon($coupon_id)
	{
		$props=array();
		$props_names=array("name","code","min_price","expiration_date","value","value_type","usage_number");
		foreach($props_names as $n)
			$props['coupon_'.$n]=$this->input->post($n);

		persian_normalize($props);
		$props['coupon_active']=(int)($this->input->post("active")=="on");

		$customers=-1;
		if($this->input->post("customers_type") != -1)
			$customers=implode(",", $this->input->post("customer_ids"));
		$props['coupon_customers']=$customers;

		$this->coupon_manager_model->set_props($coupon_id, $props);

		set_message($this->lang->line("coupon_changed_successfully"));

		return redirect(get_admin_coupon_details_link($coupon_id));
	}
}