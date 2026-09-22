<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Telas de erro
    |--------------------------------------------------------------------------
    | Tom TECHO: humano, tranquilizador e com uma saída clara.
    */

    'back_home' => 'Voltar ao início',
    'code'      => 'Erro :n',

    'e500' => [
        'title'       => 'Algo deu errado',
        'message'     => 'Tivemos um problema do nosso lado e não conseguimos concluir o que você estava fazendo. Já está registrado e estamos verificando. Tente novamente em instantes.',
        'report'      => 'Relatar este problema',
        'report_hint' => 'Como você coordena/administra o sistema, pode relatar para que a equipe verifique.',
    ],

    'e404' => [
        'title'   => 'Não encontramos esta página',
        'message' => 'A página que você procura não existe ou foi movida. Confira o endereço ou volte ao início.',
    ],

    'e403' => [
        'title'   => 'Você não tem acesso a isto',
        'message' => 'Esta seção exige permissões que a sua conta não tem. Se você acha que é um engano, fale com a pessoa que coordena a sua equipe.',
    ],

    'e503' => [
        'title'   => 'Estamos em manutenção',
        'message' => 'Estamos fazendo uma atualização rápida do sistema. Tente novamente em alguns minutos. Obrigado pela paciência!',
    ],

    'e419' => [
        'title'     => 'A página expirou',
        'message'   => 'A sua sessão expirou porque a página ficou aberta por muito tempo. Você não perdeu nada: vamos levar você de volta para continuar.',
        'retry'     => 'CONTINUAR',
        'countdown' => 'Redirecionando em :seconds…',
    ],

];
