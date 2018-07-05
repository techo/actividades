<?php

function convertirCondicion($comparacion, $valor)
{
    switch ($comparacion){
        case 'like':
            return '%'.$valor.'%';
            break;
        case 'in':
            return array_map('trim', explode(',', $valor));
            break;
        default:
            return $valor;
    }
}

function quote($valor)
{
    return "'" . $valor . "'";
}

function clean_string($string){
    $string = strip_tags($string);
    $string = html_entity_decode($string);

    return $string;
}