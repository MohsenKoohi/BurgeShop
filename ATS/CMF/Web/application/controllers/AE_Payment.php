<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class AE_Payment extends Burge_CMF_Controller {

	function __construct()
	{
		parent::__construct();

		$this->load->model(array(
			"payment_manager_model"
		));

		$this->lang->load('ae_payment',$this->selected_lang);

		return;
	}

	public function index()
	{	
		$this->set_search_results();

		$this->data['message']=get_message();

		$this->data['payment_methods']=$this->payment_manager_model->get_payment_methods();
		$this->data['raw_page_url']=get_link("admin_payment");
		$this->data['lang_pages']=get_lang_pages(get_link("admin_payment",TRUE));
		$this->data['header_title']=$this->lang->line("payments");
		
		$this->send_admin_output("payment");

		return;	 
	}

	private function set_search_results()
	{
		$filter=$this->get_search_filters();
		
		$this->data['filter']=$filter;
		
		$items_per_page=20;
		$page=1;
		if($this->input->get("page"))
			$page=(int)$this->input->get("page");

		$total=$this->payment_manager_model->get_total_payments($filter);
		$this->data['total']=$total;
		$this->data['total_pages']=ceil($total/$items_per_page);
		if($total)
		{
			if($page > $this->data['total_pages'])
				$page=$this->data['total_pages'];
			if($page<1)
				$page=1;
			$this->data['current_page']=$page;
			
			$start=($page-1)*$items_per_page;
			$filter['start']=$start;
			$filter['length']=$items_per_page;

			$end=$start+$items_per_page-1;
			if($end>($total-1))
				$end=$total-1;
			$this->data['start']=$start+1;
			$this->data['end']=$end+1;		
	
			$filter['order_by']="payment_id DESC";

			$this->data['payments_info']=$this->payment_manager_model->get_payments($filter);

			unset($filter['start'],$filter['length'],$filter['order_by']);
		}
		else
		{
			$this->data['start']=0;
			$this->data['end']=0;
			$this->data['orders_info']=array();
		}

		return;
	}

	private function get_search_filters()
	{
		$filter=array();

		$pfnames=array("payment_id","order_id", "method","start_date","end_date","name","description");
		foreach($pfnames as $pfname)
		{
			if($this->input->get($pfname))
				$filter[$pfname]=$this->input->get($pfname);	

			if(("start_date" === $pfname) || ("end_date"===$pfname))
				if(!validate_persian_date($filter[$pfname]))
					unset($filter[$pfname]);
		}

		return $filter;
	}

}