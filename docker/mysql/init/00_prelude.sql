-- Se ejecuta antes de importar tus datos
CREATE DATABASE IF NOT EXISTS totalcode;
USE totalcode;

-- Quitar ONLY_FULL_GROUP_BY (y mantener otras configuraciones)
SET @@GLOBAL.sql_mode = REPLACE(@@GLOBAL.sql_mode,'ONLY_FULL_GROUP_BY','');

-- Relajar restricciones de fechas '0000-00-00 ...' si aparecieran
SET @@GLOBAL.sql_mode =
  REPLACE(REPLACE(@@GLOBAL.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE','');

-- A nivel de sesión también
SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','');
SET SESSION sql_mode =
  REPLACE(REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE','');

-- Evitar warnings por zona horaria
SET time_zone = '+00:00';
