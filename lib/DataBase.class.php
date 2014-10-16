<?php

class DataBase
{
    private  static $connection;

    public static function getInstance($db_name, $user, $pass) {
        if (! self::$connection) {
            self::$connection = new MySqli('localhost', $user, $pass, $db_name);
            if (self::$connection->connect_error) {
                throw new Exception('', UserRequest::ERROR_DATABASE_CONNECTION_REQUISITES);
            }
        }
        return self::$connection;
    }

    public static function query($query) {
        $result = self::$connection->query($query);
        $string = "";
        $array = array();
        if (is_object($result)) {
            $array = $result->fetch_all();
            if (count($array)) {
                foreach($array as $a) {
                    $string .= $a[0] . '<br>';
                }
            }
        }
        return array('string' => $string, 'array' => $array);
    }
}