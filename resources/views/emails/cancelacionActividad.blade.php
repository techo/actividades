<html>
<head>
    <title>Cancelacion Actividad</title>
</head>
 
<body>
  <table border="0" style="border:1px solid #999999;font-family:Helvetica,sans-serif;font-size:12px" align="center">
    <tbody>
      <tr>
        <td>
          <p>Hola {{$inscripcion->persona->nombres}} {{$inscripcion->persona->apellidoPaterno}}: </p>
          <p> Te informamos que la actividad <strong>{{$inscripcion->actividad->nombreActividad}}</strong> de TECHO - {{$inscripcion->actividad->pais->nombre}} que iniciaba el {{$inscripcion->actividad->fechaInicio->format('d/m/Y')}}. a sido <strong>CANCELADA</strong> </p>
          <p><b>Agradecemos tu interes y te invitamos a entrar en el sitio de Techo para buscar otras actividades!!!</b></p>
          <p> Para TECHO - Argentina es importante que te mantengas enterado de las nuevas actividades. Para ello, entra siempre en nuestro Sitio Web.  </p>
            <p> Muchas gracias!!  </p>
          </p>
        </td> 
      </tr>
      <tr>
        <td> 
          @include('emails.footer') 
        </td>
      </tr>
    </tbody>
  </table>
</body>
</html>

</body>
 
</html>