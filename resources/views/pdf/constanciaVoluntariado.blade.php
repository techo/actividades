@extends('main')

@section('page_title')
    CONSTANCIA DE VOLUNTARIADO
@endsection

@section('styles')
    <style>
        h4 {
            font-family: 'MontserratRegular', sans-serif;
        }
    </style>
@endsection

@section('main_content')
    <div class="row d-flex justify-content-center">
        <img src="img/techo_logo_big.png" height="60" alt="logo techo">
    </div>
    <div class="row">
        <div class="mt-5 text-justify">
            <h4 class="text-center">CONSTANCIA DE VOLUNTARIADO</h4>
            <br>
            <p>
                TECHO es una organización liderada por jóvenes voluntarios que trabajan en 18 países de Latinoamérica y el Caribe, 
                Estados Unidos y Europa. Para superar la situación de pobreza, desarrollamos proyectos que mejoren las condiciones 
                de hábitat y habitabilidad de las familias, con el fin de una mejor calidad de vida, donde todas las personas tengan 
                las oportunidades para desarrollar sus capacidades y puedan ejercer y gozar plenamente de sus derechos y deberes. 
            </p>
            <p>
                Mediante la presente, TECHO certifica que la persona 
                    {{ $persona->getNombreCompletoAttribute() }}, identificada con DNI 
                    {{ $persona->dni }}, ha desempeñado el rol de voluntariado en las siguientes actividades:
            </p>
            <ul>
                @foreach ($inscripciones as $inscripcion)
                    @if($inscripcion->actividad != null)
                    <li>
                        {{ $inscripcion->actividad->nombreActividad }} el {{ $inscripcion->actividad->fechaInicio->format('d/m/Y') }}
                    </li>
                    @endif
                @endforeach
            </ul>
            @if(count($integrantes) > 0)
            <p>
                Destacamos su participación como miembro de nuestro voluntariado permanente en
            </p>
           
                @foreach ($integrantes as $integrante)
                <ul>
                <li>
                    El equipo 
                        {{ $integrante->equipo->nombre }}, 
                    desempeñándose como 
                        {{ $integrante->rol }}
                    entre las fechas
                        {{ $integrante->fechaInicio->format('m/Y') }}
                    @if($integrante->fechaFin)
                        y {{ $integrante->fechaFin->format('m/Y') }}
                    @else
                        y actual
                    @endif

                </li>
                </ul>
                @endforeach
            @endif
            <p>
                Extendemos la presente constancia para que la persona interesada la utilice según lo considere necesario.
            </p>
  
            <p  class="text-right">{{ Carbon\Carbon::now()->format('d/m/Y') }}</p>
            <!-- firma de la persona -->


        </div>
    </div>
@endsection

@section('footer')
    
@endsection