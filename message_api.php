<?php

class Messages
{

    // set instance id and access_token
    private $instance_id = 'xxxx';
    private $access_token = 'xxxxx';


    //@desc
    //send text message
    public function sendMessage($msg, $num)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://yourdomain.com/api/send.php?instance_id=' . $this->instance_id . '&access_token=' . $this->access_token . '&type=json&number=' . $num . '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "text": "' . $msg . '"
            }',
        )
        );

        $response = curl_exec($curl);
        curl_close($curl);
    }



    //@desc
    //send text button message
    public function sendBtnMessage($msg, $num, $btn1, $btn2, $btn3, $btnid1, $btnid2, $btnid3)
    {

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => 'https://yourdomain.com/api/send.php?number=' . $num . '&type=json&instance_id=' . $this->instance_id . '&access_token=' . $this->access_token . '',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => '{
                "text": "' . $msg . '",
                "footer": "select option",
                "buttons": [
                    {"buttonId": "' . $btnid1 . '", "buttonText": {"displayText": "' . $btn1 . '"}},
                    {"buttonId": "' . $btnid2 . '", "buttonText": {"displayText": "' . $btn2 . '"}},
                    {"buttonId": "' . $btnid3 . '", "buttonText": {"displayText": "' . $btn3 . '"}}
                ]
            }',
        )
        );

        $response = curl_exec($curl);
        curl_close($curl);
    }



    //@desc
    //send media message
    public function sendMediaMessage($msg, $num)
    {

        $ch = curl_init();
        $url = "https://yourdomain.com/api/send.php";
        $dataArray = [
            "number" => $num,
            "type" => "media",
            "media_url" => "https://www.shareicon.net/data/2015/10/28/663316_resume_512x512.png",
            "message" => $msg,
            "instance_id" => $this->instance_id,
            "access_token" => $this->access_token
        ];

        $data = http_build_query($dataArray);
        $getUrl = $url . "?" . $data;

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 80);

        $response = curl_exec($ch);
        curl_close($ch);
    }



    //@desc
    //whatsapp number validation
    public function checkNumber($num)
    {

        $ch = curl_init();
        $url = "https://yourdomain.com/api/send.php";
        $dataArray = [
            "number" => $num,
            "type" => 'check_phone',
            "instance_id" => $this->instance_id,
            "access_token" => $this->access_token,
        ];

        $data = http_build_query($dataArray);
        $getUrl = $url . "?" . $data;

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_URL, $getUrl);
        curl_setopt($ch, CURLOPT_TIMEOUT, 80);

        $response = curl_exec($ch);
        $obj = json_decode($response);
        $sts = $obj->Status;

        if ($obj->Message == "available") {
            $res = "available";
        } else if ($obj->Message == "not available") {
            $res = "not available";
        } else {
            $res = "not";
        }

        curl_close($ch);
        return $res;
    }

}

?>