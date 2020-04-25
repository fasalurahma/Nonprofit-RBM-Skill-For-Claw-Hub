<?php

class ControllerHyperlocalShippinglist extends Controller {

	private $error = array();
	private $data = array();

	public function index() {

		$this->data = array_merge($this->data,$this->language->load('hyperlocal/shippinglist'));

		$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('hyperlocal/hyperlocal');

		$filter_array = array(
			'filter_name',
			'filter_range_to',
			'filter_range_from',
			'filter_price',
			'filter_weight_to',
			'filter_weight_from',
			'page',
			'sort',
			'order',
			'start',
			'limit',
		);


		$url = '';

		foreach ($filter_array as $unsetkey => $key) {

			if (isset($this->request->get[$key])) {
				$filter_array[$key] = $this->request->get[$key];
			} else {
				if ($key=='page')
					$filter_array[$key] = 1;
				elseif($key=='sort')
					$filter_array[$key] = 'cs.id';
				elseif($key=='order')
					$filter_array[$key] = 'ASC';
				elseif($key=='start')
					$filter_array[$key] = ($filter_array['page'] - 1) * $this->config->get('config_limit_admin');
				elseif($key=='limit')
					$filter_array[$key] = $this->config->get('config_limit_admin');
				else
					$filter_array[$key] = null;
			}

			unset($filter_array[$unsetkey]);

			if(isset($this->request->get[$key])) {
				if ($key=='filter_name' )
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				else
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}

		$this->language->load('hyperlocal/shippinglist');

  		$this->data['breadcrumbs'] = array();

   		$this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('text_home'),
      'href'      => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
      'separator' => false
   		);

   		$this->data['breadcrumbs'][] = array(
      'text'      => $this->language->get('heading_title'),
      'href'      => $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'),
      'separator' => ' :: '
   		);

		$this->data['delete'] = $this->url->link('hyperlocal/shippinglist/delete', 'user_token=' . $this->session->data['user_token'] , 'SSL');
    	$this->data['addshipping'] = $this->url->link('hyperlocal/hyperlocal', 'user_token=' . $this->session->data['user_token'] , 'SSL');

    	$results = $this->model_hyperlocal_hyperlocal->viewtotal($filter_array);

		$product_total = $this->model_hyperlocal_hyperlocal->viewtotalentry($filter_array);

		$this->data['result_shipping']=array();

	    foreach ($results as $result) {

	      	$this->data['result_shipping'][] = array(
				     'selected'    => False,
				     'id'          => $result['id'],
				     'name'        => $result['name'] ? $result['name'] : 'Admin',
				     'price'       => $result['price'],
				     'range_from'  => $result['range_from'],
				     'range_to'    => $result['range_to'],
				     'weight_from' => $result['weight_from'],
				     'weight_to'   => $result['weight_to'],
			     );

		}

 		$this->data['user_token'] = $this->session->data['user_token'];

 		if (isset($this->error['warning'])) {
			$this->data['error_warning'] = $this->error['warning'];
		} else {
			$this->data['error_warning'] = '';
		}

		if (isset($this->session->data['success'])) {
			$this->data['success'] = $this->session->data['success'];
			unset($this->session->data['success']);
		} else {
			$this->data['success'] = '';
		}

		$url = '';

		foreach ($filter_array as $key => $value) {
			if(isset($this->request->get[$key])) {
				if(!isset($this->request->get['order']) AND isset($this->request->get['sort']))
					$url .= '&order=DESC';
				if ($key=='filter_name')
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif($key=='order')
					$url .= $value=='ASC' ? '&order=DESC' : '&order=ASC';
				elseif($key!='start' AND $key!='limit' AND $key!='sort')
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}

		$this->data['sort_name'] = $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . '&sort=name' . $url, 'SSL');
		$this->data['sort_price'] = $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . '&sort=cs.price' . $url, 'SSL');
		$this->data['sort_range_to'] = $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . '&sort=cs.range_to' . $url, 'SSL');
		$this->data['sort_range_from'] = $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . '&sort=cs.range_from' . $url, 'SSL');
		$this->data['sort_weight_to'] = $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . '&sort=cs.weight_to' . $url, 'SSL');
		$this->data['sort_weight_from'] = $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . '&sort=cs.weight_from' . $url, 'SSL');

		$url = '';

		foreach ($filter_array as $key => $value) {
			if(isset($this->request->get[$key])) {
				if(!isset($this->request->get['order']) AND isset($this->request->get['sort']))
					$url .= '&order=DESC';
				if ($key=='filter_name')
					$url .= '&' . $key . '=' . urlencode(html_entity_decode($filter_array[$key], ENT_QUOTES, 'UTF-8'));
				elseif($key!='page')
					$url .= '&' . $key . '=' . $filter_array[$key];
			}
		}

		$pagination = new Pagination();
		$pagination->total = $product_total;
		$pagination->page = $filter_array['page'];
		$pagination->limit = $this->config->get('config_limit_admin');
		$pagination->url = $this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . $url . '&page={page}', 'SSL');

		$this->data['pagination'] = $pagination->render();

		$this->data['results'] = sprintf($this->language->get('text_pagination'), ($product_total) ? (($filter_array['page'] - 1) * $this->config->get('config_limit_admin')) + 1 : 0, ((($filter_array['page'] - 1) * $this->config->get('config_limit_admin')) > ($product_total - $this->config->get('config_limit_admin'))) ? $product_total : ((($filter_array['page'] - 1) * $this->config->get('config_limit_admin')) + $this->config->get('config_limit_admin')), $product_total, ceil($product_total / $this->config->get('config_limit_admin')));

		foreach ($filter_array as $key => $value) {
			if($key!='start' AND $key!='end')
				$this->data[$key] = $value;
		}

		$this->data['header'] = $this->load->controller('common/header');
		$this->data['footer'] = $this->load->controller('common/footer');
		$this->data['column_left'] = $this->load->controller('common/column_left');
		$this->response->setOutput($this->load->view('hyperlocal/shippinglist',$this->data));

  	}

  	public function delete() {
    	$this->language->load('hyperlocal/shippinglist');

    	$this->document->setTitle($this->language->get('heading_title'));

		$this->load->model('hyperlocal/hyperlocal');

		if (isset($this->request->post['selected']) && $this->validateDelete()) {
			foreach ($this->request->post['selected'] as $id) {
				$this->model_hyperlocal_hyperlocal->deleteentry($id);
	  		}

			$this->session->data['success'] = $this->language->get('text_success');

			$url='';

			if (isset($this->request->get['page'])) {
				$url .= '&page=' . $this->request->get['page'];
			}
		}
		$this->response->redirect($this->url->link('hyperlocal/shippinglist', 'user_token=' . $this->session->data['user_token'] . $url, 'SSL'));
  	}

	protected function validateDelete() {
    	if (!$this->user->hasPermission('modify', 'hyperlocal/shippinglist')) {
      		$this->error['warning'] = $this->language->get('error_permission');
    	}

		if (!$this->error) {
	  		return true;
		} else {
	  		return false;
		}
  	}

}
?>
