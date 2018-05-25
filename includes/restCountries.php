<?php
/**
 * Created by PhpStorm.
 * User: bislewl
 * Date: 5/23/2018
 * Time: 5:15 PM
 */

class restCountries{

	var $filter = '';
	var $limit = 50;
	var $queryParameters = array();


	function searchBoxInput($input){

	}

	function searchSort(){

	}

	function searchLimit($limit){
		$this->limit = (int)$limit;

		return;
	}

	function filterResults($fields){
		$this->queryParameters[] = 'fields=' . implode(';', $fields);

		return;
	}


	function apiCall($path){
		$url = 'https://restcountries.eu/rest/v2/';
		$url .= $path;
		if(is_array($this->queryParameters) && count($this->queryParameters) > 0){
			$url .= '?' . implode('&', $this->queryParameters);
		}
		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
		curl_setopt($curl, CURLOPT_HEADER, false);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		if(isset($params) && is_array($params) && count($params) > 0){
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($params));
		}
		$response = curl_exec($curl);
		curl_close($curl);
		$return = json_decode($response, true);

		return $return;
	}

	// https://restcountries.eu/rest/v2/all
	function getAll(){

	}

	// https://restcountries.eu/rest/v2/name/{name} or https://restcountries.eu/rest/v2/name/{name}?fullText=true
	function getByName($query, $full_name = false){
		if($full_name == true){
			$this->queryParameters[] = 'fullText=true';
		}
	}

	// https://restcountries.eu/rest/v2/alpha/{code}
	function getByCode($code){
		$query = (is_array($code)) ? implode(';', $code) : $code;
	}

	// https://restcountries.eu/rest/v2/currency/
	function getByCurrency(){

	}

	// https://restcountries.eu/rest/v2/lang/{et}
	function getByLanguage(){

	}

	// https://restcountries.eu/rest/v2/capital/{capital}
	function getByCapitalCity(){

	}

	// https://restcountries.eu/rest/v2/callingcode/{callingcode}
	function getByCallingCode(){

	}

	// https://restcountries.eu/rest/v2/region/{region}
	function getByRegion(){

	}

	// https://restcountries.eu/rest/v2/regionalbloc/{regionalbloc}
	function getByRegionalBloc(){

	}

	function getAutoFillValues($string){
		$return    = array();
		$countries = array();
		$this->filterResults(array('name', 'alpha2code', 'alpha3code', 'flag'));
		$name_array = $this->getByName($string);
		$code_array = $this->getByCode($string);
		foreach($name_array as $name_item){
			$countries[$name_item['alpha3Code']] = array(
				'alpha2Code' => $name_item['alpha2Code'],
				'alpha3Code' => $name_item['alpha3Code'],
				'name'       => $name_item['alpha3Code'],
				'flag'       => $name_item['flag'],
			);
		}
		foreach($code_array as $code_item){
			$countries[$code_item['alpha3Code']] = array(
				'alpha2Code' => $code_item['alpha2Code'],
				'alpha3Code' => $code_item['alpha3Code'],
				'name'       => $code_item['alpha3Code'],
				'flag'       => $code_item['flag'],
			);
		}

		return $return;
	}
}