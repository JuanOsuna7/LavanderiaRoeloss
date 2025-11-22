<?php
require_once __DIR__ . '/../config.php';

class Prenda {
    public static function createPrenda(string $tRopa, float $costoRopa, ?string $descripcion = null)
    {
        global $pdo;
        $sql = "INSERT INTO tipos_prenda (nombre_tipo, precio_por_kg, descripcion, estatus)
                VALUES (:nombreP, :costoP, :descripcion, :estatusP)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombreP' => $tRopa,
            ':costoP' => $costoRopa,
            ':descripcion' => $descripcion,
            ':estatusP' => 1
        ]);
        return $pdo->lastInsertId();
    }
    
    public static function listaPrendas()
    {
        global $pdo;
        $sql = "SELECT pk_tipo_prenda, nombre_tipo, precio_por_kg, descripcion, estatus FROM tipos_prenda ORDER BY nombre_tipo ASC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function actualizarPrenda(string $tRopa, float $costoRopa, int $fk_prenda, ?string $descripcion = null)
    {
        global $pdo;
        $sql = "UPDATE tipos_prenda 
                 SET nombre_tipo = :nombreP, precio_por_kg = :costoP, descripcion = :descripcion 
                 WHERE pk_tipo_prenda = :fk";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':nombreP' => $tRopa,
            ':costoP' => $costoRopa,
            ':descripcion' => $descripcion,
            ':fk'        => $fk_prenda
        ]);
         return $stmt->rowCount();
    }


}
