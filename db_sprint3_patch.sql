-- ============================================================
--  NutriSucre — SQL Patch Sprint 3 (Compatibilidad Universal)
--  Ejecuta este código en la pestaña SQL de phpMyAdmin de Clever Cloud
-- ============================================================

-- USE bcj5pzf6lomricmiafi6; -- Comentado para Clever Cloud ya que la BD ya está seleccionada por defecto

-- 1. Modificaciones a la tabla de usuarios
ALTER TABLE usuarios ADD COLUMN ci VARCHAR(30) DEFAULT NULL;
ALTER TABLE usuarios ADD COLUMN celular VARCHAR(30) DEFAULT NULL;
ALTER TABLE usuarios ADD COLUMN estado ENUM('activo','bloqueado') DEFAULT 'activo';

-- 2. Modificaciones a la tabla de nutricionistas (perfil profesional, pagos y contacto)
ALTER TABLE nutricionistas ADD COLUMN telefono VARCHAR(30) DEFAULT NULL;
ALTER TABLE nutricionistas ADD COLUMN whatsapp VARCHAR(30) DEFAULT NULL;
ALTER TABLE nutricionistas ADD COLUMN mostrar_correo TINYINT(1) DEFAULT 1;
ALTER TABLE nutricionistas ADD COLUMN qr_code VARCHAR(255) DEFAULT NULL;
ALTER TABLE nutricionistas ADD COLUMN titular_cuenta VARCHAR(150) DEFAULT NULL;
ALTER TABLE nutricionistas ADD COLUMN banco VARCHAR(150) DEFAULT NULL;
ALTER TABLE nutricionistas ADD COLUMN nro_cuenta VARCHAR(100) DEFAULT NULL;
ALTER TABLE nutricionistas ADD COLUMN datos_transferencia_adicional TEXT DEFAULT NULL;
ALTER TABLE nutricionistas ADD COLUMN pago_qr_habilitado TINYINT(1) DEFAULT 0;
ALTER TABLE nutricionistas ADD COLUMN pago_transferencia_habilitado TINYINT(1) DEFAULT 0;
ALTER TABLE nutricionistas ADD COLUMN pago_deposito_habilitado TINYINT(1) DEFAULT 0;
ALTER TABLE nutricionistas ADD COLUMN fotos_adicionales JSON DEFAULT NULL;

-- 3. Modificaciones a la tabla de citas (relación con servicios y comprobantes)
ALTER TABLE citas ADD COLUMN servicio_id INT DEFAULT NULL;
ALTER TABLE citas ADD COLUMN comprobante_pago VARCHAR(255) DEFAULT NULL;
ALTER TABLE citas ADD COLUMN metodo_pago ENUM('QR','Transferencia','Deposito') DEFAULT NULL;
ALTER TABLE citas ADD COLUMN motivo_rechazo TEXT DEFAULT NULL;
ALTER TABLE citas MODIFY COLUMN estado ENUM('pendiente','pendiente_confirmacion','confirmada','rechazada','cancelada') DEFAULT 'pendiente_confirmacion';

-- 4. Restricción de clave foránea para servicio_id en citas (opcional pero recomendada)
ALTER TABLE citas ADD CONSTRAINT fk_citas_servicios 
    FOREIGN KEY (servicio_id) REFERENCES servicios(id) ON DELETE SET NULL;
