<?php

namespace App\Libraries;


//if (!defined("BASEPATH")) exit("No direct script access allowed");
//die(APPPATH."ThirdParty/flow/lib/FlowApi.class.php");
require_once APPPATH."ThirdParty/flow/lib/FlowApi.class.php";

use \FlowApi;

class Flow extends FlowApi {
    public function __construct(){
        parent::__construct();
    }
}