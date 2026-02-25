<?php

/*
 * Click nbfs://nbhost/SystemFileSystem/Templates/Licenses/license-default.txt to change this license
 * Click nbfs://nbhost/SystemFileSystem/Templates/Scripting/PHPClass.php to edit this template
 */

/**
 * Description of MysqlConnection
 *
 * @author davidgomez
 */
class MysqlConnection {

    protected $mysqli;

    public function __construct() {
//        $user = 'u53r_BD_pR0d';
//	$password = '&x0Mo0G2i.5-8.w@T0=i!^@Bs$8T6DmgXDcIy3c';
//	$db = 'V3rd3_v4ll3_pR0d';
//        $this->mysqli = new mysqli("localhost", $user, $password, $db);
        if ($this->mysqli->connect_error) {
            die('Error de conexión a la BD');
        }
    }
    

}
