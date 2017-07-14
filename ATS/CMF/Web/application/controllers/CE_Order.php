<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Order extends Burge_CMF_Controller {
	protected $hit_level=2;

	function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			"cart_manager_model"
			,"customer_manager_model"
			,"order_manager_model"
		));
	}

	public function submit()
	{	
		$cart=$this->cart_manager_model->get_cart($this->selected_lang);
		if(!$cart['products'])
			return redirect(get_link("home_url"));

		if(!$this->customer_manager_model->has_customer_logged_in())
		{
			$this->session->set_userdata("backurl",get_link("customer_order_submit"));
			set_message($this->lang->line("please_login_before_checkout"));
			redirect(get_link("customer_login"));
			return;
		}

		$this->order_manager_model->submit_order();

		exit();


		$cart=$this->cart_manager_model->get_cart($this->selected_lang);
		$this->data['products']=$cart['products'];
		$this->data['total_price']=$cart['total_price'];

		$this->data['lang_pages']=get_lang_pages(get_link("customer_cart",TRUE));
		
		$this->data['header_title']=$this->lang->line("cart").$this->lang->line("header_separator").$this->data['header_title'];
		
		$this->send_customer_output("cart");

		return;
	}

	private function remove_item()
	{
		$index=(int)$this->input->post("item_index");

		$this->cart_manager_model->remove_item($index);

		set_message($this->lang->line("item_removed_successfully"));

		return redirect(get_link('customer_cart'));
	}
}