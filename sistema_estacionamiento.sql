SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;


CREATE TABLE `configuracion_sistema` (
  `id` int(11) NOT NULL,
  `nombre_negocio` varchar(150) NOT NULL,
  `telefono_negocio` varchar(20) DEFAULT NULL,
  `direccion_fisica` text DEFAULT NULL,
  `moneda_simbolo` varchar(5) DEFAULT '$',
  `tolerancia_entrada_minutos` int(11) DEFAULT 0,
  `nombre_impresora` varchar(100) DEFAULT 'POS-80',
  `papel_ancho_mm` int(11) DEFAULT 80,
  `avance_papel` int(11) DEFAULT 0,
  `numero_copias` int(11) DEFAULT 1,
  `encabezado_ticket` text DEFAULT NULL,
  `pie_ticket_entrada` text DEFAULT NULL,
  `pie_ticket_salida` text DEFAULT NULL,
  `fecha_actualizacion` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `ver_nombre` tinyint(1) DEFAULT 1,
  `ver_telefono` tinyint(1) DEFAULT 1,
  `ver_direccion` tinyint(1) DEFAULT 0,
  `ver_marca` tinyint(1) DEFAULT 1,
  `ver_folio` tinyint(1) DEFAULT 1,
  `ver_encabezado` tinyint(1) DEFAULT 1,
  `ver_pie_e` tinyint(1) DEFAULT 1,
  `ver_pie_s` tinyint(1) DEFAULT 1,
  `orden_elementos` text DEFAULT NULL,
  `estilos_ticket` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `horarios_operacion` (
  `id` int(11) NOT NULL,
  `dia_semana` enum('Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo') NOT NULL,
  `esta_abierto` tinyint(1) DEFAULT 1,
  `hora_apertura` time DEFAULT NULL,
  `hora_cierre` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `ingresos_vehiculos` (
  `id` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `id_tarifa` int(11) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `fecha_ingreso` datetime DEFAULT current_timestamp(),
  `estado` enum('En Estacionamiento','Finalizado') DEFAULT 'En Estacionamiento',
  `usuario_registro` varchar(50) DEFAULT 'Admin',
  `pago_adelantado_monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `pago_adelantado_concepto` varchar(60) DEFAULT NULL,
  `pago_adelantado_nota` varchar(120) DEFAULT NULL,
  `pago_adelantado_fecha` datetime DEFAULT NULL,
  `pago_adelantado_usuario` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `pagos_pensiones` (
  `id` int(11) NOT NULL,
  `pension_id` int(11) NOT NULL,
  `dias_extension` int(11) DEFAULT NULL,
  `vigencia_anterior_fin` date DEFAULT NULL,
  `vigencia_nueva_fin` date DEFAULT NULL,
  `fecha_pago` datetime NOT NULL DEFAULT current_timestamp(),
  `monto_mxn` decimal(10,2) NOT NULL DEFAULT 0.00,
  `plan_nombre` varchar(100) DEFAULT NULL,
  `metodo_pago` varchar(50) NOT NULL DEFAULT 'Efectivo',
  `referencia` varchar(120) DEFAULT NULL,
  `notas` text DEFAULT NULL,
  `usuario` varchar(80) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `pensiones` (
  `id` int(11) NOT NULL,
  `cliente_nombre` varchar(120) NOT NULL,
  `cliente_telefono` varchar(30) DEFAULT NULL,
  `vehiculo_placa` varchar(20) NOT NULL,
  `vehiculo_tipo` varchar(50) NOT NULL DEFAULT 'Automóvil',
  `plan_nombre` varchar(100) DEFAULT NULL,
  `monto_mxn` decimal(10,2) NOT NULL DEFAULT 0.00,
  `vigencia_inicio` date NOT NULL,
  `vigencia_fin` date NOT NULL,
  `notas` text DEFAULT NULL,
  `esta_activa` tinyint(1) NOT NULL DEFAULT 1,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE `salidas_vehiculos` (
  `id` int(11) NOT NULL,
  `id_ingreso` int(11) NOT NULL,
  `boleto_perdido` tinyint(1) NOT NULL DEFAULT 0,
  `descuento_tipo` enum('PORCENTAJE','MONTO','HORAS') DEFAULT NULL,
  `descuento_valor` decimal(10,2) DEFAULT NULL,
  `descuento_monto` decimal(10,2) NOT NULL DEFAULT 0.00,
  `descuento_motivo` varchar(255) DEFAULT NULL,
  `fecha_salida` datetime NOT NULL DEFAULT current_timestamp(),
  `minutos_totales` int(11) NOT NULL DEFAULT 0,
  `monto_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `extra_noche` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_recibido` decimal(10,2) NOT NULL DEFAULT 0.00,
  `monto_cambio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `usuario_cobro` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `tarifas_vehiculos` (
  `id` int(11) NOT NULL,
  `tipo_vehiculo` varchar(50) NOT NULL,
  `costo_hora` decimal(10,2) NOT NULL,
  `costo_fraccion_extra` decimal(10,2) NOT NULL,
  `tolerancia_extra_minutos` int(11) DEFAULT 0,
  `tolerancia_entrada_minutos` int(11) DEFAULT 0,
  `costo_boleto_perdido` decimal(10,2) DEFAULT 0.00,
  `extra_noche` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(80) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `rol` enum('ADMIN','CAJERO','OPERADOR') NOT NULL DEFAULT 'OPERADOR',
  `activo` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_acceso` datetime DEFAULT NULL,
  `fecha_creacion` datetime NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` datetime DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;


ALTER TABLE `configuracion_sistema`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `horarios_operacion`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `ingresos_vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_tarifa` (`id_tarifa`),
  ADD KEY `idx_ingresos_estado` (`estado`),
  ADD KEY `idx_ingresos_fecha_ingreso` (`fecha_ingreso`),
  ADD KEY `idx_ingresos_placa` (`placa`),
  ADD KEY `idx_ingresos_pago_adelantado` (`pago_adelantado_fecha`);

ALTER TABLE `pagos_pensiones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_pension_fecha` (`pension_id`,`fecha_pago`);

ALTER TABLE `pensiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_pensiones_placa` (`vehiculo_placa`),
  ADD KEY `idx_activa` (`esta_activa`),
  ADD KEY `idx_vigencia` (`vigencia_inicio`,`vigencia_fin`);

ALTER TABLE `salidas_vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_salida_por_ingreso` (`id_ingreso`),
  ADD KEY `idx_fecha_salida` (`fecha_salida`);

ALTER TABLE `tarifas_vehiculos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_usuario` (`usuario`),
  ADD KEY `idx_rol` (`rol`),
  ADD KEY `idx_activo` (`activo`);


ALTER TABLE `configuracion_sistema`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `horarios_operacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `ingresos_vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pagos_pensiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `pensiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `salidas_vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `tarifas_vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;


ALTER TABLE `ingresos_vehiculos`
  ADD CONSTRAINT `ingresos_vehiculos_ibfk_1` FOREIGN KEY (`id_tarifa`) REFERENCES `tarifas_vehiculos` (`id`);

ALTER TABLE `pagos_pensiones`
  ADD CONSTRAINT `fk_pagos_pensiones_pension` FOREIGN KEY (`pension_id`) REFERENCES `pensiones` (`id`) ON DELETE CASCADE;

ALTER TABLE `salidas_vehiculos`
  ADD CONSTRAINT `fk_salidas_ingreso` FOREIGN KEY (`id_ingreso`) REFERENCES `ingresos_vehiculos` (`id`) ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
