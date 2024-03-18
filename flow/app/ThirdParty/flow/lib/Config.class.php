<?php
class Config {
    static function get($name){
        global $COMMERCE_CONFIG;

        switch(ENVIRONMENT){
            case 'production':
                $COMMERCE_CONFIG = array(
                    "APIKEY" => "29FCFEE8-AB36-4F72-9969-5B4CEL225C9E",
                    "SECRETKEY" => "5602ea935de56f485a55c6b29864a396955b41bd",
                    "APIURL" => "https://www.flow.cl/api",
                    "BASEURL" => "https://www.example.com/flow"
                );
            break;

            default:
                $COMMERCE_CONFIG = array(
                    "APIKEY" => "29FCFEE8-AB36-4F72-9969-5B4CEL225C9E",
                    "SECRETKEY" => "5602ea935de56f485a55c6b29864a396955b41bd",
                    "APIURL" => "https://sandbox.flow.cl/api",
                    "BASEURL" => "https://www.example.com/flow"
                );
        }

        if (!isset($COMMERCE_CONFIG[$name])){
            throw new Exception("The configuration element ".$name." doesnt exist in ".ENVIRONMENT, 1);
        }

        return $COMMERCE_CONFIG[$name];
    }
}
