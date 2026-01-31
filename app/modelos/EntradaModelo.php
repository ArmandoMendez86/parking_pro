<?php
class EntradaModelo
{
    private $conexion;

    public function __construct($db)
    {
        $this->conexion = $db;
    }

    public function verificarVehiculoInterno($placa)
    {
        $sql = "SELECT id FROM ingresos_vehiculos WHERE placa = ? AND estado = 'En Estacionamiento'";
        $stmt = $this->conexion->prepare($sql);
        $stmt->execute([$placa]);
        return $stmt->fetch();
    }

    public function registrarIngreso($placa, $id_tarifa, $marca, $color, $fecha)
    {
        // Cambiamos NOW() por un marcador de posición ?
        $sql = "INSERT INTO ingresos_vehiculos (placa, id_tarifa, marca, color, fecha_ingreso, estado) 
            VALUES (?, ?, ?, ?, ?, 'En Estacionamiento')";
        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([$placa, $id_tarifa, $marca, $color, $fecha]);
    }
}
