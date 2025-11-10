<?php
// Prueba simple de conexión
try {
    echo "Probando conexión sin contraseña...\n";
    $pdo = new PDO("mysql:host=localhost;charset=utf8", "root", "");
    echo "✓ Conexión básica exitosa\n";
    
    echo "Probando base de datos lavanderiaroeloss...\n";
    $pdo = new PDO("mysql:host=localhost;dbname=lavanderiaroeloss;charset=utf8", "root", "");
    echo "✓ Conexión a lavanderiaroeloss exitosa\n";
    echo "¡La configuración es correcta!\n";
} catch (PDOException $e) {
    echo "✗ Error: " . $e->getMessage() . "\n";
    
    echo "\nProbando con contraseña 'mysql'...\n";
    try {
        $pdo = new PDO("mysql:host=localhost;dbname=lavanderiaroeloss;charset=utf8", "root", "mysql");
        echo "✓ Conexión exitosa con contraseña 'mysql'\n";
        echo "Necesitas usar \$password = 'mysql' en config.php\n";
    } catch (PDOException $e2) {
        echo "✗ Error con mysql: " . $e2->getMessage() . "\n";
        
        echo "\nProbando con contraseña 'root'...\n";
        try {
            $pdo = new PDO("mysql:host=localhost;dbname=lavanderiaroeloss;charset=utf8", "root", "root");
            echo "✓ Conexión exitosa con contraseña 'root'\n";
            echo "Necesitas usar \$password = 'root' en config.php\n";
        } catch (PDOException $e3) {
            echo "✗ Error con root: " . $e3->getMessage() . "\n";
            echo "Verifica tu configuración de MySQL\n";
        }
    }
}
?>