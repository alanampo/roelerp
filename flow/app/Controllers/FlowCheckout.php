<?php
namespace App\Controllers;

use App\Libraries\Flow; // Import library
use App\Models\ClienteModel;
use App\Models\CotizacionModel;

class FlowCheckout extends BaseController
{

    public function __construct()
    {
        //parent::__construct();q
    }

    public function generar($id_cot)
    {
        try {
            //load order
            $cotizacionModel = new CotizacionModel($db);
            $clienteModel = new ClienteModel($db);

            $cotizacion = $cotizacionModel->find($id_cot);

            $cliente = $clienteModel->find($cotizacion["id_cliente"]);
            if (!isset($cliente["mail"])) {
                throw new Exception("El cliente no tiene un email asignado");
            }

            $this->flow = new Flow();
            $service = "payment/create";
            $method = "POST";

            $params = array(
                "commerceOrder" => "ROEL" . strtotime(date("Y-m-d H:i:s")),
                "subject" => "COTIZACION " . $id_cot,
                "currency" => "CLP",
                "amount" => (int) $cotizacion["monto"],
                "email" => $cliente["mail"],
                "paymentMethod" => 9,
                "urlConfirmation" => "https://roelplant.cl",
                "urlReturn" => "https://roelplant.cl",
            );

            $response = $this->flow->send($service, $params, $method);

            $insert = array(
                "flow_order" => $response["flowOrder"],
                "flow_token" => $response["token"],
                "commerce_order" => $params["commerceOrder"],
                "trx_currency" => $params["currency"],
                "trx_subject" => $params["subject"],
                "trx_amount" => $params["amount"],
                "trx_status" => $params["status"],
                "date_created" => date("Y-m-d H:i:s"),
            );

            //$new_trx = $this->payment_database->spawn_new_flow_transaction($insert);

            $destination = $response["url"] . "?token=" . $response["token"];
            //redirect()->to($destination);
            echo $destination;
        } catch (Exception $e) {
            print_r($e);
        }
    }

    public function linkfactura($folio, $monto, $email)
    {
        try {
            $this->flow = new Flow();
            $service = "payment/create";
            $method = "POST";

            $params = array(
                "commerceOrder" => "ROEL" . strtotime(date("Y-m-d H:i:s")),
                "subject" => "FACTURA " . $folio,
                "currency" => "CLP",
                "amount" => (int) $monto,
                "email" => $email,
                "paymentMethod" => 9,
                "urlConfirmation" => "https://roelplant.cl",
                "urlReturn" => "https://roelplant.cl",
            );

            $response = $this->flow->send($service, $params, $method);

            $insert = array(
                "flow_order" => $response["flowOrder"],
                "flow_token" => $response["token"],
                "commerce_order" => $params["commerceOrder"],
                "trx_currency" => $params["currency"],
                "trx_subject" => $params["subject"],
                "trx_amount" => $params["amount"],
                "trx_status" => $params["status"],
                "date_created" => date("Y-m-d H:i:s"),
            );
            //$new_trx = $this->payment_database->spawn_new_flow_transaction($insert);
            $destination = $response["url"] . "?token=" . $response["token"];
            //redirect()->to($destination);
            echo $destination;
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
