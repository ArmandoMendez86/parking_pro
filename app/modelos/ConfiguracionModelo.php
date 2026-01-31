<?php
class ConfiguracionModelo {
    private $conexion;
    public function __construct($db) { $this->conexion = $db; }

    public function obtenerConfiguracionGlobal() {
        $stmt = $this->conexion->prepare("SELECT * FROM configuracion_sistema LIMIT 1");
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function guardarConfiguracionGlobal($datos) {
        $sql = "UPDATE configuracion_sistema SET 
                nombre_negocio = :nombre, telefono_negocio = :telefono, direccion_fisica = :direccion,
                moneda_simbolo = :moneda, tolerancia_entrada_minutos = :tolerancia,
                nombre_impresora = :impresora, papel_ancho_mm = :papel, numero_copias = :copias,
                encabezado_ticket = :encabezado, pie_ticket_entrada = :pie_e, pie_ticket_salida = :pie_s, 
                ver_nombre = :v_nom, ver_telefono = :v_tel, ver_direccion = :v_dir, 
                ver_marca = :v_mar, ver_folio = :v_fol, ver_encabezado = :v_enc,
                ver_pie_e = :v_pe, ver_pie_s = :v_ps, estilos_ticket = :estilos WHERE id = 1";
        return $this->conexion->prepare($sql)->execute($datos);
    }

    public function actualizarHorarioDia($dia, $abierto, $apertura, $cierre) {
        $sql = "UPDATE horarios_operacion SET esta_abierto = ?, hora_apertura = ?, hora_cierre = ? WHERE dia_semana = ?";
        return $this->conexion->prepare($sql)->execute([$abierto, $apertura, $cierre, $dia]);
    }

    // ✅ NUEVO: borrado real en BD
    public function eliminarTarifa($id) {
        $sql = "DELETE FROM tarifas_vehiculos WHERE id = ?";
        return $this->conexion->prepare($sql)->execute([(int)$id]);
    }

    // NUEVA LÓGICA: Sincroniza usando el ID para no romper llaves foráneas
    public function sincronizarTarifa($id, $tipo, $costo, $extra, $tol, $perd) {
        if (!empty($id)) {
            $sql = "UPDATE tarifas_vehiculos 
                    SET tipo_vehiculo = ?, costo_hora = ?, costo_fraccion_extra = ?, tolerancia_extra_minutos = ?, costo_boleto_perdido = ?
                    WHERE id = ?";
            return $this->conexion->prepare($sql)->execute([$tipo, $costo, $extra, $tol, $perd, $id]);
        } else {
            $sql = "INSERT INTO tarifas_vehiculos (tipo_vehiculo, costo_hora, costo_fraccion_extra, tolerancia_extra_minutos, costo_boleto_perdido)
                    VALUES (?, ?, ?, ?, ?)";
            return $this->conexion->prepare($sql)->execute([$tipo, $costo, $extra, $tol, $perd]);
        }
    }
}
