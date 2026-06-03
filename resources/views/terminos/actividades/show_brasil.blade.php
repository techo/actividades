@extends('main')

@section('page_title')
    Carta de voluntariado
@endsection

@section('main_content')
    <div class="row d-flex justify-content-center">
        <img src="/img/logo_n.png" height="70" alt="logo techo">
    </div>
    <div class="row">
        <div class="col-md-8 offset-md-2 mt-5 text-justify">
            <h2 class="text-center">TERMO DE ADESÃO AO TRABALHO VOLUNTÁRIO PONTUAL</h2>
            <br>
            <p>Os nomes infra assinados, doravante denominados VOLUNTÁRIO/A, e a organização UM TETO PARA MEU PAÍS – BRASIL, inscrita no CNPJ/MF sob o nº 10.513.214/0001-15, doravante chamada de TETO,
            @if($actividad && $actividad->oficina)
                com sede em {{ $actividad->oficina->nombre }},
            @else
                com sede no Brasil,
            @endif
            nos termos da Lei no. 9.608/1998 ("Lei do Voluntariado"), resolvem firmar o presente Termo de Adesão ao Serviço Voluntário Pontual, a ser regido conforme as seguintes cláusulas e condições:
            </p>
            <ul>
                <li>O objeto da prestação do presente serviço voluntário é
                @if($actividad)
                    a atividade chamada "{{ $actividad->nombreActividad }}", promovida pela sede da TETO
                    @if($actividad->oficina) {{ $actividad->oficina->nombre }}@endif,
                    a ser realizada
                    @if($actividad->localidad) na cidade de {{ $actividad->localidad->localidad }}@endif,
                @else
                    a atividade promovida pela TETO,
                @endif
                sem qualquer tipo de remuneração ou benefício pelos serviços prestados.
                </li>
                <li>As PARTES têm conhecimento de que a prestação de serviços ora proposta tem caráter voluntário e não gera vínculo empregatício, nem obrigação de natureza trabalhista, previdenciária ou afim, conforme disposto no parágrafo único do artigo 1º da Lei nº 9.608/98.</li>
                <li>O presente Termo de Adesão têm validade
                @if($actividad && $actividad->fechaInicio && $actividad->fechaFin)
                    do início do dia {{ $actividad->fechaInicio->format('d/m/Y') }} até o fim do dia {{ $actividad->fechaFin->format('d/m/Y') }},
                @else
                    durante o período da atividade,
                @endif
                sendo que seu término poderá efetivar-se pela vontade de quaisquer das PARTES, quando da ausência injustificada do(a) voluntário(a), sem qualquer ônus, ao término das atividades a serem desenvolvidas ou ao fim do prazo de validade.
                </li>
                <li>O/A VOLUNTÁRIO/A compromete-se a manter absoluto sigilo acerca de quaisquer documentos, informações ou dados que tiverem acesso por força dos exercícios das atividades objeto do presente Termo de Adesão.</li>
                <li>O/A VOLUNTÁRIO/A concede a TETO o direito de propriedade de todo e qualquer material produzido por ele/a durante a execução e vigência deste Termo.</li>
                <li>O/A VOLUNTÁRIO/A compromete-se a respeitar o direito de imagem das pessoas presentes nas atividades, especialmente crianças, adolescentes e moradores das comunidades. É vedado tirar, obter, ceder, utilizar, compartilhar ou divulgar fotos e/ou vídeos sem autorização ou de forma que exponha indevidamente as pessoas fotografadas ou filmadas antes, durante ou após a execução das atividades.</li>
                <li>Caso descumprido o acordado acima, pelo presente instrumento, o/a VOLUNTÁRIO/A isenta a TETO de quaisquer responsabilidades sobre uso indevido de imagem, fotografia ou vídeo, que por ventura venham a ser contestados pelos seus reais detentores de direito ou entidades de representação públicas ou privadas, jurídicas ou não, além de estar ciente de que o descumprimento pode acarretar em seu afastamento da atividade, bem como da TETO.</li>
                <li>O/A VOLUNTÁRIO/A concede à TETO, a título gratuito, licença para utilização e reprodução de suas imagens captadas, bem como do conteúdo de qualquer alocução proferida, para fins de publicação, reprodução e comunicação ao público, e por qualquer meio ou processo, com o fim de divulgar o projeto, pelo prazo de 05 (cinco) anos.</li>
                <li>O/A VOLUNTÁRIO/A está ciente de que as atividades são realizadas em áreas de risco, em áreas de difícil acesso ou afastadas de centros médicos, e que ele/ela poderá incorrer em perigos, e isenta a TETO de qualquer responsabilidade referente a acidentes pessoais ou materiais que por ventura ocorram no desempenho de suas atividades nas sedes da TETO ou em qualquer local ou comunidade na qual a TETO desenvolva seu trabalho, cabendo ao/à voluntário/a zelar por sua integridade física e seus pertences.</li>
                <li>A TETO fornece seguro da Porto Seguro Seguradora, com cobertura durante os dias do evento. O seguro cobre morte acidental, com capital segurado de R$ 15.000,00, bem como despesas médicas, hospitalares e odontológicas, até o limite de R$ 3.000,00, desde que decorrentes de acidente pessoal coberto pelo seguro. Em caso de despesas médicas, o reembolso será realizado conforme as regras, prazos e documentação exigidos pela Porto Seguro.</li>
                <li>Será de inteira responsabilidade do/a VOLUNTÁRIO/A qualquer dano ou prejuízo que vier a causar à TETO.</li>
                <li>As PARTES elegem o foro da Comarca de São Paulo para dirimir dúvidas relativas ao presente termo de adesão.</li>
            </ul>

            <br>
            <hr>
            <h5>Lei do Voluntariado nº 9.608, de 18.02.98</h5>
            <p>Dispõe sobre o serviço voluntário e dá outras providências.</p>
            <p><strong>Art. 1º</strong> – Considera-se serviço voluntário, para fins desta Lei, a atividade não remunerada, prestada por pessoa física a entidade pública de qualquer natureza ou instituição privada de fins não lucrativos, que tenha objetivos cívicos, culturais, educacionais, científicos, recreativos ou de assistência social, inclusive mutualidade. <em>Parágrafo único:</em> O serviço voluntário não gera vínculo empregatício nem obrigação de natureza trabalhista, previdenciária ou afim.</p>
            <p><strong>Art. 2º</strong> – O serviço voluntário será exercido mediante a celebração de termo de adesão entre a entidade, pública ou privada, e o prestador do serviço voluntário, dele devendo constar o objeto e as condições do seu serviço.</p>
            <p><strong>Art. 3º</strong> – O prestador do serviço voluntário poderá ser ressarcido pelas despesas que comprovadamente realizar no desempenho das atividades voluntárias. <em>Parágrafo único:</em> As despesas a serem ressarcidas deverão estar expressamente autorizadas pela entidade a que for prestado o serviço voluntário.</p>
            <p><strong>Art. 4º</strong> – Esta lei entra em vigor na data de sua publicação.</p>
            <p><strong>Art. 5º</strong> – Revogam-se as disposições em contrário.</p>

        </div>
    </div>
@endsection

@section('footer')
    @include('partials.footer')
@endsection
