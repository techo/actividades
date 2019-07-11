-- save current setting of sql_mode
SET @old_sql_mode := @@sql_mode ;

-- derive a new value by removing NO_ZERO_DATE and NO_ZERO_IN_DATE
SET @new_sql_mode := @old_sql_mode ;
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_DATE,'  ,','));
SET @new_sql_mode := TRIM(BOTH ',' FROM REPLACE(CONCAT(',',@new_sql_mode,','),',NO_ZERO_IN_DATE,',','));
SET @@sql_mode := @new_sql_mode ;


UPDATE Actividad SET fechaFinInscripciones = null WHERE fechaFinInscripciones = '0000-00-00 00:00:00';
UPDATE Actividad SET fechaInicioEvaluaciones = null WHERE fechaInicioEvaluaciones = '0000-00-00 00:00:00';
UPDATE Actividad SET fechaFinEvaluaciones = null WHERE fechaFinEvaluaciones = '0000-00-00 00:00:00';