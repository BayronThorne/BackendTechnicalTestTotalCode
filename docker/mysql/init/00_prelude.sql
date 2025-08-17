CREATE DATABASE IF NOT EXISTS totalcode;
USE totalcode;

SET @@GLOBAL.sql_mode = REPLACE(@@GLOBAL.sql_mode,'ONLY_FULL_GROUP_BY','');

SET @@GLOBAL.sql_mode =
  REPLACE(REPLACE(@@GLOBAL.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE','');

SET SESSION sql_mode = REPLACE(@@SESSION.sql_mode,'ONLY_FULL_GROUP_BY','');
SET SESSION sql_mode =
  REPLACE(REPLACE(@@SESSION.sql_mode,'NO_ZERO_DATE',''),'NO_ZERO_IN_DATE','');

SET time_zone = '+00:00';
