<?php
// Archivo de prueba para verificar la conexión a la base de datos

echo "<h2>🔍 Diagnóstico de Conexión MySQL</h2>\n";

// Configuraciones posibles para probar
$configs = [
    ['host' => 'localhost', 'user' => 'root', 'pass' => '', 'desc' => 'XAMPP/WAMP (sin contraseña)'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'mysql', 'desc' => 'Contraseña: mysql'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => 'root', 'desc' => 'Contraseña: root'],
    ['host' => '127.0.0.1', 'user' => 'root', 'pass' => '', 'desc' => 'IP local sin contraseña'],
    ['host' => 'localhost', 'user' => 'root', 'pass' => '123456', 'desc' => 'Contraseña: 123456']
];

$dbname = 'lavanderiaroeloss';
$conexionExitosa = false;

foreach ($configs as $config) {
    echo "<h3>Probando: {$config['desc']}</h3>\n";
    
    try {
        $pdo = new PDO("mysql:host={$config['host']};charset=utf8", $config['user'], $config['pass']);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
        echo "✅ <span style='color: green;'>Conexión básica exitosa</span><br>\n";
        
        // Probar conexión a la base de datos específica
        try {
            $pdo_db = new PDO("mysql:host={$config['host']};dbname=$dbname;charset=utf8", $config['user'], $config['pass']);
            echo "✅ <span style='color: green;'>Conexión a '$dbname' exitosa</span><br>\n";
            
            // Verificar tabla usuarios
            $stmt = $pdo_db->query("SHOW TABLES LIKE 'usuarios'");
            if ($stmt->rowCount() > 0) {
                echo "✅ <span style='color: green;'>Tabla 'usuarios' encontrada</span><br>\n";
                
                // Contar usuarios
                $stmt = $pdo_db->query("SELECT COUNT(*) as total FROM usuarios");
                $count = $stmt->fetch(PDO::FETCH_ASSOC);
                echo "📊 Total de usuarios: {$count['total']}<br>\n";
                
                if (!$conexionExitosa) {
                    echo "<div style='background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
                    echo "<strong>🎉 ¡CONFIGURACIÓN CORRECTA ENCONTRADA!</strong><br>";
                    echo "Usa esta configuración en config.php:<br><br>";
                    echo "<code>";
                    echo "\$host = '{$config['host']}';<br>";
                    echo "\$username = '{$config['user']}';<br>";
                    echo "\$password = '" . ($config['pass'] === '' ? '' : $config['pass']) . "';<br>";
                    echo "</code>";
                    echo "</div>";
                    $conexionExitosa = true;
                }
            } else {
                echo "❌ <span style='color: red;'>Tabla 'usuarios' no encontrada</span><br>\n";
            }
            
        } catch (PDOException $e) {
            echo "❌ <span style='color: red;'>Error conectando a '$dbname': " . $e->getMessage() . "</span><br>\n";
        }
        
    } catch (PDOException $e) {
        echo "❌ <span style='color: red;'>Error de conexión: " . $e->getMessage() . "</span><br>\n";
    }
    
    echo "<hr>\n";
}

if (!$conexionExitosa) {
    echo "<div style='background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<strong>⚠️ No se pudo establecer conexión</strong><br>";
    echo "Verifica que:<br>";
    echo "• XAMPP/WAMP esté ejecutándose<br>";
    echo "• MySQL esté activo<br>";
    echo "• La base de datos 'lavanderiaroeloss' exista<br>";
    echo "• Las credenciales sean correctas<br>";
    echo "</div>";
}
?>