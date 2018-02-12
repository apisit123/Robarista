<?php
require_once '../vendor/autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
$logger = new Logger('LineBot');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient($_ENV["LINEBOT_ACCESS_TOKEN"]);
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => $_ENV["LINEBOT_CHANNEL_SECRET"]]);
$signature = $_SERVER['HTTP_' . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];

$strAccessToken = "Your LINEBot access token ";
$img_url = array("https://www.picz.in.th/images/2018/01/30/AMERICANO.jpg", "https://www.picz.in.th/images/2018/01/30/ESPRESSO.jpg" , "https://www.picz.in.th/images/2018/01/30/set-a-latte-macchiato-220-ml.jpg" , "https://www.picz.in.th/images/2018/01/30/CHOCOCINO.jpg", "https://www.picz.in.th/images/2018/01/30/GREEN-TEA.jpg");
$coffee = array("Americano", "Espresso", "Latte Macchiato", "Chococino", "Green tea");
$c_g_1 = array("AMERICANO", "ESPRESSO");
$c_g_2 = array("LATTE MACCHIATO", "CHOCOCINO", "GREEN TEA");


try {
	$events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
	error_log('parseEventRequest failed. InvalidSignatureException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
	error_log('parseEventRequest failed. UnknownEventTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
	error_log('parseEventRequest failed. UnknownMessageTypeException => '.var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
	error_log('parseEventRequest failed. InvalidEventRequestException => '.var_export($e, true));
}
foreach ($events as $event) {
	// Postback Event

	if (($event instanceof \LINE\LINEBot\Event\PostbackEvent)) {
		//$logger->info('Postback message has come');
		$text = $event->getPostbackData();
		$coffee_check = array("AMERICANO", "ESPRESSO", "LATTE-MACCHIATO", "CHOCOCINO", "GREEN-TEA");
		$piece = explode("|", $text);
		$piece[0] = strtoupper($piece[0]);
		$xp = str_replace(" ","-",$piece[0]); 
		

		if (in_array($xp, $coffee_check)){

		if($xp == "LATTE-MACCHIATO"){
			$xp = "set-a-latte-macchiato-220-ml";
		}

		$actions = array (
				// general message acti
				New \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("yes", "Order[{$piece[0]}|Yes]{$piece[1]}"),
				New \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder("no", "Order[x|No]")
			);
			$button = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\ButtonTemplateBuilder("Confirm order?", $piece[0], "https://www.picz.in.th/images/2018/01/30/".$xp.".jpg", $actions);
			$outputText = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("this message to use the phone to look to the Oh", $button);
			$response = $bot->replyMessage($event->getReplyToken(), $outputText);
		}

		if(strpos($text, 'Yes') !== false) {
			$_uid = explode("]", $text);

			$_axces = file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&q={"UserId":"'.$_uid[1].'"}&c=true');

			if($_axces < 3 || $_uid[1] == "U8d468e687e2a830ecf7006126484638c" || $_uid[1] == "Uf7024ae966a267eab0a9f5b82444ea6c"){
	
      			$x_tra = str_replace("Order","", $text);
		      	$pieces = explode("|", $x_tra);
		      	$_coffee=str_replace("[","",$pieces[0]);

			    $response = $bot->getProfile($_uid[1]);
				if ($response->isSucceeded()) {
				    $profile = $response->getJSONDecodedBody();
				    $_dispName = $profile['displayName'];
				    $_imgProFILE = $profile['pictureUrl'];
				}
		      	

		      	$_successOrder = file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/currentValue?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&c=true');
		      	$sk = json_decode(file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&s={%22No%22:1}&sk='.$_successOrder),true);
		      	$cancelOrder = file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/cancelOrder?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&c=true');


   		        
   		        $out = [];

   		        foreach($sk as $element) {
        			$out[$element['group']][] = ['Coffee' => $element['Coffee']];

				}
   		        
   		        $a = json_encode($out);

   		        $asas = json_decode($a);

   		        $g1 = sizeof($asas->G1);
   		        $g2 = sizeof($asas->G2);
   		        
   		        if (in_array($_coffee, $c_g_1)){
		      		$g1 += 1;
		      		$c_type = "G1";
		      	}elseif (in_array($_coffee, $c_g_2)) {
		      		$g2 += 1;
		      		$c_type = "G2";
		      	}

		      	$_buffer = file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&c=true');
		      	$_no = $_buffer + $cancelOrder + 1;

		      	$date = new DateTime();
		      	$timestamp = $date->getTimestamp();
       	      	$newData = json_encode(
		        array(
		          "No" => $_no,
		          'UserId' => $_uid[1],
		          'Coffee' => $_coffee,
		          'PicProfile' => $_imgProFILE,
		          'Name' => $_dispName,
		          'group' => $c_type,
		          'date' => $timestamp

		        )
		      );
		      	$opts = array(
		        	'http' => array(
		            'method' => "POST",
		            'header' => "Content-type: application/json",
		            'content' => $newData
		         )
		      );    
		      $context = stream_context_create($opts);
		      $returnValue = file_get_contents("https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA",false,$context);

		      $array = json_decode(file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&s={date:1}'));
		      $index = array_search($timestamp, array_keys($array));

		      $w_time = ($g1*180)+($g2*300);
		      $mm = $w_time/60;
		      $ss = $w_time%60;

		    $outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Order recieve\nYour order number {$index}\nPlease wait about {$mm}.{$ss} minute");		
			$response = $bot->replyMessage($event->getReplyToken(), $outputText);
			}elseif ($_axces >= 30) {
				$outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("ไม่ควรดื่มเกินวันละ 3 แก้ว!");	
				$response = $bot->replyMessage($event->getReplyToken(), $outputText);
			}
		}else{
			$outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Thank you :)");	
			$response = $bot->replyMessage($event->getReplyToken(), $outputText);
			break;
		}
		
		continue;
	}

	// Message Event = TextMessage
	if (($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
		$messageText=strtolower(trim($event->getText()));

		$_uid = $event->getUserId();

		switch ($messageText) {
			
			case "menu" :
				$columns = array();
				for($i=0;$i<5;$i++) {
					$actions = array(
						if
						new \LINE\LINEBot\TemplateActionBuilder\PostbackTemplateActionBuilder($orderNow[$i],$coffee[$i]."|".$_uid),
					);
					$column = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselColumnTemplateBuilder($coffee[$i], " ", $img_url[$i] , $actions);
					$columns[] = $column;
				}
				$carousel = new \LINE\LINEBot\MessageBuilder\TemplateBuilder\CarouselTemplateBuilder($columns);
				$outputText = new \LINE\LINEBot\MessageBuilder\TemplateMessageBuilder("Carousel Demo", $carousel);
				$response = $bot->replyMessage($event->getReplyToken(), $outputText);
				break;

			default :
				$outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder("Please try again.");	
				$response = $bot->replyMessage($event->getReplyToken(), $outputText);
				break;
		}
		if ($_uid == "U8d468e687e2a830ecf7006126484638c" || $_uid == "Uf7024ae966a267eab0a9f5b82444ea6c" || $_uid == "U6211216e572f1f6bbaa11ec4e733a7ff") {
			# code...
			if(strpos($messageText, 'delete') !== false){

				$del = explode("|", $messageText);

				$get_id = file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&q={"No":'.$del[1].'}&f={_id:1}');
				$json = json_encode($get_id);
				$data =  substr($json,30,-7);

				$del_doc = shell_exec('curl -X DELETE https://api.mlab.com/api/1/databases/tstdb/collections/linebot/'.$data.'?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA');
			
				//$logger->info($data);
				if(strlen($del_doc) >= 150){
					$input = shell_exec('curl -d \'{"data":"x"}\' -H "Content-Type: application/json" -X POST https://api.mlab.com/api/1/databases/tstdb/collections/cancelOrder?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA');			

					//$logger->info($input);

				}else{
					$input = "Err";
				}

				

				$outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($del_doc."\n".$input);	
				$response = $bot->replyMessage($event->getReplyToken(), $outputText);
				break;

			}

			if(strpos($messageText, 'clearall') !== false){

				$clear_database = shell_exec('curl -d \'[]\' -H "Content-Type: application/json" -X PUT https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA ; curl -d \'[]\' -H "Content-Type: application/json" -X PUT https://api.mlab.com/api/1/databases/tstdb/collections/currentValue?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA ; curl -d \'[]\' -H "Content-Type: application/json" -X PUT https://api.mlab.com/api/1/databases/tstdb/collections/cancelOrder?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA');
				
				$outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($clear_database);	
				$response = $bot->replyMessage($event->getReplyToken(), $outputText);
				break;

			}

			if(strpos($messageText, 'list') !== false){
					$list = file_get_contents('https://api.mlab.com/api/1/databases/tstdb/collections/linebot?apiKey=4csW3sDVAQwWESHj37IW_1XkRSAvhVwA&f={"No":1,"Name":1,"Coffee":1}');
					$json = json_decode($list);
					$num = count($json);
					for($i=0; $i<$num; $i++){
						$str_list .= ($json[$i]->No.'   '.$json[$i]->Name.'   '.$json[$i]->Coffee."\n");
					}
					$outputText = new \LINE\LINEBot\MessageBuilder\TextMessageBuilder($str_list);
					$response = $bot->replyMessage($event->getReplyToken(), $outputText);
				break;
			}
		}
	}
}  

