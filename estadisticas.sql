-- Cantidad de personas únicas que participaron
SELECT count(distinct idPersona)
FROM Inscripcion i
WHERE
presente = 1 AND
created_at >= '01-01-2019 00:00';

-- voluntades movilizadas confirmadas
SELECT count(*)
FROM Inscripcion i
WHERE
presente = 1 AND
created_at >= '01-01-2019 00:00';

-- voluntades movilizadas sin confirmar
SELECT count(*)
FROM Inscripcion i
WHERE
-- presente = 1 AND
created_at >= '01-01-2019 00:00';

-- top 10 actividades más convocadoras
SELECT nombreActividad, count(*)
FROM Inscripcion i
JOIN Actividad a ON a.idActividad = i.idActividad
WHERE
-- presente = 1 AND
created_at >= '01-01-2019 00:00'
group by nombreActividad
order by count(*) desc
LIMIT 10;

-- top voluntarios más involucrados
SELECT p.nombres, p.apellidoPaterno, count(*)
FROM Persona p
JOIN Inscripcion i ON p.idPersona = i.idPersona
WHERE
presente = 1 AND
p.created_at >= '01-01-2018 00:00'
group by p.idPersona, p.nombres, p.apellidoPaterno
order by count(*) desc;

-- top coordinadores más convocantes
SELECT a.idCoordinador, p.nombres, p.apellidoPaterno, count(*)
FROM Actividad a
JOIN Inscripcion i ON a.idActividad = i.idActividad
JOIN Persona p ON p.idPersona = a.idCoordinador
WHERE
presente = 1 AND
year(i.created_at) = 2019
group by a.idCoordinador
order by count(*) desc
LIMIT 10;

SELECT p.nombres, p.apellidoPaterno, count(*)
FROM Actividad a
JOIN Inscripcion i ON a.idActividad = i.idActividad
JOIN Persona p ON p.idPersona = a.idCoordinador
WHERE
presente = 1 AND
year(i.created_at) = 2019
group by a.idCoordinador
order by count(*) desc
LIMIT 10;