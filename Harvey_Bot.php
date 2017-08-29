<?php
class Harvey_Bot {
	private $google_maps_api_key = 'AIzaSyACDJY9JCwE_3ksIY2Ny-aKdkte2xw5ruw';

	public function connect() {
		return mysqli_connect('localhost','root','','collegeh_houston');
	}

	public function search_for_address($text) {
		$address_array = array();
		$address_endings = array('alley','aly','annex','anex','anx','arcade','arc','avenue','ave','bayou','byu','boulevard','blvd','branch','br','bridge','brg','brook','brk','center','ctr','circle','cir','court','ct','drive','dr','expressway','expy','fld','flts','frge','freeway','fwy','gtwy','highway','hwy','lane','ln','lodge','ldg','manor','mnr','meadow','mdw','mdws','park','pkwy','parkway','place','pl','plaza','plz','road','rd','route','rte','skyway','skwy','street','st','terrace','ter','trafficway','trfy','way');
		$address_started = 0;
		for ($i = sizeof($text) - 1; $i >= 0; $i--) { 
			$word = preg_replace("/[^a-zA-Z 0-9]+/", "", $text[$i]);
			switch ($address_started) {
				case 0:
					if (in_array($word, $address_endings)) {
						$address_started = 1;
						array_push($address_array, $word);
					} 
					break;
				case 1:
					array_push($address_array, $word);
					if (is_numeric($word)) {
						$address_started = 2;
 					}
					break;
				case 2:
					break;
				default:
					break;
			}
		}
		$new_array = array_reverse($address_array);
		if (empty($new_array)) {
			return '1600+Main+Street';
		} 
		return implode("+", $new_array);
	}

	public function get_coords($address) {
		$coords = array();
		$url = 'https://maps.googleapis.com/maps/api/geocode/json?address='.$address.',+Houston,+Tx&key='.$this->google_maps_api_key;
		$data = json_decode(file_get_contents($url),true);
		$coords['lat'] = $data['results'][0]['geometry']['location']['lat'];
		$coords['lng'] = $data['results'][0]['geometry']['location']['lng'];
		return $coords;
	}

	public function get_closest_org($lat, $lng) {
		$data = mysqli_query($this->connect(), "SELECT * FROM shelters ORDER BY SQRT(POW(latitude - '$lat',2) + POW(longitude - '$lng',2)) ASC LIMIT 1");
		return mysqli_fetch_assoc($data);
	}

	public function return_message ($org_name, $address) {
		return 'The nearest hurricane shelter to you is '.$org_name.' on '.$address.'.';
	}

	public function main($text) {

		$address = $this->search_for_address(explode(" ", $text));
		$coords = $this->get_coords($address);
		$closest_org = $this->get_closest_org($coords['lat'], $coords['lng']);
		return $this->return_message($closest_org['org_name'],$closest_org['address']);
	}
}