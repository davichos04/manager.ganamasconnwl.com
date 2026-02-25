<?php

die('0');
require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/MysqlConnection.php');

class SenderErewards extends MysqlConnection {

    private $isDevelop;

    public function __construct($isDevelop = true, $isLocal = true) {
        parent::__construct();
        $this->isDevelop = $isDevelop;
    }

    public function run() {
        $sql = "SELECT id, body_request FROM j6bp1_store_erewards WHERE is_verified = 1 ORDER BY id asc limit 1";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            //$sql = "SELECT GROUP_CONCAT(id) AS ids, winner_name, winner_email, GROUP_CONCAT(code,';',expiration_date,';', winner_buy_date SEPARATOR '|') AS 'codes', winner_type FROM winners WHERE estatus LIKE 'Pendiente de envio' AND code IS NOT NULL AND file_id=" . $file['id'] . " GROUP BY winner_email";

            $url = 'https://prod-apierewards.adventa.solutions';
            $token = 'eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiJ9.eyJhdWQiOiIxIiwianRpIjoiZjUyMzA0YTJmY2VmYjY4OWM0ZjA4ZGI3MjU0YTI3NDQyZGM1Njg4MmJmM2U1YWVlNzJmMzhhM2Q5OGNmM2E0NzZkNWE0MjFkZmFmOWM0MjkiLCJpYXQiOjE2ODAwNzIzMzYsIm5iZiI6MTY4MDA3MjMzNiwiZXhwIjoxNzExNjk0NzM2LCJzdWIiOiI3NCIsInNjb3BlcyI6W119.I29aRYlZ5IMsQElPURYOPmrvYH4iNdy61778-vRmrL5nGQlfY6kC7b6L37zMU_ZgbQnTNOE-77caVWQ-SYhU4y7JBiUm-_gc_OakPyW2a-610J4X2vW3KhLD6IOjhgmJ5YMK1n6AmJco1tH0AVSXnkLunLlsUWEcydKKYS_WNy8kBHokIuXhV2pdp01vYjShZAyHFEd-pwr2OxF4PNEz_Hc7NvXW_P2o52Jpiw6himV3jA_PAhhtvsp8pO4BY77OZ2rMdQD2etPop7sysmNBBhKsGtcQQneTnhoOb6JlX-aWLXX6Q9jJUNgivkhSBvDuQdzvOnJj82Bw26LioFFsxrE3p6qku9Dy9zcSLZPD2WY0uwwf1UxByYbIYCZoC45M1oRDPnbfQSL-O0vVnh3RmC0oqAb1xwnaujArKwS5A35TLmeqXEkAc0sHiYE7MxeHeWhrve9F3otkQ-elUBCu1GKUE723px6KUzclGujFLF9MjlW-VPPHaDTI64DyP9uhIS6xEjj6s-a69eZnpJVqGMUcFsrl70CMJqiWB-LlcJZ7hcgSm7KU4-_BCIYEP182FdpLLy6QJoyWD4gCK_ywHIxC9nDWOte7-DDFmaiwLZQ82qWrNj0f1NUMWu7QpTIephSJYmhgEvQrAy-egXM67-1l0k0RxryvIDvxDDA9Lk0';
            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'POST',
                CURLOPT_POSTFIELDS => $row['body_request'],
                CURLOPT_HTTPHEADER => array("Authorization: Bearer " . $token, 'Content-Type: application/json'),
            ));
            curl_setopt($curl, CURLOPT_FAILONERROR, true); // Required for HTTP error codes to be reported via our call to curl_error($ch)
            curl_setopt($curl, CURLOPT_VERBOSE, true);
            curl_setopt($curl, CURLOPT_USERAGENT, 'Mozilla/4.0 (compatible; MSIE 5.01; Windows NT 5.0)');
            $response = curl_exec($curl);
            $handle = fopen($row['id'] . '.txt', 'a+');
            fwrite($handle, $response . chr(13));
            fclose($handle);
            $sql = 'UPDATE j6bp1_store_erewards SET body_reponse = ' . $response . ' is_verified = 2 WHERE id = ' . $row['id'];
            $this->mysqli->query($sql);
        }
    }

}

$sender = new SenderErewards(false, false);
$sender->run();
