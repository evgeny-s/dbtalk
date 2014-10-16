<?php

class History
{
    const FILE_NAME = 'history.txt';

    public static function addHistoryData($text, $input_value = "") {
        $current = file_get_contents(self::FILE_NAME);
        $current .= '>' . $input_value . '<br>' .  $text . '<br>';
        file_put_contents(self::FILE_NAME, $current);
    }

    public static function getHistoryData() {
        return file_get_contents(self::FILE_NAME);
    }

    public static function clearHistory() {
        file_put_contents(self::FILE_NAME, "");
    }
}