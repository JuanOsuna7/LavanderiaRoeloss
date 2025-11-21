<?php
require_once __DIR__ . '/../config.php';

class Cliente {
    public static function crearCliente($telefono, $direccion, $id_persona)
    {
        global $pdo;
        // Insertar cliente (estatusCli = 'Activo')
        $sql = "INSERT INTO clientes (telefono, direccion, estatusCli, fk_persona)
                    VALUES (:telefono, :direccion, 1, :fk_persona)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':telefono' => $telefono,
            ':direccion' => $direccion,
            ':fk_persona' => $id_persona
        ]);
        return $pdo->lastInsertId();
    }

     public static function listaCli()
    {
        global $pdo;
        $sql = "SELECT 
                c.pk_cliente AS id_cliente,
                p.nombres,
                p.aPaterno,
                p.aMaterno, 
                c.telefono,
                c.estatusCli,
                DATE_FORMAT(cast(cast(c.pk_cliente as unsigned) as datetime), '%d/%m/%Y %H:%i') as fecha_registro
            FROM clientes c
            INNER JOIN personas p ON c.fk_persona = p.pk_persona
            ORDER BY c.pk_cliente DESC
            LIMIT 5";
        $stmt = $pdo->query($sql);
        $clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $clientes;
    }

    public static function actualizarCli(string $telefono, string $direccion, int $fk_cliente)
    {
        global $pdo;
        $sql = "UPDATE clientes SET telefono = :telefono, direccion = :direccion 
                 WHERE pk_cliente = :fk";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':telefono'   => $telefono,
            ':direccion'  => $direccion,
            ':fk'        => $fk_cliente
        ]);
         return $stmt->rowCount();
    }


}
