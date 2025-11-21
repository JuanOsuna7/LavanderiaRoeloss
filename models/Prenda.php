<?php
require_once __DIR__ . '/../config.php';

class Prenda {
    public static function createPrenda(string $tRopa, float $costoRopa)
    {
        global $pdo;
        $sql = "INSERT INTO prendas (nombrePrenda, costoPrenda, estatusPrenda)
                VALUES (:nombreP, :costoP, :estatusP)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombreP' => $tRopa,
            ':costoP' => $costoRopa,
            ':estatusP' => 1
        ]);
        return $pdo->lastInsertId();
    }
    
    public static function listaPrendas()
    {
        global $pdo;
        $sql = "SELECT * FROM prendas";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function actualizarPrenda(string $tRopa, float $costoRopa, int $fk_prenda)
    {
        global $pdo;
        $sql = "UPDATE prendas 
                 SET nombrePrenda = :nombreP, costoPrenda = :costoP 
                 WHERE pk_prenda = :fk";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombreP' => $tRopa,
            ':costoP' => $costoRopa,
            ':fk'        => $fk_prenda
        ]);
         return $stmt->rowCount();
    }


}
