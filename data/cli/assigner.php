<?php

require_once(__DIR__ . '/MysqlConnection.php');

class Assigner extends MysqlConnection {

    private $isDevelop;

    public function __construct($isDevelop = true, $isLocal = true) {
        parent::__construct();
        $this->isDevelop = $isDevelop;
    }

    public function run() {
        $sql = "SELECT id FROM files WHERE estatus like 'Pendiente de asignar premios' ORDER BY id ASC limit 1";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            $file = $result->fetch_assoc();
            $this->assignCodes($file['id']);
            $sql = "UPDATE files SET estatus='Envío en proceso' WHERE id=" . $file['id'];
            if (!$this->isDevelop) {
                $this->mysqli->query($sql);
            }
        } else {
            echo "Sin archivos por procesar";
        }
    }

    protected function getByFileId($fileId) {
        $sql = "SELECT * FROM winners WHERE file_id =" . $fileId . " AND estatus LIKE 'Pendiente de asignar premios' ORDER BY id ASC";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            return $result;
        }
        return null;
    }

    protected function assignCodes($fileId) {
        $winners = $this->getByFileId($fileId);
        if ($winners !== null) {
            while ($winner = $winners->fetch_assoc()) {
                if ((int) $winner['winner_type'] === 1) {
                    if ($this->countByEmail($winner['winner_email']) > 4) {
                        $sql = "UPDATE winners SET estatus='Excedio codigos permitidos' WHERE id=" . $winner['id'];
                        if (!$this->isDevelop) {
                            $this->mysqli->query($sql);
                        }
                        continue;
                    }
                }
                if ((int) $winner['winner_type'] >= 1 && (int) $winner['winner_type'] <= 6) {
                    if ($this->countByEmail($winner['winner_email']) > 6) {
                        $sql = "UPDATE winners SET estatus='Excedio codigos permitidos' WHERE id=" . $winner['id'];
                        if (!$this->isDevelop) {
                            $this->mysqli->query($sql);
                        }
                        continue;
                    }
                }
                if ((int) $winner['winner_type'] > 6) {
                    $sql = "UPDATE winners SET estatus='Pendiente de envio' WHERE id=" . $winner['id'];
                    if (!$this->isDevelop) {
                        $this->mysqli->query($sql);
                    }
                    continue;
                }
                $codeResult = $this->createWinner($winner['id'], $winner['winner_type']);
                if ($codeResult !== null) {
                    $sql = "UPDATE winners SET estatus='Pendiente de envio', code='" .
                            $codeResult['code_value'] . "', expiration_date= '" .
                            $codeResult['expiration_date'] . "' WHERE id=" . $winner['id'];
                    if (!$this->isDevelop) {
                        $this->mysqli->query($sql);
                    }
                } else {
                    $codeResult = $this->getCodeByUserId($winner['id']);
                    if ($codeResult !== null) {
                        $sql = "UPDATE winners SET estatus='Pendiente de envio', code='" .
                                $codeResult['code_value'] . "', expiration_date= '" .
                                $codeResult['expiration_date'] . "' WHERE id=" . $winner['id'];
                        if (!$this->isDevelop) {
                            $this->mysqli->query($sql);
                        }
                    } else {
                        $sql = "UPDATE winners SET estatus='Error al asingar el premio', code = NULL, expiration_date = NULL WHERE id=" . $winner['id'];
                        if (!$this->isDevelop) {
                            $this->mysqli->query($sql);
                        }
                    }
                }
            }
        }
    }

    protected function countByEmail($email) {
        $sql = "SELECT count(*) AS 'total' FROM `winners` WHERE `winner_email` like('$email') AND estatus NOT IN( 'Pendiente de autorizar', 'Cancelado') AND code IS NOT NULL AND winner_type=1";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            while ($value = $result->fetch_assoc()) {
                return (int) $value['total'];
            }
        }
        return 0;
    }

    protected function createWinner($winnerId, $type) {
        $code = $this->getCode($type);
        if ($code !== null) {
            $sql = "UPDATE `codes` SET `user_id`=" . $winnerId . " WHERE `id`=" . $code['id'] . ' AND `user_id` IS NULL';
            if ($this->mysqli->query($sql)) {
                return $code;
            }
        }
        return null;
    }

    public function getCode($type) {
        $sql = "SELECT * FROM `codes` WHERE `code_type`=" . $type . " AND `user_id` IS NULL ORDER BY id ASC LIMIT 1";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            while ($value = $result->fetch_assoc()) {
                return $value;
            }
        }
        return null;
    }

    public function getCodeByUserId($userId) {
        $sql = "SELECT * FROM `codes` WHERE `user_id`=" . $userId . " ORDER BY id ASC LIMIT 1";
        $result = $this->mysqli->query($sql);
        if ($result->num_rows > 0) {
            while ($value = $result->fetch_assoc()) {
                return $value;
            }
        }
        return null;
    }

}

$assigner = new Assigner(false, false);
$assigner->run();
//$assigner->close();