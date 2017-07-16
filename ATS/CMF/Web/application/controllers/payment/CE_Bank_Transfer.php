<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class CE_Bank_Transfer extends Burge_CMF_Controller {
	protected $hit_level=1;

	function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			"customer_manager_model"
			,"order_manager_model"
			,"payment_manager_model"
		));
	}

	public function index($order_id)
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
		if($this->input->post("post_type") == 'submit_payment')
			return $this->submit_payment($order_id);
		
		$total=$order['order_total'];
		$payment_id=$this->payment_manager_model->add_payment($order_id, $total, "bank_transfer");
		$this->session->set_userdata("payment_bank_transfer_payment_id",$payment_id);		

		$this->data['order_id']=$order_id;
		$this->data['message']=get_message();
		$this->data['order_total']=$total;

		$this->data['lang_pages']=get_lang_pages(get_customer_payment_order_link($order_id,TRUE));
		$this->data['header_title']=
			$this->lang->line("payment_method_bank_transfer").$this->lang->line("header_separator")
			.$this->data['header_title'];

		$this->send_customer_output("payment/bank_transfer");
		
		return;
	}

	private function submit_payment($order_id)
	{
		$payment_id=(int)$this->session->userdata("payment_bank_transfer_payment_id");				
		$this->session->unset_userdata("payment_bank_transfer_payment_id");		

		if(!$payment_id)
			return redirect(get_link("home_url"));

		$props=array(
			"name"					=> $this->input->post("name")
			,"date"					=> $this->input->post("date")
			,"bank"					=> $this->input->post("bank")
			,"reference_code"		=> $this->input->post("reference_code")
		);

		$comment=$props;

		$this->payment_manager_model->add_history($payment_id, 'end_payment', $comment, $props['reference_code']);
		
		$this->order_manager_model->add_history($order_id, 'payed');

		set_message($this->lang->line("your_payment_info_saved_successfully_and_will_be_verified_soon"));

		redirect(get_link("customer_order"));
		
		return;
	}
}