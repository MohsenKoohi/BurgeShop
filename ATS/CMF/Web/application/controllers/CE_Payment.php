<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Payment extends Burge_CMF_Controller {
	protected $hit_level=2;

	function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			"customer_manager_model"
			,"order_manager_model"
			,"payment_manager_model"
		));
	}

	public function pay($order_id)
	{	
		if(!$this->customer_manager_model->has_customer_logged_in())
		{
			redirect(get_link("home_url"));
			return;
		}

		$order_id=(int)$order_id;
		$orders=$this->order_manager_model->get_orders(array(
			"order_id" 			=> $order_id
			,"customer_id"		=> $this->customer_manager_model->get_logged_customer_id()
			,"status"			=> 'submitted'
		));

		if(!$orders)
			return redirect(get_link("home_url"));
		
		$order=$orders[0];
		
		bprint_r($order);
		exit();
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

		$order_id=$this->order_manager_model->submit_order();

		set_message($this->lang->line("your_order_submitted_successfully"));

		redirect(get_customer_payment_order_link($order_id));
		
		return;
	}

	public function orders()
	{
		
	
	}
}