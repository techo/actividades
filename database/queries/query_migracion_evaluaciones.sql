use sigp;

select puntaje_social.idEvaluador, puntaje_social.idEvaluado, puntaje_social.idActividad, puntaje_social.puntajeSocial, puntaje_constructivo.puntajeConstructivo
    FROM
(SELECT e.IdEncuestaRespuesta, e.idEvaluador AS idEvaluador, e.idEvaluado as idEvaluado, e.idActividad as idActividad, r.Respuesta, trim(left(o.Texto, 2))+0 as puntajeSocial, p.Texto
FROM Actividad act
       JOIN _EncuestaRespuestaActividad AS e ON act.idActividad = e.idActividad
       JOIN _PreguntaRespuesta AS r ON r.idEncuestaRespuesta = e.IdEncuestaRespuesta
       JOIN _Opcion AS o ON o.idPregunta = r.IdPregunta
                              AND o.posicion = r.Respuesta
       JOIN _Pregunta AS p ON p.IdPregunta = r.IdPregunta
WHERE act.idActividad = 15916
  AND idFormulario = 111
  and e.idEvaluado = 154511
  AND trim(left(o.Texto, 2)) REGEXP '^[0-9]+$'
  AND p.Texto in ('Puntaje Social')) as puntaje_social
LEFT JOIN
(SELECT e.IdEncuestaRespuesta, e.idEvaluador as idEvaluador, e.idEvaluado as idEvaluado, e.idActividad as idActividad, r.Respuesta, trim(left(o.Texto, 2))+0 as puntajeConstructivo
FROM Actividad act
       JOIN _EncuestaRespuestaActividad AS e ON act.idActividad = e.idActividad
       JOIN _PreguntaRespuesta AS r ON r.idEncuestaRespuesta = e.IdEncuestaRespuesta
       JOIN _Opcion AS o ON o.idPregunta = r.IdPregunta
                              AND o.posicion = r.Respuesta
       JOIN _Pregunta AS p ON p.IdPregunta = r.IdPregunta
WHERE act.idActividad = 15916
  AND idFormulario = 111
  and e.idEvaluado = 154511
  AND trim(left(o.Texto, 2)) REGEXP '^[0-9]+$'
  AND p.Texto in ('Puntaje Constructivo:')) as puntaje_constructivo
ON puntaje_social.idEvaluador = puntaje_constructivo.idEvaluador
AND puntaje_social.idEvaluado = puntaje_constructivo.idEvaluado
AND puntaje_social.idActividad = puntaje_constructivo.idActividad
AND puntaje_social.idActividad = 15916
UNION
select puntaje_social.idEvaluador, puntaje_social.idEvaluado, puntaje_social.idActividad, puntaje_social.puntajeSocial, puntaje_constructivo.puntajeConstructivo
FROM
     (SELECT e.IdEncuestaRespuesta, e.idEvaluador AS idEvaluador, e.idEvaluado as idEvaluado, e.idActividad as idActividad, r.Respuesta, trim(left(o.Texto, 2))+0 as puntajeSocial, p.Texto
      FROM Actividad act
             JOIN _EncuestaRespuestaActividad AS e ON act.idActividad = e.idActividad
             JOIN _PreguntaRespuesta AS r ON r.idEncuestaRespuesta = e.IdEncuestaRespuesta
             JOIN _Opcion AS o ON o.idPregunta = r.IdPregunta
                                    AND o.posicion = r.Respuesta
             JOIN _Pregunta AS p ON p.IdPregunta = r.IdPregunta
      WHERE act.idActividad = 15916
        AND idFormulario = 111
        and e.idEvaluado = 154511
        AND trim(left(o.Texto, 2)) REGEXP '^[0-9]+$'
        AND p.Texto in ('Puntaje Social')) as puntaje_social
       RIGHT JOIN
         (SELECT e.IdEncuestaRespuesta, e.idEvaluador as idEvaluador, e.idEvaluado as idEvaluado, e.idActividad as idActividad, r.Respuesta, trim(left(o.Texto, 2))+0 as puntajeConstructivo
          FROM Actividad act
                 JOIN _EncuestaRespuestaActividad AS e ON act.idActividad = e.idActividad
                 JOIN _PreguntaRespuesta AS r ON r.idEncuestaRespuesta = e.IdEncuestaRespuesta
                 JOIN _Opcion AS o ON o.idPregunta = r.IdPregunta
                                        AND o.posicion = r.Respuesta
                 JOIN _Pregunta AS p ON p.IdPregunta = r.IdPregunta
          WHERE act.idActividad = 15916
            AND idFormulario = 111
            and e.idEvaluado = 154511
            AND trim(left(o.Texto, 2)) REGEXP '^[0-9]+$'
            AND p.Texto in ('Puntaje Constructivo:')) as puntaje_constructivo
         ON puntaje_social.idEvaluador = puntaje_constructivo.idEvaluador
              AND puntaje_social.idEvaluado = puntaje_constructivo.idEvaluado
              AND puntaje_social.idActividad = puntaje_constructivo.idActividad
              AND puntaje_social.idActividad = 15916
              AND puntaje_social.idEvaluado = 154511;
