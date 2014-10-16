<?php

class Command
{
    public  $text;

    public function __construct($text) {
        $this->text = $text;
    }

    /* show tables */
    public function processShow() {
        if (strtolower($this->text[1]) == 'tables') {

            $result = DataBase::query('show tables');
            return $result['string'];
        }
    }

    /* count_rows `table` */
    public function processCountRows() {
        if ($table_name = $this->text[1]) {

            $result = DataBase::query('select count(*) from ' . $table_name);
            return $result['string'];
        } else {
            return "";
        }
    }

    /* add_rows `table_name` `count` */
    public function processAddRows() {
        $count = abs( (isset($this->text[2]) ? $this->text[2] : 0 ) * 1);
        if ( ($table_name = $this->text[1]) && ($count) ) {
            $table_structure = $result = DataBase::query('describe ' . $table_name);
            for ($i = 1; $i <= $count; $i++) {
                $q1 = "";
                $q2 = "";
                if (count($table_structure)) {
                    foreach($table_structure['array'] as $row) {
                        $q1 .= $row[0] . ',';
                        if ($row[5] == 'auto_increment') {
                            $q2 .= 'NULL' . ',';
                        } else {
                            $a_type = explode('(', $row[1]);
                            $field_type = $a_type[0];
                            $limit = "";

                            if (isset($a_type[1]) && $a_type[1]) {
                                $a_limit = explode(')', $a_type[1]);
                                $limit = $a_limit[0];
                            }

                            switch(strtolower($field_type)) {
                                case 'int':
                                    if ($limit) {
                                        $value = rand(1, pow(10, $limit));
                                    } else {
                                        $value = rand(1, pow(10, 10));
                                    }
                                    break;
                                case 'tinyint':
                                    $value = rand(1, pow(10, $limit));
                                    break;
                                case 'mediumint':
                                    $value = rand(1, pow(10, $limit));
                                    break;
                                case 'varchar':
                                    $value = "'" . $this->generateString($limit) . "'";
                                    break;
                                case 'longtext':
                                    $value = "'" . $this->generateString(pow(2, 10)) . "'"; /* Предел 2^32 */
                                    break;
                                case 'date':
                                    $value = "'" . $this->generateDate() . "'";
                                    break;
                                case 'datetime':
                                    $value = "'" . $this->generateDateTime() . "'";
                                    break;
                                /* И так далее для каждого типа... */
                                default:
                                    $value = "''";
                                    break;
                            }
                            $q2 .= $value . ',';
                        }
                    }
                }

                $q1 = trim($q1, ',');
                $q2 = trim($q2, ',');

                $query = "INSERT INTO $table_name ($q1) VALUES ($q2)";

                $result = DataBase::query($query);
            }

            return "complete!";
        } else {
            return "";
        }
    }

    /* describe `table_name` */
    public function processDescribe() {
        $string = "";
        if ($table_name = $this->text[1]) {
            $result = DataBase::query('describe ' . $table_name);
            if (count($result['array'])) {
                foreach($result['array'] as $a) {
                    $string .= "Field: $a[0]<br>Type: $a[1]<br>Null: $a[2]<br>
                        Key: $a[3]<br>Default: $a[4]<br>Extra: $a[5]<br><br>
                    ";
                }
            }
        }
        return $string;
    }

    protected function generateString($count) {
        $alphabet = "abcdefghijklmnopqrstuwxyzABCDEFGHIJKLMNOPQRSTUWXYZ0123456789 ";
        $string = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < $count; $i++) {
            $n = rand(0, $alphaLength);
            $string[] = $alphabet[$n];
        }
        return implode($string);
    }

    protected function generateDate() {
        $now = new DateTime('now');
        $n = rand(1, $now->getTimestamp());
        return date( "Y-m-d", $n);
    }

    protected function generateDateTime() {
        $now = new DateTime('now');
        $n = rand(1, $now->getTimestamp());
        return date( "Y-m-d H:i:s", $n);
    }

    public function processSelectFrom() {
        if ($table_name = $this->text[1]) {

            $result = DataBase::query('select * from ' . $table_name);
            $table_structure = DataBase::query('describe ' . $table_name);

            $string = "";
            if (count($result['array'])) {
                foreach($result['array'] as $key => $item) {
                    $string .= "Row " . $key . ":<br>";
                    foreach($table_structure['array'] as $k => $s) {
                        $string .= $s[0] . ': ' . $item[$k] . "<br>";
                    }
                    $string .= '<br>';
                }
            }
            return $string;
        } else {
            return "";
        }
    }

    public function processClear() {
        if (count($this->text) == 1) {
            History::clearHistory();
        } else {
            throw new Exception("", UserRequest::ERROR_UNKNOWN_COMMAND);
        }

        return "complete!";
    }
}