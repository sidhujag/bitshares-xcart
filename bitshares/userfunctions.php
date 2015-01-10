<?php

require 'config.php';
function getOrderCartHelper($id)
{
	global $baseURL;
	global $apiKey;

	$url = $baseURL.'admin.php?target=RESTAPI';
  $path = 'order';
  if($id)
  {
    $path = $path.'/'.$id;
  }
	$datatopost = array (
    "_method" => 'get',
		"_key" =>	$apiKey,
		"_path" =>	$path
		);
  $url = $url.'&'.http_build_query($datatopost);
  
	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, '15');
	
	$response = curl_exec($ch);
	if ($response === false){
		debuglog('request to xcart failed');
		debuglog(curl_error($ch));
	}
			
	curl_close($ch);
	return json_decode($response, TRUE);
}
// response_code 1=approved, 4=complete, 5=cancel
function getOrderWithStatusFromCartHelper($id, $response_code)
{
	$response = getOrderCartHelper($id);
  $orders = array();
	if ($response !== false){
    if($id === NULL)
    {
      
      // loop over all orders
      foreach ($response as $responseOrder) {
        // get order total here and payment method, validate method with Bitshares
        $total = $responseOrder['total'];
        $order_id = $responseOrder['order_id'];
        $method = $responseOrder['payment_method_name'];
        if($method === 'Bitshares')
        {
          // get order info with id
          $orderInfo = getOrderCartHelper($order_id);
          // check payment status with response_code
          if($orderInfo !== false && $orderInfo['paymentStatus']['code'] === $response_code)
          {
            // save currency code
            $currency = $orderInfo['currency']['code'];
            $order = array (
              "order_id" => $order_id,
	            "currency" =>	$currency,
	            "order_total" =>	$total
	          );
            // add to open orders array for return
            array_push($orders, $order);
          }
        }
      }
      
    }
    else 
    {
      if($response['order_id'] == $id && $response['paymentStatus']['code'] == $response_code && $response['payment_method_name'] === 'Bitshares')
      {
        $ret = array (
          "order_id" => $id,
	        "currency" =>	$response['currency']['code'],
	        "order_total" =>	$response['total']
	      );
        array_push($orders, $ret);

      }
    }  
  }
	return $orders;
}

function sendToCart($id, $statusCode, $comment)
{
	global $baseURL;
	global $apiKey;
	$response_code = $statusCode; // Q=awaiting payment, C=cancelled, P=paid
	$response_reason = $comment; 
	$order_id = $id; 
	$url = $baseURL.'admin.php?target=RESTAPI';

	$datatopost = array (
    "_method" => 'put',
		"_key" =>	$apiKey,
		"_path" =>	'order/'.$order_id,
		"model[paymentStatus][code]" => $response_code,
		"model[adminNotes]" => $response_reason

		);
	$ch = curl_init($url);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
	curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($datatopost));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, '15');
	
	
	$response = curl_exec($ch);
	if ($response === false){
		debuglog('request to xcart failed');
		debuglog(curl_error($ch));
    $response = array();
    $response['error'] = curl_error($ch);    
	}
			
	curl_close($ch);
	return $response;
}
function getOpenOrdersUser()
{

	$openOrderList = array();
  // find open orders status id (not paid)
	$result = getOrderWithStatusFromCartHelper(NULL, 'Q');
  foreach ($result as $responseOrder) {
		$newOrder = array();
		$total = $responseOrder['order_total'];
		$total = number_format((float)$total,2);		
		$newOrder['total'] = $total;
		$newOrder['currency_code'] = $responseOrder['currency'];
		$newOrder['order_id'] = $responseOrder['order_id'];
		$newOrder['date_added'] = 0;
		array_push($openOrderList,$newOrder);    
	}
	return $openOrderList;
}
function isOrderCompleteUser($memo, $order_id)
{
	global $accountName;
	global $hashSalt;
  // find orders with id order_id and status id (completed)
	$result = getOrderWithStatusFromCartHelper($order_id, 'P');
	foreach ($result as $responseOrder) {
			$total = $responseOrder['order_total'];
			$total = number_format((float)$total,2);
			$asset = btsCurrencyToAsset($responseOrder['currency']);
			$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
			$memoSanity = btsCreateMemo($hash);		
			if($memoSanity === $memo)
			{	
				return TRUE;
			}
	}
	return FALSE;	
}
function doesOrderExistUser($memo, $order_id)
{
	global $accountName;
	global $hashSalt;
  // find orders with id order_id and status id (not paid)
	$result = getOrderWithStatusFromCartHelper($order_id, 'Q');
	foreach ($result as $responseOrder) {
			$total = $responseOrder['order_total'];
			$total = number_format((float)$total,2);
			$asset = btsCurrencyToAsset($responseOrder['currency']);
			$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
      $memoSanity = btsCreateMemo($hash);
			if($memoSanity === $memo)
			{	
				$order = array();
				$order['order_id'] = $order_id;
				$order['total'] = $total;
				$order['asset'] = $asset;
				$order['memo'] = $memo;	
				return $order;
			}
	}
	return FALSE;
}

function completeOrderUser($order)
{
	global $baseURL;
  $response = sendToCart($order['order_id'], 'P', 'Order paid for');  
  $response['url'] = $baseURL;
	return $response;
}
function cancelOrderUser($order)
{
	global $baseURL;
  $response = sendToCart($order['order_id'], 'C', 'Cancelled by user');   
  $response['url'] = $baseURL;
	return $response;
}
function cronJobUser()
{
	return 'Success!';
}
function createOrderUser()
{

	global $accountName;
	global $hashSalt;

	$order_id    = $_REQUEST['order_id'];
	$asset = btsCurrencyToAsset($_REQUEST['code']);
	$total = number_format((float)$_REQUEST['total'],2);
	$hash =  btsCreateEHASH($accountName,$order_id, $total, $asset, $hashSalt);
	$memo = btsCreateMemo($hash);
	$ret = array(
		'accountName'     => $accountName,
		'order_id'     => $order_id,
		'memo'     => $memo
	);
	
	return $ret;	
}

?>