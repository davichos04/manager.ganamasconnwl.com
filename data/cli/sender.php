<?php

require_once(__DIR__ . '/../../vendor/autoload.php');
require_once(__DIR__ . '/MysqlConnection.php');

use PHPMailer\PHPMailer\PHPMailer;

class Sender extends MysqlConnection {

    private $isDevelop;

    public function __construct($isDevelop = true, $isLocal = true) {
        parent::__construct();
        $this->isDevelop = $isDevelop;
    }

    public function run() {
        $sql = "SELECT id FROM files WHERE estatus like 'Envio en proceso' ORDER BY id asc limit 1";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            $file = $result->fetch_assoc();
            //$sql = "SELECT GROUP_CONCAT(id) AS ids, winner_name, winner_email, GROUP_CONCAT(code,';',expiration_date,';', winner_buy_date SEPARATOR '|') AS 'codes', winner_type FROM winners WHERE estatus LIKE 'Pendiente de envio' AND code IS NOT NULL AND file_id=" . $file['id'] . " GROUP BY winner_email";
            $sql = "SELECT id AS ids, winner_name, winner_email, concat(code,';',expiration_date,';', winner_buy_date) AS 'codes',
			winner_type, guide_number, carrier FROM winners WHERE estatus LIKE 'Pendiente de envio'  AND file_id=" . $file['id'];
            $result = $this->mysqli->query($sql);
            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    $this->sendMail($row);
                }
                $sql = "UPDATE files SET estatus='Envio terminado' WHERE id=" . $file['id'];
                if (!$this->isDevelop) {
                    $this->mysqli->query($sql);
                }
            } else {
                $sql = "UPDATE files SET estatus='Envio terminado' WHERE id=" . $file['id'];
                if (!$this->isDevelop) {
                    $this->mysqli->query($sql);
                }
            }
        } else {
            echo "Sin archivos por procesar";
        }
    }

    protected function sendMail($params) {
        try {

            $html = $this->getHTML($params);
            $subject = $this->getSubject($params);
            $mail = new PHPMailer(true);

            if ($this->isDevelop) {
                $credentials = $this->getAccount(0);
            } else {
                $credentials = $this->getAccount($params['winner_type']);
            }

            $file = $this->getFile($params);

            // Settings
            $mail->IsSMTP();
            $mail->CharSet = 'UTF-8';
            $mail->setFrom('hola@movistarpromociones.mx', 'Movistar Promociones');
            $mail->addAddress($params['winner_email'], $params['winner_name']);
            $mail->Host = "mail.smtp2go.com"; // SMTP server example
            $mail->SMTPDebug = false; // enables SMTP debug information (for testing)
            $mail->SMTPAuth = true; // enable SMTP authentication
            $mail->Port = 2525; // set the SMTP port for the GMAIL server
            $mail->Username = $credentials['username']; // SMTP account username example
            $mail->Password = $credentials['password']; // SMTP account password example
            $mail->addReplyTo('hola@movistarpromociones.mx', 'Movistar Promociones');
            // Content
            $mail->isHTML(true); // Set email format to HTML
            $mail->Subject = $subject;
            $mail->Body = $html;
            $mail->AltBody = $subject;
            if ($file !== null) {
                $mail->addAttachment($file);
            }
            $sql = "UPDATE winners SET delivery_date = now(), estatus = 'Error al enviar correo' WHERE id in (" . $params['ids'] . ')';
            if ($mail->send()) {
                $messageId = $mail->getSMTPInstance()->getLastTransactionID();
                $sql = "UPDATE winners SET delivery_date=now(), estatus='Enviado', opens=0, clicks=0, message_id='" . $messageId . "' WHERE id in (" . $params['ids'] . ')';
            }
            if (!$this->isDevelop) {
                $this->mysqli->query($sql);
            }
        } catch (Exception $e) {
            var_dump($e->getMessage());
        }
    }

    private function getHTML($params) {
        switch ((int) $params['winner_type']) {
            case 1:
                return $this->getHTML1($params);
            case 2:
                return $this->getHTML2($params);
            case 3:
                return $this->getHTML3($params);
            case 4:
                return $this->getHTML4($params);
            case 5:
                return $this->getHTML5($params);
            case 6:
                return $this->getHTML6($params);
            case 7:
                return $this->getHTML7();
            case 8:
                return $this->getHTML8();
            case 9:
                return $this->getHTML9();
            case 10:
                return $this->getHTML10();
            case 11:
                return $this->getHTML11();
            case 12:
                return $this->getHTML12();
            case 13:
                return $this->getHTML13();
            case 14:
                return $this->getHTML14();
            case 15:
                return $this->getHTML15();
            case 16:
                return $this->getHTML16();
            case 17:
                return $this->getHTML17();
            case 18:
                return $this->getHTML18($params);
            default:
                return $this->getHTML1($params);
        }
    }

    private function getSubject($params) {
        switch ((int) $params['winner_type']) {
            case 7:
                return 'Tu registro es incorrecto';
            case 8:
                return 'Tu registro ha sido rechazado';
            case 9:
            case 10:
            case 11:
            case 12:
            case 13:
            case 14:
            case 15:
            case 16:
            case 17:
                return 'Felicidades eres un posible ganador de La Emoción de Ganar con Movistar';
            case 18:
                return 'TU PREMIO VA EN CAMINO';
            default:
                return '¡Gracias por tu compra! Aquí tienes tu código';
        }
    }

    private function getFile($params) {
        $path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'files' . DIRECTORY_SEPARATOR);
        switch ((int) $params['winner_type']) {
            case 9:
                return $path . DIRECTORY_SEPARATOR . 'Recibo_de_Premio_para_SUCURSAL_Laptop.docx';
            case 10:
                return $path . DIRECTORY_SEPARATOR . 'Carta_Recibo_ENVIO_A_DOMICILIO_Kit_Gamer.docx';
            case 11:
                return $path . DIRECTORY_SEPARATOR . 'Recibo_de_Premio_para_SUCURSAL_Pantalla.docx';
            case 12:
                return $path . DIRECTORY_SEPARATOR . 'Carta_Recibo_Premio_ENVIO_A_DOM_Celular_Xiaomi.docx';
            case 13:
                return $path . DIRECTORY_SEPARATOR . 'Carta_Recibo_Premio_ENVIO_A_DOM_Celular_Vivo.docx';
            case 14:
                return $path . DIRECTORY_SEPARATOR . 'Carta_Recibo_Premio_ENVIO_A_DOM_Celular_Huawei.docx';
            case 15:
                return $path . DIRECTORY_SEPARATOR . 'Carta_Recibo_Premio_ENVIO_A_DOM_Celular_ZTE.docx';
            case 16:
                return $path . DIRECTORY_SEPARATOR . 'Carta_Recibo_Premio_ENVIO_A_DOM_Celular_IPhone.docx';
            default:
                return null;
        }
    }

    private function getHTML1($params) {
        $codes = explode('|', $params['codes']);
        $finalCodes = '';
        $buyDate = [];
        $it = 0;
        foreach ($codes as $code) {
            $c = explode(';', $code);
            if (stripos($c[0], ',') > 0) {
                $codigos = explode(',', $c[0]);
                $date = explode(',', $c[1]);
                $i = 0;
                foreach ($codigos as $codigo) {
                    $finalCodes .= '<tr align="center">
                            <td height="30" align="center"
                                style="font-family: ' . "Arial" . ', sans-serif; font-size:38px; color:#fff; line-height:38px; font-weight: bold;text-align: center;">
                                ' . $codigo . '
                            </td>
                        </tr>
                        <tr align="center">
                            <td height="18" align="center"
                                style="font-family: ' . "Arial" . ', sans-serif; font-size:18px; color:#fff; line-height:18px; font-weight: normal;text-align: center;">
                                Válido hasta el ' . $date[$i] . '
                            </td>
                        </tr>
                        <tr align="center">
                            <td align="center" height="40">
                                &nbsp;
                            </td>
                        </tr>';
                    $i++;
                }
            } else {
                $finalCodes .= '<tr align="center">
                            <td height="30" align="center"
                                style="font-family: ' . "Arial" . ', sans-serif; font-size:38px; color:#fff; line-height:38px; font-weight: bold;text-align: center;">
                                ' . $c[0] . '
                            </td>
                        </tr>
                        <tr align="center">
                            <td height="18" align="center"
                                style="font-family: ' . "Arial" . ', sans-serif; font-size:18px; color:#fff; line-height:18px; font-weight: normal;text-align: center;">
                                Válido hasta el ' . $c[1] . '
                            </td>
                        </tr>
                        <tr align="center">
                            <td align="center" height="40">
                                &nbsp;
                            </td>
                        </tr>';
                $it++;
            }
            array_push($buyDate, $c[2]);
        }


        $html = '<html>

<head>
	<title>Mailing Buen Fin</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>

<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
	<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
		<!-- START HEADER/BANNER -->
		<tbody>
			<tr>
				<td align="center">
					<table class="col-800" width="800" border="0" align="center" cellpadding="0" cellspacing="0">
						<tbody>
							<tr>
								<td align="center" valign="top" background="https://movistarpromociones.mx/img/BG1.jpg"
									bgcolor="#019DF4" style="background-size:cover; background-position:top;"
									height="604" width="800">
									<table class="col-800" width="800" height="604" border="0" align="center"
										cellpadding="0" cellspacing="0">
										<tbody>
											<tr>
												<td height="604" width="65" align="center">

												</td>
												<td height="604" width="496" align="center">
													<table height="604" width="496" align="center">
														<tbody>
															<tr align="center">
																<td align="center" height="70">
																	&nbsp;
																</td>
															</tr>
															<tr align="center">
																<td align="center" height="56"
																	style="font-family: ' . "Arial" . ', sans-serif; font-size:54px; color:#fff; line-height:54px; font-weight: bold;">
																	Felicidades
																</td>
															</tr>
															<tr align="center">
																<td align="center" height="60"
																	style="font-family: ' . "Arial" . ', sans-serif; font-size:54px; color:#fff; line-height:54px; font-weight: bold;">
																	' . $params['winner_name'] . '
																</td>
															</tr>
															<tr align="center">
																<td align="center" height="15">
																	&nbsp;
																</td>
															</tr>
															<tr align="center">
																<td align="center" height="60"
																	style="font-family: ' . "Arial" . ', sans-serif; font-size:27px; color:#092331; line-height:30px; font-weight: bold;">
																	Aquí tienes tu' . (($it > 1) ? 's' : '') . ' tarjeta' . (($it > 1) ? 's' : '') . '<br />de regalo digital
																</td>
															</tr>
															<tr align="center">
																<td align="center" height="10">
																</td>
															</tr>
															<tr align="center">
																<td align="center" height="152"
																	style="text-align: center;">
																	<img margin="0" padding="0" width="286" height="152"
																		src="https://movistarpromociones.mx/img/500.png" />
																</td>
															</tr>
															<tr align="center">
																<td align="center" height="10">
																</td>
															</tr>
															' . $finalCodes . '
															<tr align="center">
																<td align="center" height="30">
																	&nbsp;
																</td>
															</tr>
														</tbody>
													</table>

												</td>
												<td height="604" width="241" align="center">

												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
							<tr>
								<td align="center" valign="top" background="https://movistarpromociones.mx/img/BG2.jpg"
									bgcolor="#fff" style="background-size:cover; background-position:top;" height="595"
									width="800">
									<table class="col-800" width="800" height="595" border="0" align="center"
										cellpadding="0" cellspacing="0">
										<tbody>
											<td align="center" width="800" height="35">
											</td>
											<tr align="center">
                                            
												<td height="283" width="800" align="center" style="text-align: center;">
													<a border="0" href="https://www.amazon.com.mx/" target="_blank" style="border:0;">
														<img margin="0" padding="0" width="710" height="283"
															src="https://movistarpromociones.mx/img/CTA.png" />
													</a>
												</td>
											</tr>
											<tr align="center">
												<td align="center" width="800" height="61" style="text-align: center;">
													<a border="0" href="https://www.amazon.com.mx/" target="_blank"
														style="border:0;text-decoration: none;text-align: center;">
														<img margin="0" padding="0" width="361" height="61"
															src="https://movistarpromociones.mx/img/copy.png" />
														<p style="font-family: ' . "Arial" . ', sans-serif; font-size:14px; color:#0B2739; line-height:14px; font-weight: normal;text-align: center;">
															Realizada el ' . implode(', ', $buyDate) . '
														</p>
													</a>
												</td>
											</tr>
											<tr align="center">
												<td align="center" width="800" height="64"
													style="font-family: ' . "Arial" . ', sans-serif; font-size:14px; color:#0B2739; line-height:14px; font-weight: normal;text-align: center;">
													Vigencia de la promoción​ del 18 al 30 de noviembre de 2022 o hasta
													agotar existencias​<br />Consulta Términos y Condiciones, así como
													el Aviso de Privacidad en<br />
													<a border="0"
														href="https://www.movistar.com.mx/tyc/buen-fin/tarjeta-regalo"
														target="_blank"
														style="border:0;text-decoration: none;color:#019DF4;">
														movistar.com.mx/tyc/buen-fin/tarjeta-regalo
													</a>
												</td>
											</tr>
											<tr align="center">
												<td align="center" width="800" height="10">
												</td>
											</tr>
											<tr align="center">
												<td align="center" width="800" height="15"
													style="font-family: ' . "Arial" . ', sans-serif; font-size:14px; color:#0B2739; line-height:14px; font-weight: normal;text-align: center;">
													hola@promocionesmovistar.mx​
												</td>
											</tr>
											<tr align="center">
												<td align="center" width="800" height="40">
												</td>
											</tr>
										</tbody>
									</table>
								</td>
							</tr>
						</tbody>
					</table>
				</td>
			</tr>
		</tbody>
	</table>
</body>

</html>';
        return $html;
    }

    private function getHTML2($params) {
        $codes = explode('|', $params['codes']);
        $finalCodes = '';
        $buyDate = [];
        $it = 0;
        foreach ($codes as $code) {
            $c = explode(';', $code);
            if (stripos($c[0], ',') > 0) {
                $codigos = explode(',', $c[0]);
                $date = explode(',', $c[1]);
                $i = 0;
            }
            array_push($buyDate, $c[2]);
        }

        $html = '<html>
<head>
<title>Mailing Buen Fin</title>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
</head>
<body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
<table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
	<tbody>
		<tr>
			<td align="center">
				<table class="col-800" width="600" border="0" align="center" cellpadding="0" cellspacing="0">
					<tbody>
						<tr>
							<td align="center" valign="top" background="https://movistarpromociones.mx/img/Mailing_maqueta.jpg" bgcolor="#019DF4" style="background-size:cover; background-position:top;" height="904" width="600">
								<table class="col-800" width="600" height="904" border="0" align="center" cellpadding="0" cellspacing="0">
									<tbody>
										<tr>
											<td height="904" width="12" align="center">
												
											</td>
											<td height="904" width="516" align="center">
												<table height="904" width="516" align="center">
													<tbody>
														<tr align="center">
															<td width="131" height="64" align="center">&nbsp;
																
															</td>
															<td width="139" align="center">&nbsp;</td>
															<td width="230" align="left">
																<p style="font-family: Verdana,Arial, Helvetica, sans-serif;font-size: 24px;color: #fff;">' . $params['winner_name'] . '</p>
															</td>
														</tr>
														<tr align="center">
															<td height="36" colspan="3" align="center" style="font-family: "Arial", sans-serif; font-size:54px; color:#fff; line-height:54px; font-weight: bold;">&nbsp;</td>
														</tr>
														<tr align="center">
															<td height="130" colspan="3" align="center" style="font-family: "Arial", sans-serif;  color:#fff; line-height:30px; font-weight: bold;"><br><br><br>
															<p style="font-size: 18px;color: #020202;font-weight: bold;line-height: 22px;">Códigos de promoción:<br> ' . implode(' y ', $codigos) . '
																<br><span style="font-size: 12px;font-weight: 400;">Vigencia al ' . $date[0] . '</span>
															</p><br></td>
														</tr>
														<tr align="center">
															<td height="35" colspan="3" align="center">
																<a href="https://cinepolis.com/" style="text-decoration: none !important; font-size: 30px;" target="_blank">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
																
															</td>
														</tr>
														<tr align="center">
															<td height="10" colspan="3" align="center" style="font-family: "Arial", sans-serif; font-size:27px; color:#092331; line-height:30px; font-weight: bold;">
															<a href="https://cinepolis.com/" style="text-decoration: none !important;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a></td>
														</tr>
														<tr align="center">
															<td height="10" colspan="3" align="center">
															</td>
														</tr>
														<tr align="center">
															<td height="102" colspan="3" align="center" style="text-align: center;">&nbsp;</td>
														</tr>
														<tr align="center">
															<td height="10" colspan="3" align="center">
															</td>
														</tr>
														<tr align="center">
                                                                                                                    <td height="70" colspan="3" align="center" style="text-align: center;">
                                                                                                                        <p style="margin-left:70px;font-family: ' . 'Arial' . ', sans-serif; font-size:18px; line-height:18px; font-weight: normal;text-align: center;font-weight: bold;"><br><br><br><br>Realizada el ' . implode(' y ', $buyDate) . '</p>
                                                                                                                    </td>
                                                                                                                </tr>
														<tr align="center">
															<td height="50" colspan="3" align="center">&nbsp;
																
															</td>
														</tr>
														<tr align="center">
															<td height="18" colspan="3" align="center" style="font-family: "Arial", sans-serif; font-size:18px; color:#fff; line-height:18px; font-weight: normal;text-align: center;">
																
															</td>
														</tr>
														<tr align="center">
															<td height="30" colspan="3" align="center">&nbsp;
																
															</td>
														</tr>
													</tbody>
												</table>
												
										  </td>
											<td height="904" width="72" align="center">
												
											</td>
										</tr>
									</tbody>
								</table>
							</td>
						</tr>
						
					</tbody>
				</table>
			</td>
		</tr>
	</tbody>
</table>
</body>
</html>';
        return $html;
    }

    private function getHTML3($params) {
        $codes = explode('|', $params['codes']);
        $finalCodes = $vigencia = $buyDate = [];
        $it = 0;
        foreach ($codes as $code) {
            $c = explode(';', $code);
            array_push($finalCodes, $c[0]);
            array_push($vigencia, $c[1]);
            array_push($buyDate, $c[2]);
        }

        $html = '<html>
    <head>
        <title>Mailing</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            	<tbody>
                <tr>
                    <td align="center">
                        <table class="col-800" width="600" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td align="center" valign="top" background="https://movistarpromociones.mx/img/tregalo.jpg" bgcolor="#019DF4" style="background-size:cover; background-position:top;" height="910" width="600">
                                        <table class="col-800" width="600" height="910" border="0" align="center" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td height="910" width="12" align="center">

                                                    </td>
                                                    <td height="904" width="516" align="center">
                                                        <table height="904" width="516" align="center">
                                                            <tbody>
                                                                <tr align="center">
                                                                    <td width="131" height="10" align="center">&nbsp;

                                                                    </td>
                                                                    <td width="139" align="center">&nbsp;</td>
                                                                    <td width="230" align="left">
                                                                        <p style="font-family: Verdana,Arial, Helvetica, sans-serif;font-size: 24px;color: #fff;">
                                                                            ' . $params['winner_name'] . '
                                                                        </p>
                                                                        <br>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="36" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:54px; color:#fff; line-height:54px; font-weight: bold;">&nbsp;</td>
                                                                </tr>
                                                                <tr align="center">
                                                                    
                                                                    <td height="100" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif;  color:#fff; line-height:30px; font-weight: bold;">
                                                                        <br><br><br>
                                                                        <p style="font-size: 18px;color: #020202;font-weight: bold;line-height: 22px;">Código de promoción: ' . implode(' y ', $finalCodes) . '
                                                                            <br><span style="font-size: 15px;font-weight: 400;">Vigencia al ' . implode(' y ', $vigencia) . '</span>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="35" colspan="3" align="center">&nbsp;
                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:14px; color:#092331; line-height:30px; font-weight: bold;">
                                                                        &nbsp;
                                                                        <br><br>
                                                                        <a href="https://www.pinkrevolver.com.mx/" style="margin-left: 70px; font-family: Verdana,Arial, Helvetica, sans-serif;border-radius: 32px; background-color: #5EB614; color: #fff;padding: 8px 32px;text-decoration: none;" alt="Da clic aquí">Da clic aquí</a>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="40" colspan="3" align="center" style="text-align: center;">
                                                                        <a href="#" style="text-decoration: none;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;">
                                                                        <p style="margin-left: 40px;">&nbsp; </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="70" colspan="3" align="center" style="text-align: center;">
                                                                        
                                                                        <p style="margin-left:70px;font-family: ' . 'Arial' . ', sans-serif; font-size:18px; line-height:18px; font-weight: normal;text-align: center;font-weight: bold;"><br><br><br><br><br><br><br>Realizada el ' . implode(' y ', $buyDate) . '</p>

                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;"></td>
                                                                </tr>

                                                                <tr align="center">
                                                                    <td height="30" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:22px; line-height:20px; font-weight: bold;text-align: center;">
                                                                        <p> </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="40" colspan="3" align="center" >


                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                            <br><br>
                                                            <td height="18" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:18px; color:#fff; line-height:18px; font-weight: normal;text-align: center;">

                                                            </td>
                                                </tr>
                                                <tr align="center">
                                                    <td height="30" colspan="3" align="center">&nbsp;

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                    <td height="904" width="72" align="center">

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

            </tbody>
        </table>
    </td>
</tr>
</tbody>
</table>
</body>
</html>';
        return $html;
    }

    private function getHTML4($params) {
        $codes = explode('|', $params['codes']);
        $finalCodes = $vigencia = $buyDate = [];
        $it = 0;
        foreach ($codes as $code) {
            $c = explode(';', $code);
            array_push($finalCodes, $c[0]);
            array_push($vigencia, $c[1]);
            array_push($buyDate, $c[2]);
        }

        $html = '<html>
    <head>
        <title>Mailing</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td align="center">
                        <table class="col-800" width="600" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td align="center" valign="top" background="https://movistarpromociones.mx/img/m1.jpg" bgcolor="#019DF4" style="background-size:cover; background-position:top;" height="904" width="600">
                                        <table class="col-800" width="600" height="904" border="0" align="center" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td height="904" width="12" align="center">

                                                    </td>
                                                    <td height="904" width="516" align="center">
                                                        <table height="904" width="516" align="center">
                                                            <tbody>
                                                                <tr align="center">
                                                                    <td width="131" height="10" align="center">&nbsp;

                                                                    </td>
                                                                    <td width="139" align="center">&nbsp;</td>
                                                                    <td width="230" align="left">
                                                                        <p style="font-family: Verdana,Arial, Helvetica, sans-serif;font-size: 24px;color: #fff;">' . $params['winner_name'] . '</p>
                                                                        <br>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="56" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:54px; color:#fff; line-height:54px; font-weight: bold;">&nbsp;</td>
                                                                </tr>
                                                                <tr align="center">

                                                                    <td height="100" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif;  color:#fff; line-height:30px; font-weight: bold;">
                                                                        <br><br>
                                                                        <p style="font-size: 18px;color: #020202;font-weight: bold;line-height: 22px;">Código de promoción: ' . implode(' y ', $finalCodes) . '
                                                                            <br><span style="font-size: 15px;font-weight: 400;">Vigencia al ' . implode(' y ', $vigencia) . '</span>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="35" colspan="3" align="center">&nbsp;
                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:14px; color:#092331; line-height:30px; font-weight: bold;">
                                                                        &nbsp;
                                                                        <a href="https://www.nuevo.dominos.com.mx/" style="margin-left: 70px; font-family: Verdana,Arial, Helvetica, sans-serif;border-radius: 32px; background-color: #5EB614; color: #fff;padding: 8px 32px;text-decoration: none;" alt="Da clic aquí">Da clic aquí</a>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="40" colspan="3" align="center" style="text-align: center;">
                                                                        <a href="#" style="text-decoration: none;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;">
                                                                        <p style="margin-left: 40px;"> </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;"><br><br><br>
                                                                        <p style="margin-left:70px;font-family: ' . 'Arial' . ', sans-serif; font-size:18px; line-height:18px; font-weight: normal;text-align: center;font-weight: bold;">Realizada el ' . implode(', ', $buyDate) . '</p>

                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="30" colspan="3" align="center" style="text-align: center;"></td>
                                                                </tr>

                                                                <tr align="center">
                                                                    <td height="30" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:22px; line-height:20px; font-weight: bold;text-align: center;">
                                                                        <p> </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="40" colspan="3" align="center" >


                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                            <br><br>
                                                            <td height="18" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:18px; color:#fff; line-height:18px; font-weight: normal;text-align: center;">

                                                            </td>
                                                </tr>
                                                <tr align="center">
                                                    <td height="30" colspan="3" align="center">&nbsp;

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                    <td height="904" width="72" align="center">

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

            </tbody>
        </table>
    </td>
</tr>
</tbody>
</table>
</body>
</html>';
        return $html;
    }

    private function getHTML5($params) {
        $codes = explode('|', $params['codes']);
        $finalCodes = $vigencia = $buyDate = [];
        $it = 0;
        foreach ($codes as $code) {
            $c = explode(';', $code);
            array_push($finalCodes, $c[0]);
            array_push($vigencia, $c[1]);
            array_push($buyDate, $c[2]);
        }

        $html = '<html>
    <head>
        <title>Mailing</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td align="center">
                        <table class="col-800" width="600" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td align="center" valign="top" background="https://movistarpromociones.mx/img/MailingDonas_maqueta.jpg" bgcolor="#019DF4" style="background-size:cover; background-position:top;" height="904" width="600">
                                        <table class="col-800" width="600" height="904" border="0" align="center" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td height="904" width="12" align="center">

                                                    </td>
                                                    <td height="904" width="516" align="center">
                                                        <table height="800" width="516" align="center" >
                                                            <tbody>
                                                                <tr align="center">
                                                                    <td width="131" height="10" align="center">&nbsp;

                                                                    </td>
                                                                    <td width="139" align="center">&nbsp;</td>
                                                                    <td width="230" align="left"><br>
                                                                        <p style="font-family: Verdana,Arial, Helvetica, sans-serif;font-size: 24px;color: #fff;">' . $params['winner_name'] . '</p>
                                                                        <br>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="36" colspan="3" align="center" style="font-family: \'Arial\', sans-serif; font-size:54px; color:#fff; line-height:54px; font-weight: bold;">&nbsp;</td>
                                                                </tr>
                                                                <tr align="center">

                                                                    <td height="100" colspan="3" align="center" style="font-family: \'Arial\', sans-serif;  color:#fff; line-height:30px; font-weight: bold;">
                                                                        <br><br><br>
                                                                        <p style="font-size: 20px;color: #020202;font-weight: bold;line-height: 22px;">Código de promoción: ' . implode(' y ', $finalCodes) . '
                                                                            <br><span style="font-size: 16px;font-weight: 400;">Vigencia al ' . $vigencia[0] . '</span>
                                                                        </p><br></td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="35" colspan="3" align="center">&nbsp;

                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="10" colspan="3" align="center" style="font-family: \'Arial\', sans-serif; font-size:27px; color:#092331; line-height:30px; font-weight: bold;">

                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="10" colspan="3" align="center">
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;">

                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;"></td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;">
                                                                        <p style="margin-left: 40px;"> </p>
                                                                    </td>
                                                                </tr>
                                                                
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;"><br><br><br><br><br><br><br><br><br><br><br><br>
                                                                        <p style="font-family: \'Arial\', sans-serif; font-size:18px; line-height:18px; font-weight: normal;text-align: center;">Realizada el ' . implode(' y ', $buyDate) . '</p>

                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="102" colspan="3" align="center" style="text-align: center;"></td>
                                                                </tr>

                                                                <tr align="center">
                                                                    <td height="30" colspan="3" align="center" style="font-family: \'Arial\', sans-serif; font-size:22px; line-height:20px; font-weight: bold;text-align: center;">
                                                                        <p> </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="40" colspan="3" align="center" >


                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                            <br><br>
                                                            <td height="18" colspan="3" align="center" style="font-family: \'Arial\', sans-serif; font-size:18px; color:#fff; line-height:18px; font-weight: normal;text-align: center;">

                                                            </td>
                                                </tr>
                                                <tr align="center">
                                                    <td height="30" colspan="3" align="center">&nbsp;

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                    <td height="904" width="72" align="center">

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

            </tbody>
        </table>
    </td>
</tr>
</tbody>
</table>
</body>
</html>';
        return $html;
    }

    private function getHTML6($params) {
        $codes = explode('|', $params['codes']);
        $finalCodes = $vigencia = $buyDate = [];
        $it = 0;
        foreach ($codes as $code) {
            $c = explode(';', $code);
            array_push($finalCodes, 'MSR2');
            array_push($vigencia, '28/feb/2023');
            array_push($buyDate, $c[2]);
        }

        $html = '<html>
    <head>
        <title>Mailing</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    </head>
    <body bgcolor="#FFFFFF" leftmargin="0" topmargin="0" marginwidth="0" marginheight="0">
        <table width="100%" border="0" align="center" cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <td align="center">
                        <table class="col-800" width="650" border="0" align="center" cellpadding="0" cellspacing="0">
                            <tbody>
                                <tr>
                                    <td align="center" valign="top" background="https://movistarpromociones.mx/img/m2.jpg" bgcolor="#019DF4" style="background-size:contain;background-repeat:no-repeat; background-position:top;" height="904" width="600">
                                        <table class="col-800" width="600" height="1100" border="0" align="center" cellpadding="0" cellspacing="0">
                                            <tbody>
                                                <tr>
                                                    <td height="1100" width="12" align="center">

                                                    </td>
                                                    <td height="1100" width="516" align="center">
                                                        <table height="1100" width="516" align="center">
                                                            <tbody>
                                                                <tr align="center">
                                                                    <td width="131" height="1" align="center">&nbsp;

                                                                    </td>
                                                                    <td width="110" align="center">&nbsp;</td>
                                                                    <td width="230" align="left">
                                                                        <p style="font-family: Verdana,Arial, Helvetica, sans-serif;font-size: 24px;color: #fff">' . $params['winner_name'] . '</p>
                                                                        <br>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="110" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:54px; color:#fff; line-height:54px; font-weight: bold;">&nbsp;</td>
                                                                </tr>
                                                                <tr align="center">

                                                                    <td height="100" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif;  color:#fff; line-height:30px; font-weight: bold;">
                                                                        <br>
                                                                        <p style="font-size: 18px;color: #020202;font-weight: bold;line-height: 22px;">Código de promoción: ' . implode(' y ', $finalCodes) . '
                                                                            <br><span style="font-size: 15px;font-weight: 400;">Vigencia al ' . implode(' y ', $vigencia) . '</span>
                                                                        </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="35" colspan="3" align="center">&nbsp;
                                                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:14px; color:#092331; line-height:30px; font-weight: bold;">
                                                                        &nbsp;
                                                                        <a href="https://www.nuevo.dominos.com.mx/" style="margin-left: 70px; font-family: Verdana,Arial, Helvetica, sans-serif;border-radius: 32px; background-color: #5EB614; color: #fff;padding: 8px 32px;text-decoration: none;" alt="Da clic aquí">Da clic aquí</a>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;">
                                                                        <a href="#" style="text-decoration: none;">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</a>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;">
                                                                        <p style="margin-left: 40px;"> </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="50" colspan="3" align="center" style="text-align: center;"><br><br><br>
                                                                        <p style="margin-left:70px;font-family: ' . 'Arial' . ', sans-serif; font-size:18px; line-height:18px; font-weight: normal;text-align: center;font-weight: bold;">Realizada el ' . implode(', ', $buyDate) . '</p>

                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="0" colspan="3" align="center" style="text-align: center;"></td>
                                                                </tr>

                                                                <tr align="center">
                                                                    <td height="180" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:22px; line-height:20px; font-weight: bold;text-align: center;">
                                                                        <p> </p>
                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                                    <td height="40" colspan="3" align="center" >


                                                                    </td>
                                                                </tr>
                                                                <tr align="center">
                                                            <br><br>
                                                            <td height="18" colspan="3" align="center" style="font-family: ' . 'Arial' . ', sans-serif; font-size:18px; color:#fff; line-height:18px; font-weight: normal;text-align: center;">

                                                            </td>
                                                </tr>
                                                <tr align="center">
                                                    <td height="30" colspan="3" align="center">&nbsp;

                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                    <td height="904" width="80" align="center">

                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>

            </tbody>
        </table>
    </td>
</tr>
</tbody>
</table>
</body>
</html>';
        return $html;
    }

    private function getHTML7() {

        return '<h4>¡Ups! Tu registro NO se realizó correctamente.</h4>
				<p>Si cuentas con una línea Movistar, debes registrar su número.</p>
				<p>¡No lo olvides! El ticket solo sirve como evidencia si compraste un smartphone LIBERADO.</p>
				<p>Si tienes dudas, consulta nuestros Términos y Condiciones en <a href="promocionesmovistar.mx">promocionesmovistar.mx</a> y regresa para hacer tu registro nuevamente</p>
		';
    }

    private function getHTML8() {
        return '<h4>¡Ups! Tu imagen de ticket NO es correcta. Quizá se debe a alguno de los siguientes motivos:</h4>
					<p>
						<ol>
							<li>La imagen no corresponde a un ticket de compra.</li>
							<li>La imagen no está completa y no se visualizan los elementos necesarios para hacer la validación.
							<li>No es legible.</li>
							<li>No cumple con la fecha de la vigencia de la promoción.</li>
							<li>No cumple con el mínimo de compra establecido o la compra no se hizo en un establecimiento participante.</li>
							<li>El producto que compraste no participa en la promoción</li>
							<li>Ya fue registrado.</li>
						</ol>
					</p>
					<p>
						Debes ingresar un comprobante de compra válido de acuerdo a los Términos y Condiciones de la promoción. No olvides que, registrar información falsa es motivo para darte de baja de la promoción y no podrás seguir participando. 
					</p>
					<p>
						Para más información, ingresa a <a href="promocionesmovistar.mx">promocionesmovistar.mx</a>
					</p>';
    }

    private function getHTML9() {
        return '<p>
	¡Hola! Nos complace informarte que eres un posible ganador de una Laptop Gaming Lenovo para poder ser declarado como un ganador oficial y con base en nuestros términos y condiciones de la promoción. Es importante que realices lo siguiente:
</p>
<ol>
	<li>
		<strong>Descargar, imprimir y llenar la carta adjunta</strong>.
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y  firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML10() {
        return '<p>
	¡Hola! Nos complace informarte que eres un posible ganador de una Kit Gamer que incluye una silla Vorago, audifonos Redlemon y un monitor LCD Samsung. Para poder ser declarado como un ganador oficial  y con base en nuestros términos y condiciones de la promoción, es importante que realices lo siguiente:
</p>
<ol>
	<li>
		<strong>Descargar, imprimir y llenar la carta adjunta</strong>.
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y  firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML11() {
        return '<p>
¡Hola! Nos complace informarte que eres un posible ganador de una Pantalla Samsung de 50 Pulg para poder ser declarado como un ganador oficial  y con base en nuestros términos y condiciones de la promoción. Es importante que realices lo siguiente:	
</p>
<ol>
	<li>
		<strong>Descargar, imprimir y llenar la carta adjunta</strong>.
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y  firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML12() {
        return '<p>
¡Hola! Nos complace informarte que eres un posible ganador de un Celular Xiaomi Redmi 10A 64+3GB para poder ser declarado como un ganador oficial  y con base en nuestros términos y condiciones de la promoción. Es importante que realices lo siguiente:	
</p>
<ol>
	<li>
		<strong>Descargar, imprimir y llenar las cartas adjuntas.</strong>
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML13() {
        return '<p>
¡Hola! Nos complace informarte que eres un posible ganador de un Celular Vivo Y01 para poder ser declarado como un ganador oficial  y con base en nuestros términos y condiciones de la promoción. Es importante que realices lo siguiente:	
</p>
<ol>
	<li>
		<strong>Descargar, imprimir y llenar las cartas adjuntas</strong>.
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y  firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML14() {
        return '<p>
            ¡Hola! Nos complace informarte que eres un posible ganador de un Celular Huawei Nova Y70  para poder ser declarado como un ganador oficial  y con base en nuestros términos y condiciones de la promoción. Es importante que realices lo siguiente:
	</p>
    <ol>
	<li>
		<strong>Descargar, imprimir y llenar las cartas adjuntas</strong>.
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y  firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML15() {
        return '<p>
	¡Hola! Nos complace informarte que eres un posible ganador de un Celular ZTE Blade A51  para poder ser declarado como un ganador oficial  y con base en nuestros términos y condiciones de la promoción. Es importante que realices lo siguiente:
    </p>
<ol>
	<li>
		<strong>Descargar, imprimir y llenar las cartas adjuntas</strong>.
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y  firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML16() {
        return '<p>
	¡Hola! Nos complace informarte que eres un posible ganador de un iphone SE 2020 128GB  para poder ser declarado como un ganador oficial  y con base en nuestros términos y condiciones de la promoción. Es importante que realices lo siguiente:</p>
<ol>
	<li>
		<strong>Descargar, imprimir y llenar las cartas adjuntas</strong>.
	</li>
	<li>
		Llenarla con los datos que se solicitan en la misma (todos los espacios en blanco y/o con línea).
	</li>
	<li>
		Colocar tu nombre completo y firma tal como aparece en tu INE.
	</li>
	<li>
		Enviar las cartas adjuntas en respuesta a este correo. IMPORTANTE: Revisa que todas las hojas que se te mandan esten completas y  firmadas si falta alguna hoja de los documentos que se te enviaron, no se te podrá entregar premio).
	</li>
	<li>	
		Enviar adjunto en respuesta a este correo una <strong>fotografía o escan de tu INE por ambos lados</strong>.
	</li>
	<li>
		Enviar adjunto en respuesta a este correo un comprobante de domicilio, <strong>éste debe coincidir con el domicilio que indicas en la carta</strong>.
	</li>
</ol>
<p>
    Estos documentos los deberás enviar en los próximos <strong>3 días naturales</strong> a esta misma dirección de correo electrónico.
</p>
<p>
    <strong>En caso de no enviarlos en el lapso indicado se entenderá que has rechazado tu premio y se procederá a reasignarlo al siguiente participante en lista.</strong> Para conocer más te invitamos a leer nuestros términos y condiciones en <a href="https://promocionesmovistar.mx" target="_BLANK">promocionesmovistar.mx</a>
</p>';
    }

    private function getHTML17() {
        return '<p>
	¡Hola!</p><br>
<p>
Te informamos que hemos recibido tu documentación en tiempo y forma.</p>
<p>
Pronto estaremos comunicándonos de nuevo contigo por este medio para enterarte de la fecha y horario en el que deberás asistir a recoger tu premio a nuestros centros de atención Movistar. ¡Estate muy atento a tu correo y mantenlo limpio para que te pueda llegar nuestro mensaje!. Recuerda también revisar tu bandeja de No deseados.
</p>
<p>
    <strong>¡Muchísimas Felicidades y esperamos verte pronto!
</strong>
</p><br>
<p>Equipo de Soporte.</p>';
    }

    private function getHTML18($params) {
        return '<p>
	¡Hola ' . $params['winner_name'] . '!</p><br>
<p>
Te informamos que hemos recibido tu documentación en tiempo y forma.</p>
<p>
Te pasamos tu número de guía con el que va tu premio a la dirección indicada en tu carta de recibo, para que puedas dar seguimiento puntual del mismo:
</p>
<p><br>
    <strong>GUÍA</strong>: ' . $params['guide_number'] . '<br>
    <strong>PAQUETERÍA</strong>: ' . $params['carrier'] . '
</p>
<p>
    <strong>
        ¡Compártenos tu alegría!
    </strong>
</p><br>
<p>
    Una vez que tengas tu premio estará increíble que nos puedas compartir una foto con tu premio.
</p>
<p>
Te recordamos revisar nuestros términos y condiciones aplicables a la entrega de los premios en <a haref="https://www.promocionesmovistar.mx">www.promocionesmovistar.mx</a>
</p>';
    }

    public function close() {
        $this->mysqli->close();
    }

}

$sender = new Sender(false, false);
$sender->run();
