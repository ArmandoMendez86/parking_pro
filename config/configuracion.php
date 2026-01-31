<?php
// Configurar la zona horaria para México / América Latina
date_default_timezone_set('America/Mexico_City');
// Configuración de rutas
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$servidor = $_SERVER['HTTP_HOST'];
$carpeta = "/sistema_estacionamiento/"; 

define('URL_BASE', $protocolo . $servidor . $carpeta);

// Configuración de Base de Datos
$host = 'localhost';
$db_nombre = 'sistema_estacionamiento';
$usuario = 'root';
$clave = '';

try {
    $db = new PDO("mysql:host=$host;dbname=$db_nombre;charset=utf8", $usuario, $clave);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}