<html>
<head>
    <title>Confimacion Inscripcion</title>
</head>
 
<body>
  <table border="0" style="border:1px solid #999999;font-family:Helvetica,sans-serif;font-size:12px" align="center">
    <tbody>
      <tr>
        <td>
          <p>Hola {{$inscripcion->persona->nombres}} {{$inscripcion->persona->apellidoPaterno}}: </p>
          <p> Te has inscrito para participar en <strong>{{$inscripcion->actividad->nombreActividad}}</strong> de TECHO - {{$inscripcion->actividad->pais->nombre}} que inicia el {{$inscripcion->actividad->fechaInicio->format('d/m/Y')}}.  </p>
          <b>En:</b> {{$inscripcion->actividad->localidad->localidad}} {{$inscripcion->actividad->provincia->provincia}}
          <b>Coordinador:</b> {{$inscripcion->actividad->coordinador->nombres}} {{$inscripcion->actividad->coordinador->apellidoPaterno}}
          <p>{{$inscripcion->actividad->mensajeInscripcion}} <br></p>
          @if($inscripcion->actividad->LinkPago)
          <p>
            <br>
            <b>
              <span style="font-size:20px">Ahora SOLO FALTA UN PASO: 
                <span style="color:rgb(255,153,0)">ABONAR LA CONSTRUCCIÓN </span>
              </span>
            </b>
            <br>
            <br>
            <b>TENÉS TIEMPO HASTA EL VIERNES 17 DE NOVIEMBRE</b> para confirmar tu inscripción!<br>
            <br>
            Te dejamos el siguiente <a href="{{$inscripcion->actividad->LinkPago}}">BOTÓN DE PAGO</a> e <a href="https://sites.google.com/a/techo.org/veni-a-construir/pago" target="_blank" >INSTRUCTIVO</a> que te permiten gestionar cómo querés abonar la construcción.<br>
            Te recordamos que el monto para abonar es de <b>${{$inscripcion->actividad->costo}}</b>, los cuales cubren los gastos de traslado, seguro y comida durante la construcción. En el caso que no puedas abonarlo, no queremos que dejes de participar, escribinos a <a href="mailto:problemasdepago.argentina@techo.org" target="_blank">problemasdepago.argentina@<wbr>techo.org</a> para poder gestionar una PRÓRROGA o BECA.<br>
            <br>
          </p>
          @endif
          <p><b>Te esperamos!!!</b></p>
          <p>Punto de encuento</p>
          <ul>
            <li>
              <b>{{$inscripcion->punto_encuento->punto}}</b> - {{$inscripcion->punto_encuento->localidad->localidad}} - {{$inscripcion->punto_encuento->provincia->provincia}} - {{$inscripcion->punto_encuento->pais->nombre}} <b>horario:</b> {{$inscripcion->punto_encuento->horario}} <b>coordinador:</b> {{$inscripcion->punto_encuento->responsable->nombres}} {{$inscripcion->punto_encuento->responsable->apellidoPaterno}}
            </li>
          </ul>
          
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