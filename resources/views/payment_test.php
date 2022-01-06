<?php
    function signParams($params, $secretKey){
        $dataToSign = array();
        $signedFieldNames = explode(",",$params["signed_field_names"]);
        foreach ($signedFieldNames as &$field) {
            $dataToSign[] = $field . "=" . $params[$field];
        }
        $joinedData = implode(",",$dataToSign);
        return base64_encode(hash_hmac('sha256', $joinedData, $secretKey, true));
    }


    $sid = sprintf("%06d", rand(0,999999));
    $access_key = \config("values.ACCESS_KEY");
    $profile_id = \config("values.PROFILE_ID");
    $secret_key = \config("values.SECRET_KEY");
    $merchant_id = \config("values.MERCHANT_ID");

    $params = array();
    $params['access_key'] = $access_key;
    $params['profile_id'] = $profile_id;
    $params['transaction_uuid'] = uniqid();
    $params['signed_date_time'] = gmdate("Y-m-d\TH:i:s\Z");
    $params['locale'] = 'en';
    $params['transaction_type'] = 'authorization';
    $params['reference_number'] = (int)(rand(0, 999999));
    $params['currency'] = 'USD';

    $params['device_fingerprint_id'] = $sid;

    $params['amount'] = 100;
    $params['bill_to_address_country'] = "LA";
    $params['bill_to_forename'] = \config("values.BILL_TO_FORENAME");
    $params['bill_to_surname'] = \config("values.BILL_TO_SURNAME");
    $params['bill_to_email'] = \config("values.BILL_TO_EMAIL");
    $params['bill_to_phone'] = \config("values.BILL_TO_PHONE");
    $params['bill_to_address_city'] = \config("values.BILL_TO_ADDRESS_CITY");
    $params['bill_to_address_line1'] = \config("values.BILL_TO_ADDRESS_LINE1");
    $params['bill_to_address_postal_code'] = \config("values.BILL_TO_ADDRESS_POSTAL_CODE");
    $params['merchant_secure_data1'] = "special message 1";
    $params['merchant_secure_data2'] = "special data 2";
    $params['merchant_secure_data3'] = "special data 3";

    $params['signed_field_names'] = '';
    $params['signed_field_names'] = implode(',', array_keys($params));

    $params['signature'] = signParams($params, $secret_key);
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta http-equiv="X-UA-Compatible" content="ie=edge">
	<title>Payment Testing</title>
	<style>
        *{
            font-family: "Consolas", monospace;
        }
		body{
			width: 100%;
			height: 100%;
			padding: 0;
			margin: 0;
            background-color: #eee;
		}
		.container{
			width: 40vw;
			height: auto;
			margin: auto;
			background-color: #fff;
            border: 1px solid #ddd;
            border-top: 8px solid #c6332a;
            top: 0px;
            position: relative;
            box-shadow: 0 3px 5px #ccc;
            text-align: center;
            padding-bottom: 20px;
		}
        h2, h3{
            padding: 0;
            margin: 0;
            color: #333;
        }
        .title{
            padding: 20px 0;
            color: #333;
        }
        .border{
            border-bottom: 2px solid #c6332a;
            width: 40%;
            margin: auto;
        }
        .btn-payment{
            border: none;
            outline: none;
            padding: 14px 40px;
            color: #fff;
            background-color: #c6332a;
            border-radius: 3px;
            font-size: 15px;
            cursor: pointer;
            box-shadow: 0 3px 3px #ccc;
            margin-top: 20px;
        }
        .btn-payment:hover{
            background-color: #b12d27;
        }
        .details{
            padding: 20px;
        }
        p {
            padding: 1px 0;
            line-height: 0.4rem;
        }
        .amount{
            padding-top: 30px;
        }
	</style>
</head>
<body>
    <div class="container">
        <div class="title">
            <h1>Payment Testing</h1>
            <div class="border"></div>
        </div>
        <div class="content">
            <form action='https://testsecureacceptance.cybersource.com/oneclick/pay' method='post'>
                <!-- display payment details -->
                <div class="details">
                    <div>
                        <h3>Billing Information</h3>
                        <div>
                            <p><?=$params['bill_to_forename']?> <?=$params['bill_to_surname']?></p>
                            <p><?=$params['bill_to_address_line1']?>, <?=$params['bill_to_address_city']?>, <?=$params['bill_to_address_postal_code']?> <?=$params['bill_to_address_country']?></p>
                            <p><?=$params['bill_to_phone']?></p>
                            <p><?=$params['bill_to_email']?></p>
                        </div>
                    </div>
                    <div class="amount">
                        <h3>Amount: <?=number_format($params['amount'])?> <span style="text-transform: uppercase;"><?=$params['currency']?></span></h3>
                    </div>
                </div>
                <!-- end display payment details -->
                <!-- data required for submit form -->
                <?php
                    foreach ($params as $key => $val){
                        echo "<input type='hidden' name='$key' value='$val' />\t\n";
                    }
                ?>
                <!-- end data required for submit form -->
                <input class='btn-payment' type='submit' value='Make Payment'/>
            </form>
        </div>
        <p style="background:url(https://h.online-metrix.net/fp/clear.png?org_id=k8vif92e&amp;session_id=<?=$merchant_id . $sid?>&amp;m=1)"></p>
        <img src="https://h.online-metrix.net/fp/clear.png?org_id=k8vif92e&amp;session_id=<?=$merchant_id . $sid?>&amp;m=2" alt="">
        <object type="application/x-shockwave-flash" data="https://h.onlinemetrix.net/fp/fp.swf?org_id=k8vif92e&amp;session_id=<?=$merchant_id . $sid?>" width="1" height="1" id="thm_fp">
            <param name="movie" value="https://h.online-metrix.net/fp/fp.swf?org_id=k8vif92e&amp;session_id=<?=$merchant_id . $sid?>"/>
            <div></div>
        </object>
         <script src="https://h.online-metrix.net/fp/check.js?org_id=k8vif92e&amp;session_id=<?=$merchant_id . $sid?>" type="text/javascript"></script>
    </div>
</body>
</html>
