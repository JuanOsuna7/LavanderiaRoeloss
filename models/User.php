<?php
require_once __DIR__ . '/../config.php';

class User {
    public static function create(string $correo, string $passwordHash, int $fk_persona)
    {
        global $pdo;
        $sql = "INSERT INTO usuarios (correoUsu, contrasUsu, rolUsu, estatusUsu, fk_persona)
                VALUES (:correo, :pass, 1, 1, :fk_persona)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':correo' => $correo,
            ':pass' => $passwordHash,
            ':fk_persona' => $fk_persona
        ]);
        return $pdo->lastInsertId();
    }
    
    public static function listaUsu()
    {
        global $pdo;
        $sql = "SELECT u.pk_usuario, u.correoUsu, u.estatusUsu, p.nombres, p.aPaterno, p.aMaterno
                FROM usuarios u
                LEFT JOIN personas p ON u.fk_persona = p.pk_persona
                ORDER BY u.pk_usuario DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function actualizarUsu($correo, $password, $id)
    {
        global $pdo;

        // Si no hay contraseña nueva, solo actualiza el correo
        if ($password === null || $password === '') {
            $sql = "UPDATE usuarios 
                    SET correoUsu = :correo
                    WHERE pk_usuario = :id";

            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':correo' => $correo,
                ':id'     => $id
            ]);

            return $stmt->rowCount();
        }

        // Si hay contraseña, hashearla y actualizar ambos campos
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios 
                SET correoUsu = :correo, contrasUsu = :pass
                WHERE pk_usuario = :id";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':correo' => $correo,
            ':pass'   => $hash,
            ':id'     => $id
        ]);

        return $stmt->rowCount();
    }



}
