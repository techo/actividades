<?php

function convertirCondicion($comparacion, $valor)
{
    switch ($comparacion){
        case 'contiene':
            return [
                'comparacion' => 'like',
                'valor' => '%'.$valor.'%'
            ];
            break;
        case 'mayor que':
            return [
                'comparacion' => '>',
                'valor' => $valor
            ];
            break;
        case 'mayor o igual que':
            return [
                'comparacion' => '>=',
                'valor' => $valor
            ];
            break;
        case 'menor que':
            return [
                'comparacion' => '<',
                'valor' => $valor
            ];
            break;
        case 'menor o igual que':
            return [
                'comparacion' => '<=',
                'valor' => $valor
            ];
            break;
        case 'igual a':
            return [
                'comparacion' => '=',
                'valor' => $valor
            ];
            break;
        case 'distinto de':
            return [
                'comparacion' => '<>',
                'valor' => $valor
            ];
            break;
        case 'está en lista':
            return [
                'comparacion' => 'in',
                'valor' => array_map('trim', explode(',', $valor))
            ];
            break;
        default:
            return false;
    }
}