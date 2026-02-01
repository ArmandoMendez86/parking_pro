<?php
// config/configuracion.php

// 1) Zona horaria
date_default_timezone_set('America/Mexico_City');

// 2) URL_BASE
$protocolo = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";
$servidor  = $_SERVER['HTTP_HOST'] ?? 'localhost';

// ⚠️ Ajusta a tu carpeta real en XAMPP (ya la traías así)
$carpeta = "/sistema_estacionamiento/";

define('URL_BASE', $protocolo . $servidor . $carpeta);

// 3) Helper de auth (ruta absoluta robusta)
require_once __DIR__ . '/../app/helpers/AuthHelper.php';

// 4) PROTECCIÓN CENTRALIZADA (solo vistas)
$uri = $_SERVER['REQUEST_URI'] ?? '';

// Normaliza (por si viene con query)
$path = parse_url($uri, PHP_URL_PATH) ?? $uri;

// ✅ Solo protege si es una ruta dentro de /vistas/
// ❌ Excluye login, controladores y assets públicos
$esVista = (strpos($path, '/vistas/') !== false);
$esLogin = (strpos($path, '/vistas/login/') !== false);
$esControlador = (strpos($path, '/app/controladores/') !== false);
$esPublico = (strpos($path, '/publico/') !== false);

if ($esVista && !$esLogin && !$esControlador && !$esPublico) {
  AuthHelper::protegerVista();
}

// 5) BD
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
