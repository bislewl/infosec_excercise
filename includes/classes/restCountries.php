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
	var $offset = 0;
	var $queryParameters = array();
	var $errors = array();
	var $sortBy = '0|3';


	function validateInput($string){
		$valid = true;
		if(!preg_match('/^[a-zA-Z\s]+$/', $string)){
			$this->addError(TEXT_ERROR_INPUT_BAD);
			$valid = false;
		}

		if(strlen($string) == 0){
			$this->addError(TEXT_ERROR_INPUT_MISSING);
			$valid = false;
		}

		if(strlen($string) == 1){
			$this->addError(TEXT_ERROR_INPUT_MINIMUM);
			$valid = false;
		}

		return $valid;
	}

	public function setSort($sortby = '0|3'){
		$this->sortBy = $sortby;

		return;
	}

	public function setLimit($limit){
		$this->limit = (int)$limit;

		return;
	}

	public function setOffset($offset){
		$this->offset = (int)$offset;

		return;
	}

	function searchSort($countries){
		$sort_by           = $this->sortBy;
		$sorts             = explode('|', $sort_by);
		$sort_array_string = '';
		foreach($sorts as $sort){
			switch($sort){
				case '0': // Name ASC
					$sort_array_string .= '$name,SORT_ASC,SORT_STRING,';
					break;
				case '1': // Name DESC
					$sort_array_string .= '$name,SORT_DESC,SORT_STRING, ';
					break;
				case '2': // population ASC
					$sort_array_string .= '$population,SORT_ASC,SORT_NUMERIC';
					break;
				case '3': // population DESC
					$sort_array_string .= '$population,SORT_DESC,SORT_STRING , ';
					break;
				case '4': // alpha2code ASC
					$sort_array_string .= '$alpha2Code,SORT_ASC,SORT_STRING , ';
					break;
				case '5': // alpha2code DESC
					$sort_array_string .= '$alpha2Code,SORT_DESC,SORT_STRING, ';
					break;
				case '6': // alpha3code ASC
					$sort_array_string .= '$alpha3Code,SORT_ASC,SORT_STRING, ';
					break;
				case '7': // alpha3code DESC
					$sort_array_string .= '$alpha3Code,SORT_DESC,SORT_STRING, ';
				default:
					break;
			}
		}
		foreach($countries as $key => $row){
			$name[$key]       = $row['name'];
			$population[$key] = $row['population'];
			$alpha2Code[$key] = $row['alpha2Code'];
			$alpha3Code[$key] = $row['alpha3Code'];
		}
//		echo $sort_array_string;
		$eval = 'array_multisort( ' . $sort_array_string . ' $countries );';
//		echo $eval;
		eval($eval);
//		array_multisort($sort_array,eval($sort_array_string));
		$results = $countries;

		return $results;
	}

	function filterResults($fields){
		$this->queryParameters[] = 'fields=' . implode(';', $fields);

		return;
	}

	function resetFilter(){
		$this->queryParameters = array();

		return;
	}


	function apiCall($path, $value = ''){
		$url = 'https://restcountries.eu/rest/v2/';
		$url .= $path . '/' . $value;
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
//		echo $response;
		$result               = json_decode($response, true);
		$invalid_status_codes = array('404', '400');
		if(isset($result['status']) && in_array($result['status'], $invalid_status_codes)){
			$return = false;
		} else{
			$return = $result;
		}

		return $return;
	}

	// https://restcountries.eu/rest/v2/name/{name}
	function getByName($name){
		$return = $this->apiCall('name', $name);

		return $return;
	}

	// https://restcountries.eu/rest/v2/alpha/{code}
	public function getByCode($code){
		$query  = (is_array($code)) ? implode(';', $code) : $code;
		$return = $this->apiCall('alpha', $query);

//		echo 'getByCode' . json_encode($return);
		return $return;
	}

	public function getCountryDetail($country_array){
		foreach($country_array as $country){
			$country_code = $country['alpha3Code'];
		}
		$this->resetFilter();
		$return = $this->getByCode($country_code);

//		echo 'getCountryDetail' . json_encode($return);

		return $return;
	}

	public function processResults($countries){
		$count           = count($countries);
		$return          = array();
		$return['count'] = $count;
		switch(true){
			case ($count == 1):
//				echo json_encode($country_array);
				$return['data'] = $this->getCountryDetail($countries);
//				echo json_encode($return['data']);
				$return['details'] = $this->countryDetailsHtml($return['data']);
				break;
			case ($count > 1):
//				echo json_encode($countries);
				foreach($countries as $country){
					$regions[]    = $country['region'];
					$subregions[] = $country['subregion'];
				}
				$data                 = $this->searchSort($countries);
				$return['data']       = array_slice($data, $this->offset, $this->limit);
				$return['regions']    = array_count_values($regions);
				$return['subregions'] = array_count_values($subregions);
				break;
			case ($count == 0):
			default:
				$this->addError(TEXT_ERROR_RESULTS_NONE, 2);
				break;
		}

		return $return;
	}

	public function getSearchResults($string){
		$valid = $this->validateInput($string);
		if($valid == true){
			$countries = array();
			$this->filterResults(array('name', 'alpha2Code', 'alpha3Code', 'flag','population', 'region', 'subregion'));
			$name_array = $this->getByName($string);
			$code_array = $this->getByCode($string);
			if($name_array != false){
				foreach($name_array as $name_item){
					$countries[$name_item['alpha3Code']] = array(
						'alpha2Code' => $name_item['alpha2Code'],
						'alpha3Code' => $name_item['alpha3Code'],
						'name'       => $name_item['name'],
						'flag'       => $name_item['flag'],
						'population' => $name_item['population'],
						'region'     => $name_item['region'],
						'subregion'  => $name_item['subregion'],
					);
				}
			}
			if($code_array != false){
				if(isset($code_array['alpha3Code'])){
					if($code_array['alpha3Code'] == $string){
						$return['data'] = $this->getCountryDetail($code_array['alpha3Code']);
//						echo json_encode($return['data']);
						$return['details'] = $this->countryDetailsHtml($return['data']);
						$return['status']  = 'success';
						empty($countries);
					}
				} else{
					foreach($code_array as $code_item){
						$countries[$code_item['alpha3Code']] = array(
							'alpha2Code' => $code_item['alpha2Code'],
							'alpha3Code' => $code_item['alpha3Code'],
							'name'       => $code_item['name'],
							'flag'       => $code_item['flag'],
							'population' => $code_item['population'],
							'region'     => $code_item['region'],
							'subregion'  => $code_item['subregion'],
						);
					}
				}
			}

			if(count($countries) == 0 && !isset($return['status'])){
				$this->addError(TEXT_ERROR_RESULTS_NONE, 2);
				$return['status'] = 'error';
			}
			if(count($countries) > 0 && !isset($return['status'])){
				$return           = $this->processResults($countries);
				$return['status'] = 'success';
			}
			if(count($countries > 1) && isset($return['regions'])){
				$return['summeryHTML'] = $this->countriesResultsSummary($return);
			}
		}
		$return['errors'] = $this->errors;

		return $return;
	}

	function getAutoFillValues($string){
		$valid = $this->validateInput($string);
		if($valid == true){
			$countries = array();
			$this->setLimit(10);
			$this->filterResults(array('name', 'alpha2Code', 'alpha3Code', 'population', 'flag', 'region', 'subregion'));
			$name_array = $this->getByName($string);
			$code_array = $this->getByCode($string);
			if($name_array != false){
				foreach($name_array as $name_item){
					$countries[$name_item['alpha3Code']] = array(
						'alpha2Code' => $name_item['alpha2Code'],
						'alpha3Code' => $name_item['alpha3Code'],
						'population' => $name_item['population'],
						'name'       => $name_item['name'],
						'flag'       => $name_item['flag'],
						'region'     => $name_item['region'],
						'subregion'  => $name_item['subregion'],
					);
				}
			}

			if($code_array != false){
				if(isset($code_array['alpha3Code'])){
					if($code_array['alpha3Code'] == $string){
						$return['data']   = $this->getCountryDetail($code_array['alpha3Code']);
						$return['status'] = 'success';
//						empty($countries);
					}
				} else{
					foreach($code_array as $code_item){
						$countries[$code_item['alpha3Code']] = array(
							'alpha2Code' => $code_item['alpha2Code'],
							'alpha3Code' => $code_item['alpha3Code'],
							'population' => $code_item['population'],
							'name'       => $code_item['name'],
							'flag'       => $code_item['flag'],
							'region'     => $code_item['region'],
							'subregion'  => $code_item['subregion'],
						);
					}
				}
			}
			if(count($countries) == 0 && !isset($return['status'])){
				$this->addError(TEXT_ERROR_RESULTS_NONE, 2);
				$return['status'] = 'error';
			}
			if(count($countries) > 0 && !isset($return['status'])){
				$return           = $this->processResults($countries);
				$return['status'] = 'success';
			}
			if(count($countries > 1) && isset($return['regions'])){
				$return['summeryHTML'] = $this->countriesResultsSummary($return);
			}
		}

		$return['errors'] = $this->errors;

		return $return;
	}

	public function countriesResultsSummary($result){
		$html         = '';
		$html         .= TEXT_RESULTS_COUNT . $result['count'] . "<br/>";
		$regions_list = array();
		$regions      = $result['regions'];
		foreach($regions as $region => $regionCount){
			$regions_list[] = $region . ': ' . $regionCount;
		}
		$html            .= TEXT_RESULTS_REGIONS . implode(', ', $regions_list) . "<br/>";
		$subregions_list = array();
		$subregions      = $result['subregions'];
		foreach($subregions as $subregion => $subregionCount){
			$subregions_list[] = $subregion . ': ' . $subregionCount;
		}
		$html .= TEXT_RESULTS_SUB_REGIONS . implode(', ', $subregions_list) . "<br/>";

		return $html;
	}

	function countryDetailsHtml($result){
		$html      = '<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">' .
		             '<h2>' . $result['name'] . '</h2>' .
		             '</div>';
		$languages = array();
		foreach($result['languages'] as $language){
			$languages[] = $language['name'];
		}
		$html .= '<div class="col-xs-12 col-sm-12 col-md-9 col-lg-9"><ul>' .
		         '<li><span class="detailsTitle">' . TEXT_DETAILS_CODE_TWO . '</span> ' . $result['alpha2Code'] .'</li>'.
		         '<li><span class="detailsTitle">' . TEXT_DETAILS_CODE_THREE . '</span> ' . $result['alpha3Code'] .'</li>'.
		         '<li><span class="detailsTitle">' . TEXT_DETAILS_CODE_POPULATION . '</span> ' . $result['population'] .'</li>'.
		         '<li><span class="detailsTitle">' . TEXT_DETAILS_CODE_LANGUAGES . '</span> ' . implode(', ', $languages).'</li>'.
		         '<li><span class="detailsTitle">' . TEXT_DETAILS_CODE_REGION . '</span> ' . $result['region'] .'</li>'.
		         '<li><span class="detailsTitle">' . TEXT_DETAILS_CODE_SUBREGION . '</span> ' . $result['subregion'] .'</li>'.
		         '</ul></div>';
		$html .= '<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
			<img src="' . $result['flag'] . '" title="' . $result['name'] . '"/>
		</div>';

		return $html;
	}

	function addError($message, $severity = 1){
		$this->errors[] = array('message' => $message, 'severity' => $severity);

		return;
	}
}