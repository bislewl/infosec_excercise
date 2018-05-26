<?php
/**
 * Created by PhpStorm.
 * User: bislewl
 * Date: 5/23/2018
 * Time: 4:55 PM
 */

header("Content-Type: application/json; charset=UTF-8");
include('includes/application_top.php');
$api          = new restCountries();
$errors       = array();
$return       = array();
$action       = filter_var($_GET['action'], FILTER_SANITIZE_STRING);
$query_string = filter_var($_POST['country'], FILTER_SANITIZE_STRING);
if(isset($_POST['sort_by']) && $_POST['sort_by'] != ''){
	$sort_by = filter_var($_POST['sort_by'], FILTER_SANITIZE_STRING);
	$api->setSort($sort_by);
}
if(isset($_POST['limit']) && $_POST['limit'] != ''){
	$limit = filter_var($_POST['limit'], FILTER_SANITIZE_NUMBER_INT);
	$api->setLimit($limit);
}

if(isset($_POST['offset']) && $_POST['offset'] != ''){
	$offset = filter_var($_POST['offset'], FILTER_SANITIZE_NUMBER_INT);
	$api->setOffset($offset);
}
switch($action){
	case 'autoSuggest':
		$return = $api->getAutoFillValues($query_string);
		break;
	case 'search':
		$return = $api->getSearchResults($query_string);
		break;
	default:
		$return['errors'][] = array('message' => TEXT_ERROR_PROCESSING, 'severity' => 1);
		break;
}
if(!isset($return['status'])) $return['status'] = 'error';

echo json_encode($return);