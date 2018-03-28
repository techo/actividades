<html>
<head>
    <title>Welcome Email</title>
</head>
 
<body>
<h2>Welcome to the site {{$persona['nombres']}}</h2>
<br/>
Your registered email-id is {{$persona['mail']}} , Please click on the below link to verify your email account
<br/>
<a href="{{url('usuario/verificar_mail', $persona->verificacion->token)}}">VerificarMail</a>
</body>
 
</html>