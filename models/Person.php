<?php
require_once __DIR__ . '/../config.php';

class Person {
    public static function create(string $nombres, string $aPaterno, string $aMaterno)
    {
        global $pdo;
        $sql = "INSERT INTO personas (nombres, aPaterno, aMaterno) VALUES (:nombres, :aPaterno, :aMaterno)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombres' => $nombres,
            ':aPaterno' => $aPaterno,
            ':aMaterno' => $aMaterno
        ]);
        return $pdo->lastInsertId();
    }

    public static function actualizarPer(string $nombres, string $aPaterno, string $aMaterno, int $fk_persona)
    {
        global $pdo;
        $sql = "UPDATE personas 
                 SET nombres = :nombres, aPaterno = :aPaterno, aMaterno = :aMaterno 
                 WHERE pk_persona = :fk";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombres'   => $nombres,
            ':aPaterno'  => $aPaterno,
            ':aMaterno'  => $aMaterno,
            ':fk'        => $fk_persona
        ]);
         return $stmt->rowCount();
    }


}
