<?php
include_once(__DIR__ . '/../lib/UserRequest.class.php');
include_once(__DIR__ . '/../lib/DataBase.class.php');
include_once(__DIR__ . '/../lib/History.class.php');
include_once(__DIR__ . '/../lib/Command.class.php');
error_reporting(E_ERROR | E_PARSE);

$request = new UserRequest();
$request->processRequest();

$html = file_get_contents('tpl.html');
$html = str_replace('%history%', History::getHistoryData(), $html);
$html = str_replace('%db_name%', $request->getParameter('db_name'), $html);
$html = str_replace('%db_user%', $request->getParameter('db_user'), $html);
$html = str_replace('%db_pass%', $request->getParameter('db_pass'), $html);


echo $html;