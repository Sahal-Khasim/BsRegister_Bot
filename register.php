<?php
$data = file_get_contents("php://input");
$event = json_decode($data);

require_once(__DIR__ . "/data.php");
include './message_api.php';


$message_api = new Messages();


$phone_array = array();

$result = mysqli_query($con, "SELECT phone FROM table");
while ($res = mysqli_fetch_array($result)) {
    $phone = $res['phone'];
    array_push($phone_array, $phone);
}



if (isset($event)) {

    var_dump($event);

    if ($event->event == "messages.upsert") {
        $lattitude = $event->data->messages[0]->message->{'locationMessage'}->degreesLatitude;
        $longitude = $event->data->messages[0]->message->{'locationMessage'}->degreesLongitude;
        $jid = $event->data->messages[0]->key->{'remoteJid'};
        $buttonid = $event->data->messages[0]->message->{'buttonsResponseMessage'}->selectedButtonId;
        $number = (int) filter_var($jid, FILTER_SANITIZE_NUMBER_INT);
        $from = $event->data->messages[0]->key->{'fromMe'};
        $message = $event->data->messages[0]->message->{'conversation'};


        if (!in_array($number, $phone_array)) {
            if ($buttonid == "business_register") {
                mysqli_query($con, "INSERT INTO table (`phone`, `active_btn_id`) VALUES ('$number', '$buttonid')") or die(mysqli_error($con));
                $message_api->sendMessage('HI, What is your business name?', $number);
            } else if ($buttonid == "search_business" || strcasecmp($message, "Search business") == 0) {
                if ($message == "Search business") {
                    $buttonid = "search_business";
                    $message = "";
                }
                mysqli_query($con, "INSERT INTO table (`phone`, `active_btn_id`) VALUES ('$number', '$buttonid')") or die(mysqli_error($con));
                $message_api->sendMessage('HI, Please enter a keyword to search?', $number);
            }
        } else {
            $message = $event->data->messages[0]->message->{'conversation'};
            if ($buttonid == "business_register" || $buttonid == "search_business" || strcasecmp($message, "Search business") == 0) {
                if (strcasecmp($message, "Search business") == 0) {
                    $buttonid = "search_business";
                    $message = "";
                }
                mysqli_query($con, "UPDATE table SET active_btn_id='$buttonid' WHERE phone='$number'") or die(mysqli_error($con));
            }


            $result = mysqli_query($con, "SELECT active_btn_id FROM table WHERE phone='$number'");
            while ($res = mysqli_fetch_array($result)) {
                $active_btn_id = $res['active_btn_id'];
            }


            if ($active_btn_id == "business_register") {
                $result = mysqli_query($con, "SELECT * FROM table WHERE phone='$number'");
                while ($res = mysqli_fetch_array($result)) {
                    $name = $res['name'];
                    $contact = $res['contact'];
                    $category = $res['category'];
                    $keyword = $res['keyword'];
                    $latt = $res['lattitude'];
                    $long = $res['longitude'];
                    $search_keyword = ['search_keyword'];
                }


                if ($from != 'false') {
                    if ($name == "") {
                        $message = $event->data->messages[0]->message->{'conversation'};
                        if (!empty($message)) {
                            mysqli_query($con, "UPDATE table SET name='$message' WHERE phone='$number'") or die(mysqli_error($con));
                            $message_api->sendMessage('What is your business category?', $number);
                        } else {
                            $message_api->sendMessage('HI, What is your business name?', $number);
                        }
                    } else if (empty($category)) {

                        $message = $event->data->messages[0]->message->{'conversation'};
                        if (!empty($message)) {
                            mysqli_query($con, "UPDATE table SET category='$message' WHERE phone='$number'") or die(mysqli_error($con));
                            $message_api->sendMessage('Please enter 5 different table2 that can help your customers to get products and services that you serve             ' . '\n' . '\n' . 'Eg:' . '\n' . '\n' . 'Rice,wheat,sugar ( enter each word followed by a comma', $number);
                        }

                    } else if (empty($keyword)) {

                        $message = $event->data->messages[0]->message->{'conversation'};
                        if (!empty($message)) {
                            $table2 = explode(",", $message);
                            if (count($table2) <= 5) {
                                mysqli_query($con, "UPDATE table SET keyword='$message' WHERE phone='$number'") or die(mysqli_error($con));
                                foreach ($table2 as $keys) {
                                    $rm_space = trim($keys, " ");
                                    mysqli_query($con, "INSERT INTO table2 (`phone`, `keyword`) VALUES ('$number', '$rm_space')") or die(mysqli_error($con));
                                }
                                $message_api->sendMessage('What is your business contact number?', $number);
                            } else {
                                $message_api->sendMessage('Please enter only 5 table2?', $number);
                            }
                        }

                    } else if (empty($contact)) {

                        $message = $event->data->messages[0]->message->{'conversation'};
                        if (!empty($message)) {
                            $valid = $message_api->checkNumber("91" . $message);
                            if ($valid == "available") {
                                mysqli_query($con, "UPDATE table SET contact='$message' WHERE phone='$number'") or die(mysqli_error($con));
                                $message_api->sendMessage('Share your business location?', $number);
                            } else {
                                $message_api->sendMessage('' . $message . ' is not a whatsapp number', $number);
                            }
                        }

                    } else if (empty($latt) && empty($long)) {

                        if (!empty($lattitude) && !empty($longitude)) {
                            mysqli_query($con, "UPDATE table SET lattitude='$lattitude', longitude=$longitude WHERE phone='$number'") or die(mysqli_error($con));

                            mysqli_query($con, "UPDATE table2 SET latitude='$lattitude', longitude=$longitude WHERE phone='$number'") or die(mysqli_error($con));

                            $message_api->sendMessage('Thank you for registering your business', $number);
                        } else {
                            $message_api->sendMessage('Please share your business location!', $number);
                        }

                    } else {
                        if ($buttonid == "business_register") {
                            $message_api->sendBtnMessage('Thank you for registering your business', $number, 'Register Again', 'Search business', 'Show Details', 'business_register', 'search_business', 'show_detail');
                        } else if ($buttonid == "show_detail") {

                            $result = mysqli_query($con, "SELECT * FROM table WHERE phone='$number'");
                            while ($res = mysqli_fetch_array($result)) {

                                $contact_number = $res['contact'];
                                $category = $res['category'];
                                $name = $res['name'];
                                $services = $res['keyword'];

                                $msg = "Business Name : *$name*" . "\n" . "Category : *$category*" . "\n" . "Contact : *$contact_number*" . "\n" . "Services : *$services*" . "\n";

                                $message_api->sendMediaMessage($msg, $number);

                            }
                        }
                    }
                }



            } else if ($active_btn_id == "search_business") {

                if ($from != 'false') {

                    if ($buttonid == "search_business") {
                        $message_api->sendMessage('HI, Please enter a keyword to search?', $number);
                    }

                    $search_query = mysqli_query($con, "SELECT * FROM table WHERE phone='$number'");
                    while ($res = mysqli_fetch_array($search_query)) {
                        $search_keyword = $res['search_keyword'];
                    }


                    if (!empty($message)) {

                        mysqli_query($con, "UPDATE table SET search_keyword='$message' WHERE phone='$number'") or die(mysqli_error($con));
                        $message_api->sendMessage('Please send your current location!', $number);

                    } elseif (!empty($lattitude) && !empty($longitude) && !empty($search_keyword)) {

                        $result = mysqli_query($con, "SELECT  `phone`, `keyword`, `latitude`, `longitude`, ( 6367 * acos( cos( radians($lattitude) ) * cos( radians( latitude ) ) * cos( radians( longitude ) - radians($longitude) ) + sin( radians($lattitude) ) * sin( radians( latitude ) ) ) ) AS distance FROM table2 WHERE keyword = '$search_keyword' order by distance limit 5");
                        if (mysqli_num_rows($result) == 0) {
                            $message_api->sendMessage('Sorry, invalid keyword please search another keyword!?', $number);
                        } else {
                            $key_array = array();

                            while ($res = mysqli_fetch_array($result)) {

                                $key = $res['keyword'];
                                $ph = $res['phone'];

                                array_push($key_array, $ph);
                            }

                            $flag = "1";

                            foreach ($key_array as $key_ph) {

                                $result = mysqli_query($con, "SELECT * FROM table WHERE phone='$key_ph'");
                                while ($res = mysqli_fetch_array($result)) {

                                    $contact_number = $res['contact'];
                                    $category = $res['category'];
                                    $name = $res['name'];
                                    $services = $res['keyword'];

                                    $lat1 = $lattitude;
                                    $lon1 = $longitude;
                                    $lat2 = $res['lattitude'];
                                    $lon2 = $res['longitude'];

                                    $earthRadius = 3958.75;

                                    $dLat = deg2rad($lat2 - $lat1);
                                    $dLng = deg2rad($lon2 - $lon1);

                                    $a = sin($dLat / 2) * sin($dLat / 2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) * sin($dLng / 2);
                                    $c = 2 * atan2(sqrt($a), sqrt(1 - $a));
                                    $dist = $earthRadius * $c;

                                    // from miles
                                    $meterConversion = 1609;
                                    $geopointDistance = $dist * $meterConversion;

                                    $geopointDistance = (int) $geopointDistance;

                                    $dist = $geopointDistance . " mts";

                                    if ($geopointDistance > 1000) {
                                        $gpDistance = round($geopointDistance / 1000);
                                        $dist = $gpDistance . " kms";
                                    } else if ($geopointDistance > 500) {
                                        $dist = "more than 500 mts";
                                    } else if ($geopointDistance < 500) {
                                        $dist = "less than 500 mts";
                                    }

                                    if ($geopointDistance < 5000) {

                                        $flag = "0";

                                        $msg = "Business Name : *$name*" . "\n" . "Category : *$category*" . "\n" . "Contact : *$contact_number*" . "\n" . "Services : *$services*" . "\n" . "Distance : *$dist*" . "\n";

                                        $message_api->sendMediaMessage($msg, $number);

                                    }
                                }
                            }

                            if ($flag == "1") {
                                $flag = "0";
                                $message_api->sendMessage('Sorry no businesses available in your location!', $number);
                            }

                            mysqli_query($con, "UPDATE table SET active_btn_id='' WHERE phone='$number'") or die(mysqli_error($con));
                        }
                    }



                    $file = 'log.txt';
                    $data = json_encode($event) . "\n";
                    file_put_contents($file, $data, FILE_APPEND | LOCK_EX);
                }
            }
        }
    }
}

?>