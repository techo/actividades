<!DOCTYPE html>
<html>
  <head>
    <meta charset="utf-8">
    <title>Seleccionar País</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css">
    <style media="screen">

      body{

        background-image: url("/img/backgroundselectCountry.png");
        background-position: center; /* Center the image */
        background-repeat: no-repeat; /* Do not repeat the image */
        background-size: cover; /* Resize the background image to cover the entire container */
      }

       .container{
      margin-top: 5%;

      }

      .logo-container{
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        text-align: center;
        padding-right: 70px;

      }

      b{
        color:rgb(0, 146, 221);
        font-size: 0.9vw;
        padding-top: 5px;
      }

      h3{
        color:rgb(0, 146, 221);
        /* display: flex; */
        font-size: 17px;
      }

      li{
        list-style-type: none;
        margin-bottom: 5px;
        display: flex;
        align-items: center;
        justify-content: start;
        margin-right:5px;

      }

      li img{
        margin-right: 20px;
        width: 40px;
      }

      li a {
      	display: contents;
      }

      .contenedor-paises{
       display: flex;
       flex-direction: column;
       align-content: space-between;
       height: 80vh;
       padding: 60px 60px 60px 60px;
      }

      .footer{
        height: 10vh;
        background-color: grey;
      }
      .footer img{
        margin-left: 60px;
      }

    @media only screen and (max-width: 600px) {
       .contenedor-paises{
       	height: 100%;
       	padding: 0px;
       	padding-inline-start: 40px;
       }

       b{
         font-size: 5vw;
       }

       .logo-container{
         padding: 30px;
       }


    }
    </style>
  </head>
  <body>

    <div class="container">
      <div class="row">
        <div class="logo-container col-sm-12 col-md-4 ">
          <img src="/img/techo_logo_big.png" alt="logo techo" width="100%">
          <b>PLATAFORMA DE ACTIVIDADES</b>
        </div>
        <div class="col-sm-12 col-md-8">

              <ul class="contenedor-paises row">

                <li ><a href="{{ $protocolo }}//ar.{{ $url }}"><img src="https://www.countryflags.io/ar/flat/64.png"> <h3>Argentina</li></a>
                <li ><a href="{{ $protocolo }}//bo.{{ $url }}"><img src="https://www.countryflags.io/bo/flat/64.png"> <h3>Bolivia</h3></li></a>
                <li ><a href="{{ $protocolo }}//cl.{{ $url }}"><img src="https://www.countryflags.io/cl/flat/64.png"> <h3>Chile</h3></li></a>
                <li ><a href="{{ $protocolo }}//co.{{ $url }}"><img src="https://www.countryflags.io/co/flat/64.png"> <h3>Colombia</h3></li></a>
                <li ><a href="{{ $protocolo }}//cr.{{ $url }}"><img src="https://www.countryflags.io/cr/flat/64.png"> <h3>Costa Rica</h3></li></a>
                <li ><img src="https://www.countryflags.io/ec/flat/64.png"> <h3><a href="{{ $protocolo }}//ec.{{ $url }}">Ecuador</a></h3></li>
                <li ><img src="https://www.countryflags.io/sv/flat/64.png"> <h3><a href="{{ $protocolo }}//sv.{{ $url }}">El Salvador</a></h3></li>
                <li ><img src="https://www.countryflags.io/gt/flat/64.png"> <h3><a href="{{ $protocolo }}//gt.{{ $url }}">Guatemala</a></h3></li>
                <li ><img src="https://www.countryflags.io/hn/flat/64.png"> <h3><a href="{{ $protocolo }}//hn.{{ $url }}">Honduras</a></h3></li>
                <li ><img src="https://www.countryflags.io/mx/flat/64.png"> <h3><a href="{{ $protocolo }}//mx.{{ $url }}">México</a></h3></li>
                <li ><img src="https://www.countryflags.io/ni/flat/64.png"> <h3><a href="{{ $protocolo }}//ni.{{ $url }}">Nicaragua</a></h3></li>
                <li ><img src="https://www.countryflags.io/pa/flat/64.png"> <h3><a href="{{ $protocolo }}//pa.{{ $url }}">Panamá</a></h3></li>
                <li ><img src="https://www.countryflags.io/py/flat/64.png"> <h3><a href="{{ $protocolo }}//py.{{ $url }}">Paraguay</a></h3></li>
                <li ><img src="https://www.countryflags.io/pr/flat/64.png"> <h3><a href="{{ $protocolo }}//pr.{{ $url }}">Puerto Rico</a></h3></li>
                <li ><img src="https://www.countryflags.io/pe/flat/64.png"> <h3><a href="{{ $protocolo }}//pe.{{ $url }}">Perú</a></h3></li>
                <li ><img src="https://www.countryflags.io/do/flat/64.png"> <h3><a href="{{ $protocolo }}//do.{{ $url }}">República Dominicana</a></h3></li>
                <li ><img src="https://www.countryflags.io/uy/flat/64.png"> <h3><a href="{{ $protocolo }}//uy.{{ $url }}">Uruguay</a></h3></li>
                <li ><img src="https://www.countryflags.io/ve/flat/64.png"> <h3><a href="{{ $protocolo }}//ve.{{ $url }}">Venezuela</a></h3></li>

              </ul>

        </div>
      </div>
    </div>
    <div class="footer">
      <img src="/img/techo-logo_200x50.png" alt="">
    </div>


  </body>
</html>
