<?php
require_once __DIR__ . '/../config.php';

class Pedido {
    public static function crearPedido(float $total, string $tipoEntrega, int $clienteId, array $prendas)
    {
        global $pdo;
        // Calcular peso total desde el modelo (esto SÍ pertenece aquí)
    $pesoTotal = array_sum(array_column($prendas, 'peso'));

    // Insertar pedido
    $sqlPedido = "
        INSERT INTO pedidos (fechaDeRecibo, totalPedido, peso_total_kg, tipoEntrega, estatusPedido, fk_cliente) 
        VALUES (NOW(), ?, ?, ?, 1, ?)
    ";
    $stmt = $pdo->prepare($sqlPedido);
    $stmt->execute([$total, $pesoTotal, $tipoEntrega, $clienteId]);
    $pedidoId = $pdo->lastInsertId();

    // Insertar prendas
    $sqlItem = "
        INSERT INTO items_pedido (fk_pedido, fk_tipo_prenda, peso_kg, precio_unitario, subtotal) 
        VALUES (?, ?, ?, ?, ?)
    ";
    $stmtItem = $pdo->prepare($sqlItem);

    foreach ($prendas as $prenda) {

        $subtotal = $prenda['peso'] * $prenda['precioUnitario'];

        $stmtItem->execute([
            $pedidoId,
            $prenda['tipoPrenda'],
            $prenda['peso'],
            $prenda['precioUnitario'],
            $subtotal
        ]);
    }

    return $pedidoId;
    }
    
    public static function historialPedido()
    {
        global $pdo;
        $sql = "SELECT 
                p.pk_pedido AS id_pedido,
                per.nombres,
                per.aPaterno,
                per.aMaterno,
                p.tipoEntrega,
                p.estatusPedido,
                p.totalPedido,
                p.peso_total_kg,
                DATE_FORMAT(p.fechaDeRecibo, '%d/%m/%Y %H:%i') AS fecha,
                GROUP_CONCAT(
                    CONCAT(tp.nombre_tipo, ' (', ip.peso_kg, ' kg)')
                    ORDER BY tp.nombre_tipo
                    SEPARATOR '|'
                ) AS tipos_prenda
            FROM pedidos p
            INNER JOIN clientes c ON p.fk_cliente = c.pk_cliente
            INNER JOIN personas per ON c.fk_persona = per.pk_persona
            LEFT JOIN items_pedido ip ON p.pk_pedido = ip.fk_pedido
            LEFT JOIN tipos_prenda tp ON ip.fk_tipo_prenda = tp.pk_tipo_prenda
            GROUP BY p.pk_pedido, per.nombres, per.aPaterno, per.aMaterno, 
                     p.tipoEntrega, p.estatusPedido, p.totalPedido, 
                     p.peso_total_kg, p.fechaDeRecibo
            ORDER BY p.pk_pedido DESC";
        $stmt = $pdo->query($sql);
        $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $pedidos;
    }

    public static function existe($id) {
        global $pdo;
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM pedidos WHERE pk_pedido = ?");
        $stmt->execute([$id]);
        return $stmt->fetchColumn() > 0;
    }

    public static function actualizar($pedidoId, $clienteId, $tipoEntrega, $estatusPedido, $total, $prendas) {
        global $pdo;

        if (!self::existe($pedidoId)) {
            throw new Exception("El pedido no existe.");
        }

        // Calcular peso total
        $pesoTotal = array_sum(array_column($prendas, 'peso'));

        // Actualizar pedido
        $sql = "UPDATE pedidos SET 
                    totalPedido = ?, 
                    peso_total_kg = ?, 
                    tipoEntrega = ?, 
                    estatusPedido = ?, 
                    fk_cliente = ?
                WHERE pk_pedido = ?";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$total, $pesoTotal, $tipoEntrega, $estatusPedido, $clienteId, $pedidoId]);

        // Remover items anteriores
        $stmt = $pdo->prepare("DELETE FROM items_pedido WHERE fk_pedido = ?");
        $stmt->execute([$pedidoId]);

        // Insertar nuevos items
        $sqlItem = "INSERT INTO items_pedido 
                    (fk_pedido, fk_tipo_prenda, peso_kg, precio_unitario, subtotal)
                    VALUES (?, ?, ?, ?, ?)";

        $stmtItem = $pdo->prepare($sqlItem);

        foreach ($prendas as $p) {
            $subtotal = $p['peso'] * $p['precioUnitario'];
            $stmtItem->execute([
                $pedidoId,
                $p['tipoPrenda'],
                $p['peso'],
                $p['precioUnitario'],
                $subtotal
            ]);
        }

        return $stmt->rowCount();
    }


}
