<?php

class UserRequest
{
    protected $post;
    protected $command;
    protected $input_value;

    const ERROR_UNKNOWN_COMMAND = 1;
    const ERROR_DATABASE_CONNECTION = 2;
    const ERROR_DATABASE_CONNECTION_REQUISITES = 3;
    const ERROR_EMPTY_RESULT = 4;

    public function __construct() {
        $this->post = $_POST;
        $this->input_value = $this->getParameter('input_value');
    }

    public function processRequest() {
        /* Если post не пришёл - ничего не делаем */
        if (! $this->post) {
            return;
        }

        $result = array();

        try {
            $this->connectDB();

            $commands = $this->filterData($this->input_value);
            if (count($commands)) {
                foreach($commands as $c) {
                    if (! $c[0]) {
                        continue;
                    }
                    $this->command = new Command($c);

                        switch (strtolower($c[0])) {
                            case 'show':
                                $result[] = $this->command->processShow();
                                break;
                            case 'describe':
                                $result[] = $this->command->processDescribe();
                                break;
                            case 'count_rows';
                                $result[] = $this->command->processCountRows();
                                break;
                            case 'add_rows';
                                $result[] = $this->command->processAddRows();
                                break;
                            case 'select_from';
                                $result[] = $this->command->processSelectFrom();
                                break;
                            case 'clear';
                                $result[] = $this->command->processClear();
                                break;
                            default:
                                if (! $this->input_value) {
                                    $result[] = "";
                                } else {
                                    throw new Exception("", self::ERROR_UNKNOWN_COMMAND);
                                }
                                break;
                        }
                    if ($this->input_value && count($result) == 1 && ! $result[0]) {
                        throw new Exception("", self::ERROR_EMPTY_RESULT);
                    }
                }
            }
        } catch(Exception $e) {
            $result[] = $this->getErrorMessage($e->getCode());
        }
        $this->outputResult($result);
    }

    public function getParameter($parameter) {
        if ($this->post[$parameter]) {
            return trim($this->post[$parameter]);
        } else {
            return "";
        }
    }

    protected function connectDB() {
        $db_name = $this->getParameter('db_name');
        $db_user = $this->getParameter('db_user');
        $db_pass = $this->getParameter('db_pass');

        if (! $db_name || ! $db_user || ! $db_pass) {
            throw new Exception("", self::ERROR_DATABASE_CONNECTION);
        }

        DataBase::getInstance($db_name, $db_user, $db_pass);
    }

    protected function filterData($input) {
        /* Сначала определим, может введено несколько блоков команд через ; */
        $a_input = explode(';', trim($input));
        $result = array();

        if (count($a_input)) {
            foreach($a_input as $command) {
                /* Здесь делим команду на лексемы */
                $result[] = explode(' ', trim($command));
            }
        }

        return $result;
    }

    protected function outputResult($result) {
        $string = "";
        if (count($result)) {
            foreach($result as $item) {
                $string .= $item . '<br>';
            }
        }

        History::addHistoryData($string, $this->input_value);
    }

    public function getErrorMessage($error_code) {
        switch($error_code) {
            case self::ERROR_UNKNOWN_COMMAND:
                return "unknown command!<br>";
                break;

            case self::ERROR_DATABASE_CONNECTION:
                return "wrong data for DB connection.<br>";
                break;

            case self::ERROR_DATABASE_CONNECTION_REQUISITES:
                return "access denied for user with the current requisites";
                break;

            case self::ERROR_EMPTY_RESULT:
                return ">>empty result";
                break;
        }
    }
}