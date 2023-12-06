<?php
/**
 * This file is adding admin menues for plugin
 *
 * @package Spring GDS
 */

?>
<?php

/**
 * Itembase
 */
class ItembasePostnl {


	/**
	 * Instance_id
	 *
	 * @var string
	 */
	public $instance_id = '0852d7d0-e363-4720-a13d-36e88f6f25f9';
	/**
	 * Base_url
	 *
	 * @var string
	 */
	public $base_url = 'https://api.itembase.com';

	/**
	 * Method write_log
	 *
	 * @param $content $content This is lo content.
	 * @param file    $file    This is a file path.
	 *
	 * @return void
	 */
	public function write_log( $content, $file = 'logs/api_response.log' ) {
		$write_to_log = SPRINGGDS_PLUGIN_DIR . $file;
		file_put_contents( $write_to_log, $content . PHP_EOL, FILE_APPEND | LOCK_EX );
	}

	/**
	 * Method api_log_count
	 *
	 * @param $file  $file This is file path.
	 * @param $count $count This is limit to store logs.
	 *
	 * @return void
	 */
	public function api_log_count( $file = 'logs/api_response.log', $count = 30 ) {
		if ( get_option( 'spring_gds_api_log_count' ) ) {
			$log_count = get_option( 'spring_gds_api_log_count' );
			if ( $log_count != $count ) {
				$log_count++;
				update_option( 'spring_gds_api_log_count', $log_count );
			} else {
				$write_to_log = SPRINGGDS_PLUGIN_DIR . $file;
				file_put_contents( $write_to_log, '' );
				update_option( 'spring_gds_api_log_count', 1 );
			}
		} else {
			add_option( 'spring_gds_api_log_count', 1 );
		}
	}

	/**
	 * Method generate_secret
	 *
	 * @return string
	 */
	public function generate_secret() {
		return sprintf(
			'%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0x0fff ) | 0x4000,
			mt_rand( 0, 0x3fff ) | 0x8000,
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff ),
			mt_rand( 0, 0xffff )
		);
	}

	/**
	 * Method remove_springgds_table
	 *
	 * @param $table_prefix $table_prefix This is table prefix.
	 *
	 * @return array
	 */
	public function remove_springgds_table( $table_prefix ) {
		$tables_sql = array(
			'DROP TABLE `' . $table_prefix . '`',
			'DROP TABLE `' . $table_prefix . '_orders`',
			'DROP TABLE `' . $table_prefix . '_locations`',
			'DROP TABLE `' . $table_prefix . '_packages`',
			'DROP TABLE `' . $table_prefix . '_labels`',
			'DROP TABLE `' . $table_prefix . '_delivery_names`',
			'DROP TABLE `' . $table_prefix . '_delivery_phones`',
			'DROP TABLE `' . $table_prefix . '_delivery_emails`',
			'DROP TABLE `' . $table_prefix . '_delivery_vat`',
			'DROP TABLE `' . $table_prefix . '_delivery_ioss`',
			'DROP TABLE `' . $table_prefix . '_delivery_eori`',
			'DROP TABLE `' . $table_prefix . '_default_settings`',
			'DROP TABLE `' . $table_prefix . '_taxes`',
			'DROP TABLE `' . $table_prefix . '_split_shipments`',
			'DROP TABLE `' . $table_prefix . '_documents`',
			'DROP TABLE `' . $table_prefix . '_service_rules`',
			'DROP TABLE `' . $table_prefix . '_printing`',
			'DROP TABLE `' . $table_prefix . '_order_filter`',
			'DROP TABLE `' . $table_prefix . '_package_weight_calculations`',
		);
		return $tables_sql;
	}

	/**
	 * Method create_new_connection
	 *
	 * @param $api_secrete $api_secrete This is API secrete.
	 * @param api_key     $api_key     This is API KEY.
	 * @param username    $username    This is username.
	 *
	 * @return string
	 */
	public function create_new_connection( $api_secrete, $api_key, $username ) {
		$this->api_log_count();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling Create New Connection API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		$payload = array(
			'userName'  => $username,
			'apiKey'    => $api_key,
			'apiSecret' => $api_secrete,
		);
		$this->write_log( 'Payload => ' . PHP_EOL . json_encode( $payload ) . PHP_EOL );
		$curl = curl_init();
		$this->write_log( 'Request URL => ' . PHP_EOL . json_encode( $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/shipping/auth/v2' ) . PHP_EOL );
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/shipping/auth/v2',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => json_encode( $payload ),
				CURLOPT_HTTPHEADER     => array(
					'content-type: application/json',
				),
			)
		);
		$response = json_decode( curl_exec( $curl ) );
		$this->write_log( 'Response => ' . PHP_EOL . json_encode( $response ) . PHP_EOL );
		curl_close( $curl );
		return $response;
	}

	/**
	 * Method update_connection
	 *
	 * @param $connection_id $connection_id This is connection id.
	 * @param token         $token         This is Authentication Token.
	 * @param api_key       $api_key       This is API Key.
	 *
	 * @return array
	 */
	public function update_connection( $connection_id, $token, $api_key ) {
		$this->api_log_count();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling Update New Connection API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		$this->write_log( 'Payload => ' . PHP_EOL . json_encode( array( 'userName' => $api_key ) ) . PHP_EOL );
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/auth/v2',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'PUT',
				CURLOPT_POSTFIELDS     => json_encode(
					array(
						'userName' => $api_key,
					)
				),
				CURLOPT_HTTPHEADER     => array(
					'content-type: application/json',
					'authorization: Bearer ' . $token,
					'cache-control: no-cache',
				),
			)
		);
		$response = json_decode( curl_exec( $curl ) );
		$this->write_log( 'Response => ' . PHP_EOL . json_encode( $response ) . PHP_EOL );
		return $response;
	}

	/**
	 * Method delete_connection
	 *
	 * @param $connection_id $connection_id This is Connection ID.
	 * @param token         $token         This is Authentication Token.
	 *
	 * @return array
	 */
	public function delete_connection( $connection_id, $token ) {
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/auth/v2',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'DELETE',
				CURLOPT_HTTPHEADER     => array(
					'content-type: application/json',
					'authorization: Bearer ' . $token,
					'cache-control: no-cache',
				),
			)
		);
		$response = json_decode( curl_exec( $curl ) );
		return $response;
	}

	/**
	 * Method get_shipping_services
	 *
	 * @param $connection_id $connection_id This is Connection ID.
	 *
	 * @return array
	 */
	public function get_shipping_services( $connection_id ) {
		$url = 'https://api.itembase.com/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/services';

		$curl = curl_init( $url );
		curl_setopt( $curl, CURLOPT_URL, $url );
		curl_setopt( $curl, CURLOPT_RETURNTRANSFER, true );

		// for debug only!
		curl_setopt( $curl, CURLOPT_SSL_VERIFYHOST, false );
		curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER, false );

		$response = curl_exec( $curl );
		curl_close( $curl );
		return $response;
	}

	/**
	 * Method get_services
	 *
	 * @param token          $token          This is Authentication token.
	 * @param connection_id  $connection_id  This is connection id.
	 * @param $whole_response $whole_response This is flag to get whole response.
	 *
	 * @return array
	 */
	public function get_services( $token, $connection_id, $whole_response = false ) {
		$this->api_log_count();
		$curl = curl_init();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling GEt Shipping Services API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/services',
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					'authorization: Bearer ' . $token,
					'cache-control: no-cache',
				),
			)
		);
		$response = json_decode( curl_exec( $curl ) );

		$this->write_log( 'API response => ' . PHP_EOL . json_encode( $response ) . PHP_EOL );
		curl_close( $curl );
		$services = array();
		foreach ( $response->services as $key => $value ) {
			foreach ( $response->allowedServices as $k => $val ) {
				if ( $key == $val ) {
					$services[ $key ] = $value;
				}
			}
		}
		$this->write_log( 'API response => ' . PHP_EOL . json_encode( $services ) . PHP_EOL );
		return ( true == $whole_response ) ? $response : $services;
	}

	/**
	 * Method create_shipping_label
	 *
	 * @param payload       $payload       This is payload.
	 * @param $connection_id $connection_id This is connection id.
	 * @param token         $token         This is authentication token.
	 *
	 * @return array
	 */
	public function create_shipping_label( $payload, $connection_id, $token ) {
		$this->api_log_count();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling Create Shipping Label API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		$this->write_log( 'Request URL => ' . PHP_EOL . json_encode( $payload ) . PHP_EOL );

		$url = $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/label';
		$this->write_log( 'Request Payload => ' . PHP_EOL . $url . PHP_EOL );

		$ch = curl_init();
		curl_setopt( $ch, CURLOPT_URL, $url );
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
		curl_setopt( $ch, CURLOPT_POST, 1 );
		curl_setopt( $ch, CURLOPT_POSTFIELDS, json_encode( $payload ) );
		$headers   = array();
		$headers[] = 'Cache-Control: no-cache';
		$headers[] = 'Content-Type: application/json';
		$headers[] = 'authorization: Bearer ' . $token;
		curl_setopt( $ch, CURLOPT_HTTPHEADER, $headers );
		$result = curl_exec( $ch );
		$err    = curl_error( $ch );
		$this->write_log( 'Response => ' . PHP_EOL . $result . PHP_EOL );
		$result = json_decode( $result );
		curl_close( $ch );
		$response = array(
			'result'  => json_encode( $result ),
			'payload' => json_encode( $payload ),
		);
		return $response;
	}

	/**
	 * Method get_shipping_label
	 *
	 * @param $tracking_number $tracking_number This is a Tracking Number.
	 * @param connection_id   $connection_id   This is connection ID.
	 * @param token           $token           This is authentication token.
	 * @param label_format    $label_format    This is label format.
	 *
	 * @return array
	 */
	public function get_shipping_label( $tracking_number, $connection_id, $token, $label_format = 'PDF' ) {
		$this->api_log_count();
		$curl = curl_init();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling Get Shipping Label API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/label?trackingNumber=' . $tracking_number . '&labelFormat=' . $label_format,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					'authorization: Bearer ' . $token,
				),
			)
		);
		$response = curl_exec( $curl );
		$this->write_log( 'Response => ' . PHP_EOL . $response . PHP_EOL );
		curl_close( $curl );
		return $response;
	}

	/**
	 * Method cancel_shipment
	 *
	 * @param $tracking_number $tracking_number This is tracking Number.
	 * @param connection_id   $connection_id   This is connection ID.
	 * @param token           $token           This is Authentication Token.
	 *
	 * @return array
	 */
	public function cancel_shipment( $tracking_number, $connection_id, $token ) {
		$curl = curl_init();
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/label?trackingNumber=' . $tracking_number,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'DELETE',
				CURLOPT_HTTPHEADER     => array(
					'authorization: Bearer ' . $token,
				),
			)
		);
		$response = curl_exec( $curl );
		curl_close( $curl );
		return $response;
	}

	/**
	 * Method track_shipment
	 *
	 * @param $tracking_number $tracking_number This is Tracking Number.
	 * @param connection_id   $connection_id   This is Connection ID.
	 * @param token           $token           This is authentication token.
	 *
	 * @return array
	 */
	public function track_shipment( $tracking_number, $connection_id, $token ) {
		$this->api_log_count();
		$curl = curl_init();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling Track Shipment API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/shipment/track?trackingNumber=' . $tracking_number,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					'authorization: Bearer ' . $token,
				),
			)
		);
		$response = curl_exec( $curl );
		$this->write_log( 'Request Response => ' . PHP_EOL . $response . PHP_EOL );
		curl_close( $curl );
		return $response;
	}

	/**
	 * Method get_carrier_location
	 *
	 * @param country       $country       This is country name.
	 * @param zipcode       $zipcode       This is a zipcode.
	 * @param $connection_id $connection_id This is a connection id.
	 * @param token         $token         This is Authentication token.
	 *
	 * @return array
	 */
	public function get_carrier_location( $country, $zipcode, $connection_id, $token ) {
		$this->api_log_count();
		$curl = curl_init();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling Carrier Location API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/locations?country=' . $country . '&zip=' . $zipcode,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'GET',
				CURLOPT_HTTPHEADER     => array(
					'authorization: Bearer ' . $token,
				),
			)
		);
		$response = curl_exec( $curl );
		$this->write_log( 'Request Response => ' . PHP_EOL . $response . PHP_EOL );
		curl_close( $curl );
		return $response;
	}

	/**
	 * Method bulk_api_calls
	 *
	 * @param $urls $urls This is a bulk URL's.
	 *
	 * @return array
	 */
	public function bulk_api_calls( $urls ) {
		$this->api_log_count();
		$curl_arr = array();
		$master   = curl_multi_init();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );

		foreach ( $urls as $key => $value ) {
			$request_url = $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $value['url'];
			$this->write_log( 'Request URL => ' . PHP_EOL . $request_url . PHP_EOL );
			$curl_arr[ $key ] = curl_init( $request_url );
			curl_setopt( $curl_arr[ $key ], CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl_arr[ $key ], CURLOPT_ENCODING, '' );
			curl_setopt( $curl_arr[ $key ], CURLOPT_MAXREDIRS, 10 );
			curl_setopt( $curl_arr[ $key ], CURLOPT_TIMEOUT, 30 );
			curl_setopt( $curl_arr[ $key ], CURLOPT_CUSTOMREQUEST, 'GET' );
			curl_setopt(
				$curl_arr[ $key ],
				CURLOPT_HTTPHEADER,
				array(
					'authorization: Bearer ' . $value['token'],
				)
			);
			curl_multi_add_handle( $master, $curl_arr[ $key ] );
		}

		do {
			curl_multi_exec( $master, $running );
		} while ( $running > 0 );

		$result = array();
		foreach ( $urls as $key => $value ) {
			$result[] = curl_multi_getcontent( $curl_arr[ $key ] );
		}
		$this->write_log( 'Request Response => ' . PHP_EOL . json_encode( $result ) . PHP_EOL );
		return $result;
	}

	/**
	 * Method get_order_shipment_info
	 *
	 * @param $connection_id $connection_id This is connection id.
	 * @param token         $token         This is authentication token.
	 * @param payload       $payload       This is API payload.
	 *
	 * @return array
	 */
	public function get_order_shipment_info( $connection_id, $token, $payload ) {
		$this->api_log_count();
		$curl = curl_init();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling Get Order Shipment Info API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		$url = $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $connection_id . '/shipping/api/v2/shipment/order/info';
		$this->write_log( 'Requested URL => ' . PHP_EOL . $url . PHP_EOL );
		$this->write_log( 'Requested Payload => ' . PHP_EOL . json_encode( $payload ) . PHP_EOL );

		curl_setopt_array(
			$curl,
			array(
				CURLOPT_URL            => $url,
				CURLOPT_RETURNTRANSFER => true,
				CURLOPT_ENCODING       => '',
				CURLOPT_MAXREDIRS      => 10,
				CURLOPT_TIMEOUT        => 30,
				CURLOPT_HTTP_VERSION   => CURL_HTTP_VERSION_1_1,
				CURLOPT_CUSTOMREQUEST  => 'POST',
				CURLOPT_POSTFIELDS     => json_encode( $payload ),
				CURLOPT_HTTPHEADER     => array(
					'authorization: Bearer ' . $token,
					'Content-Type:application/json',
				),
			)
		);
		$response = curl_exec( $curl );
		$this->write_log( 'Response => ' . PHP_EOL . $response . PHP_EOL );
		curl_close( $curl );
		return $response;
	}

	/**
	 * Method convert_csv_to_json
	 *
	 * @param $path $path This is path to store.
	 *
	 * @return array
	 */
	public function convert_csv_to_json( $path ) {
		$file = fopen( $path, 'r' );
		$data = array();
		while ( ( $row = fgetcsv( $file ) ) !== false ) {
			$data[] = $row;
		}
		return $data;
	}

	/**
	 * Method bulk_api_calls_post
	 *
	 * @param $urls $urls This is bulk url's.
	 *
	 * @return array
	 */
	public function bulk_api_calls_post( $urls ) {
		$this->api_log_count();

		$curl_arr = array();
		$master   = curl_multi_init();
		$this->write_log( '==================================================================================' . PHP_EOL . 'Calling API (Date & Time=>' . gmdate( 'Y-m-d H:i:s' ) . ')' . PHP_EOL . '==================================================================================' );
		foreach ( $urls as $key => $value ) {
			$request_url = $this->base_url . '/connectivity/instances/' . $this->instance_id . '/connections/' . $value['url'];
			$this->write_log( 'Request URL => ' . PHP_EOL . $request_url . PHP_EOL );
			$curl_arr[ $key ] = curl_init( $request_url );
			curl_setopt( $curl_arr[ $key ], CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $curl_arr[ $key ], CURLOPT_ENCODING, '' );
			curl_setopt( $curl_arr[ $key ], CURLOPT_MAXREDIRS, 10 );
			curl_setopt( $curl_arr[ $key ], CURLOPT_TIMEOUT, 30 );
			curl_setopt( $curl_arr[ $key ], CURLOPT_CUSTOMREQUEST, 'POST' );
			curl_setopt(
				$curl_arr[ $key ],
				CURLOPT_HTTPHEADER,
				array(
					'authorization: Bearer ' . $value['token'],
					'Content-Type: application/json',
				)
			);
			curl_setopt( $curl_arr[ $key ], CURLOPT_POST, true );
			curl_setopt( $curl_arr[ $key ], CURLOPT_POSTFIELDS, json_encode( $value['payload'] ) );
			curl_multi_add_handle( $master, $curl_arr[ $key ] );
		}

		do {
			curl_multi_exec( $master, $running );
		} while ( $running > 0 );

		$result = array();
		foreach ( $urls as $key => $value ) {
			$result[] = curl_multi_getcontent( $curl_arr[ $key ] );
		}
		$this->write_log( 'Request Response => ' . PHP_EOL . json_encode( $result ) . PHP_EOL );
		return $result;
	}
}
