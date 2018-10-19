USE sigp;

CREATE TABLE IF NOT EXISTS evaluaciones_social AS (
SELECT e.IdEncuestaRespuesta, e.idEvaluador AS idEvaluador, e.idEvaluado as idEvaluado, e.idActividad as idActividad, trim(left(o.Texto, 2))+0 as puntaje, o.Texto
FROM Actividad act
       JOIN _EncuestaRespuestaActividad AS e ON act.idActividad = e.idActividad
       JOIN _PreguntaRespuesta AS r ON r.idEncuestaRespuesta = e.IdEncuestaRespuesta
       JOIN _Opcion AS o ON o.idPregunta = r.IdPregunta AND o.posicion = r.Respuesta
       JOIN _Pregunta AS p ON p.IdPregunta = r.IdPregunta
WHERE
 idFormulario = 111 AND
 p.Texto = 'Puntaje Social');

CREATE TABLE IF NOT EXISTS evaluaciones_tecnica AS (
SELECT e.IdEncuestaRespuesta, e.idEvaluador AS idEvaluador, e.idEvaluado as idEvaluado, e.idActividad as idActividad, trim(left(o.Texto, 2))+0 as puntaje, o.Texto
FROM Actividad act
       JOIN _EncuestaRespuestaActividad AS e ON act.idActividad = e.idActividad
       JOIN _PreguntaRespuesta AS r ON r.idEncuestaRespuesta = e.IdEncuestaRespuesta
       JOIN _Opcion AS o ON o.idPregunta = r.IdPregunta AND o.posicion = r.Respuesta
       JOIN _Pregunta AS p ON p.IdPregunta = r.IdPregunta
WHERE
idFormulario = 111 AND
p.Texto = 'Puntaje Constructivo:');

CREATE INDEX i_evaluaciones_social ON evaluaciones_social (idEvaluador,idEvaluado,idActividad);
CREATE INDEX i_evaluaciones_tecnica ON evaluaciones_tecnica (idEvaluador,idEvaluado,idActividad);

-- tabla haciendo el full join
CREATE TABLE evaluaciones AS
   SELECT *
   FROM (
		SELECT
			s.idActividad,
			s.idEvaluado,
			s.idEvaluador,
			t.puntaje as tecnico,
			s.puntaje as social,
			s.texto
		FROM
			evaluaciones_social s LEFT JOIN evaluaciones_tecnica t
			ON s.idActividad = t.idActividad
				AND s.idEvaluado = t.idEvaluado
				AND s.idEvaluador = t.idEvaluador
			 JOIN Persona p ON s.idEvaluado=p.idPersona

		UNION

		SELECT
			s.idActividad,
			s.idEvaluado,
			s.idEvaluador,
			t.puntaje as tecnico,
			s.puntaje as social,
			s.texto
		FROM
			evaluaciones_social s RIGHT JOIN evaluaciones_tecnica t
			ON s.idActividad = t.idActividad
				AND s.idEvaluado = t.idEvaluado
				AND s.idEvaluador = t.idEvaluador
			 JOIN Persona p ON s.idEvaluado=p.idPersona
		) ev;

-- importar a tabla correspondiente
INSERT INTO EvaluacionPersona (idActividad,idEvaluado,idEvaluador, puntajeTecnico, puntajeSocial)
SELECT
    idActividad,
    idEvaluado,
    idEvaluador,
    tecnico,
	social
FROM
    evaluaciones;

-- borrar temporales    
DROP TABLE evaluaciones;
DROP TABLE evaluaciones_social;
DROP TABLE evaluaciones_tecnica;
