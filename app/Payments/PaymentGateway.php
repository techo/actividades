<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 31/07/18
 * Time: 16:04
 */

namespace App\Payments;


interface PaymentGateway
{
    public function success();

    public function error();

    public function message();

    public function url();

    public function method();

}