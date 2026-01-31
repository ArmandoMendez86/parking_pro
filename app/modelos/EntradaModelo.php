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

    public function registrarIngreso(
        $placa,
        $id_tarifa,
        $marca,
        $color,
        $fecha,
        $pago_adelantado_monto = 0.00,
        $pago_adelantado_concepto = null,
        $pago_adelantado_nota = null,
        $pago_adelantado_usuario = null
    ) {
        $sql = "INSERT INTO ingresos_vehiculos
                    (placa, id_tarifa, marca, color, fecha_ingreso, estado,
                     pago_adelantado_monto, pago_adelantado_concepto, pago_adelantado_nota, pago_adelantado_fecha, pago_adelantado_usuario)
                VALUES (?, ?, ?, ?, ?, 'En Estacionamiento', ?, ?, ?, ?, ?)";

        $stmt = $this->conexion->prepare($sql);
        return $stmt->execute([
            $placa,
            (int)$id_tarifa,
            $marca,
            $color,
            $fecha,
            (float)$pago_adelantado_monto,
            $pago_adelantado_concepto,
            $pago_adelantado_nota,
            ((float)$pago_adelantado_monto > 0 ? $fecha : null),
            $pago_adelantado_usuario
        ]);
    }
}
