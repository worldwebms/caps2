<?php

abstract class CapsKayakoApi {
	
	const GET = 'GET';
	const POST = 'POST';
	
	private static function _send($request, $params, $method = self::GET) {
		
		$api_key = 'c2bca210-5368-0394-3dbf-9606317ccf01';
		$api_secret = 'NzVhZWVkZDUtMTQzMS02ZTI0LWVkYmMtMDZhNzZlM2NjMjFjMzk4Njg0ZTUtMzc3NC04ZmE0LTA5YmYtMzg0ODU1ZjIwZDc3';
		$salt = uniqid(uniqid());
		
		$request = array(
			'e' => $request
		);
		foreach ($params as $k => $v)
			$request[$k] = $v;
		$request['apikey'] = $api_key;
		$request['salt'] = $salt;
		$request['signature'] = base64_encode(hash_hmac('sha256', $salt, $api_secret));
		
		// Generate the parameters
		$query = '';
		if ($method == self::GET) {
			foreach ($request as $k => $v)
				$query .= ($query == '' ? '' : '&') . $k . '=' . rawurlencode($v);
		}
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://support.worldwebms.com/api/index.php?' . $query);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		
		if ($method == self::POST) {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($request));
		}
		
		$response = curl_exec($ch);
		
		debug($response);
		
	}
	
	public static function get_ticket_url($reference) {
		return 'https://support.worldwebms.com/staff/';
		if ($reference) {
			$response = self::_send('/Tickets/TicketSearch', array('query' => $reference, 'ticketid' => 1), self::POST);
		}
		return '';
	}
	
}