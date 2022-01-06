<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Payment extends Controller
{
    /**
     * @param {payment_option}
     */
    public function buyCoins(Request $request)
    {
      return view("payment_test");

    }

    public function paymentDetail(Request $request)
    {
        $request->validate([
            "coin"=>"required|integer"
        ]);
        return response()->json(["data"=>[
            "BILL_TO_FORENAME" => \config("values.BILL_TO_FORENAME"),
            "BILL_TO_SURNAME" => \config("values.BILL_TO_SURNAME"),
            "BILL_TO_ADDRESS_LINE1" => \config("values.BILL_TO_ADDRESS_LINE1"),
            "BILL_TO_ADDRESS_CITY" => \config("values.BILL_TO_ADDRESS_CITY"),
            "BILL_TO_ADDRESS_POSTAL_CODE" => \config("values.BILL_TO_ADDRESS_POSTAL_CODE"),
            "BILL_TO_ADDRESS_COUNTRY" => \config("values.BILL_TO_ADDRESS_COUNTRY"),
            "BILL_TO_PHONE" => \config("values.BILL_TO_PHONE"),
            "BILL_TO_EMAIL" => \config("values.BILL_TO_EMAIL"),
        ]],200);
    }


    public function callback(Request $request)
    {

    }




}
