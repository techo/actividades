<?php
/**
 * Created by PhpStorm.
 * User: johan
 * Date: 31/07/18
 * Time: 16:04
 */

namespace App\Payments;


use Illuminate\Http\Request;

interface PaymentGateway
{
    public function success();

    public function error();

    public function message();

    public function url();

    public function method();

    public function setRequest(Request $request);

    public function updateUserStatus();

}