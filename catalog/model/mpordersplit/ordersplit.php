<?php
class ModelMpordersplitOrdersplit extends Model {

  public function splitOrder($order_id , $order_status_id) {

    $products = array();
    $order_data = array();
    // $this->session->data['total_no_total'] = 2;
    $this->load->model('checkout/order');
    $order_data = $this->model_checkout_order->getOrder($order_id);
    $order_product = $this->model_checkout_order->getOrderProducts($order_id);
    $order_total = $this->model_checkout_order->getOrderTotals($order_id);

    if($order_data) {
      foreach($order_product as $op) {
        $seller_id = $this->getCustomerId($op['product_id']);
        if(isset($products[$seller_id])) {
          $order_options = $this->model_checkout_order->getOrderOptions($order_id, $op['order_product_id']);
          $op['option'] = $order_options;
          $products[$seller_id][] = $op;

        } else {
          $order_options = $this->model_checkout_order->getOrderOptions($order_id, $op['order_product_id']);
          $op['option'] = $order_options;
          $products[$seller_id][] = $op;
        }
      }

      foreach($products as $seller_id => $value) {
        $gdtotal = 0;
        $check = 1;
        foreach($value as $value2) {
          $gdtotal = $gdtotal + $value2['total'];
        }
        $quantity = 0;
        foreach($value as $key => $product) {
          $quantity += $product['quantity'];
        }
        $order_data['order_status_id'] = $order_status_id;
        $order_data['customer_group_id'] = '1';
        $order_data['marketing_id'] = '1';
        $order_data['tracking'] = '1';
        unset($value['order_product_id']);
        unset($value['order_id']);
        $order_data['products'] = $value;
        
        $this->load->model('catalog/product');
        $tax = 0;
        $check = 1;
        $price = 1;
        $sptotal = 0;
        foreach($order_total as $key1 => $total) {
         
          if($total['code'] == 'sub_total') {
            $order_total[$key1]['value'] = $gdtotal;
          }

          if($total['code'] == 'shipping') {

            if(isset($this->session->data['ordersplit_shippingmethod']) && isset($this->session->data['shipping_method']) && isset($this->session->data['shipping_method']['code'])) {

              if(isset($this->session->data['ordersplit_shippingmethod'][$this->session->data['shipping_method']['code']][$seller_id])) {

                $seller_shipping_cost = $this->session->data['ordersplit_shippingmethod'][$this->session->data['shipping_method']['code']][$seller_id];

                $order_total[$key1]['value']   = $seller_shipping_cost['cost'];
                $sptotal = $gdtotal + $seller_shipping_cost['cost'];
              }              
            }

            
          }
          if($total['code'] == 'tax') {
            $tax = 0;
            foreach ($value as $key => $product) {
              $product_info = $this->model_catalog_product->getProduct($product['product_id']);
              $tax += $product['quantity'] * $this->tax->getTaxForRate($product['price'],$product_info['tax_class_id'],$total['title']);
              $tax_check = $product['quantity'] * $this->tax->getTaxForRate($product['price'],$product_info['tax_class_id'],$total['title']);
            }
           
            $order_total[$key1]['value'] = $tax;
            $sptotal = (float)$sptotal + (float)$tax;
            $tax = 0;
            $check++;
          }

          if($total['code'] == 'total') {
            $order_total[$key1]['value'] = $sptotal;
            $order_data['total'] =  $sptotal;
          }
        }
        $order_data['totals'] = $order_total;
        $order_total = $this->model_checkout_order->getOrderTotals($order_id);
        $neworderid = $this->model_checkout_order->addOrder($order_data);
        $this->model_checkout_order->addOrderHistory($neworderid, $order_status_id);
      }
      $this->db->query("UPDATE `" . DB_PREFIX . "order` SET order_status_id = '0', date_modified = NOW() WHERE order_id = '" . (int)$order_id . "'");
    }
  }

  public function getCustomerId($product_id) {
    $row = $this->db->query("SELECT customer_id FROM " . DB_PREFIX . "customerpartner_to_product WHERE product_id=" . (int)$product_id)->row;
    return isset($row['customer_id']) && $row['customer_id'] ? $row['customer_id'] : 'admin';
  }

}
