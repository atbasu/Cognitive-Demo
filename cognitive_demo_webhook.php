<?php
# this bot simply recieves the webhook output and then retrieves
# the text entered into the room that triggered the webhook
# and, based on what it got from the room, it generates a reply accordingly
# and puts it in the outfile. A script is then called to put that reply
# into the Spark room.
#
 
 
  # get the parameters passed
  $roomId  = $_GET['room_id'];
  $auth    = $_GET['my_auth'];
  $botauth = $_GET['bot_auth'];
  $botid   = $_GET['bot_id'];
 
  $rawinp = file_get_contents('php://input');
  $jsoninp = json_decode($rawinp, true);
  $msghdr = $jsoninp['data'];
  $msgid = $msghdr['id'];
 
  //$fh = fopen($outFile, 'w') or die("can't open file");
    // the date and time this bot ran
    //$datestr = date("m/d/Y") . "  " . date("h:i:sa") . '<br><br>';
    //fwrite($fh, $datestr);
 
    // get the text of the message from the message header info given to the bot
    $cmd = 'curl -X GET https://api.ciscospark.com/v1/messages/' . $msgid . ' -H "Authorization: Bearer ' . $auth . '" -H "Accept: application/json"';
    $msginfo = shell_exec ($cmd);
 
//fwrite ($fh, "JSON from webhook:$rawinp\n");
//fwrite ($fh, "\nCMD:$cmd\n");
//fwrite ($fh, "\nmsginfo:$msginfo\n");
 
    // extract the text of the message
    $jsonmsg = json_decode($msginfo, true);
    $msgtxt = $jsonmsg['text'];
    $poster = $jsonmsg['personId'];
 
//fwrite ($fh, "\nmsgtxt:$msgtxt\n");
//fwrite ($fh, "poster is: $poster\n  botid is: $botid\n");
 
    // make sure I'm not responding to my own post
    if ($poster == $botid) {
       fclose ($fh);
       exit;
    }
 
    // based on the text answered, post to the room according to the script
    // strings to look for coming from the Spark room
    $customer1  = "it i";
    $customer2  = "i’ll";
    $customer3  = "ok, ";
    $customer4  = "sure";
    $customer5  = "grab";
    $customer6  = "199.";
    $customer7  = "4.14";
    $customer8  = "ok t";
    $customer9  = "plea";
    $customer10 = "172.";
    $customer11 = "192.";
    $customer12 = "the ";
    $customer13 = "let ";
    $customer14 = "here";
    $customer15 = "i wo";

 
    // things to respond with
	$resp1 = "Ok great! I believe the following articles/discussions may be of assistance to you:\n  1. Site-to-Site IKEv2 Tunnel between ASA and Router Configuration Examples - http://www.cisco.com/c/en/us/support/docs/security-vpn/ipsec-negotiation-ike-protocols/117337-config-asa-router-00.html \n  2. Swift Migration of IKEv1 to IKEv2 L2L Tunnel Configuration on ASA 8.4 Code - http://www.cisco.com/c/en/us/support/docs/security/asa-5500-x-series-next-generation-firewalls/113597-ptn-113597.html \n  3. Dynamic Site to Site IKEv2 VPN Tunnel Between Two ASAs Configuration Example - http://www.cisco.com/c/en/us/support/docs/security/asa-5500-x-series-next-generation-firewalls/118652-configure-asa-00.html \nWould you like to try configuring the devices based on these guides or would you prefer to wait for the support engineer to get in touch with you? ";
    $resp2 = "Sounds good! Please let me know if you need any assistance. I’ll standby";
    $resp3 = "Would you like me to check the configuration on your devices?";
    $resp4 = "Ok, you can either upload the show tech from the two devices here or if you provide me with the ip address and credentials I can use to SSH to the two devices, I can grab the show tech from the two devices directly. What would you like to do";
    $resp5 = "Please provide me the IP address and the authentication credentials I should use to ssh to your ASA.";
    $resp61 = "Attempting to grab the show tech";
    $resp62 = "";//url for cognitive_asa.txt
    $resp63 = "Ok, got it. What about the ASR?";
    $resp71 = "";//url for cognitive_asr.txt
    $resp72 = "Ok, got it. Please give me some time while I check the configuration"; // bot does not respond to this input
    $resp73 = "Comparing configurations";
    $resp74 = "Here are some of the configuration errors I found:";
    $resp75 = //url for errors.png
    $resp76 = "If these are fixed, the tunnel should get established. To get the complete result of my analysis, please download the two show techs I have grabbed from your devices earlier, visit the following page and upload them:\nhttps://cway.cisco.com/tools/L2L-Checker\n\nWould you like to test out these corrections?";
    $resp8 = "Ok, to troubleshoot this further, I need to compare the packet captures from the two VPN end points to ensure the traffic is getting to the ASAs. Would you like me to configure the capture for you or will you upload the captures once you have them?";
    $resp9 = "Ok what is the source ip address of the test traffic?"
    $resp10 = "And the destination ip address of the test traffic?"
    $resp11 = "Ok, one minute\nConfiguring captures";
    $resp112 = "Ok, please initiate the test traffic and let me know once it fails. I have also captured a snapshot of the number of encaps/decaps on the two ASAs so I can compare the two after the test.";
    $resp113 = //url show_crypto_ipsec_sa_output1.txt
    $resp121 = "Ok got it."; 
    $resp122 = //url for captures_sa_output.zip
    $resp123 = "Analyzing Captures";
    $resp124 = "It looks like the packets are making it to the ASA and I see the response coming back to the ASA as well. However, I see that while the decaps are going up on ASA, the encaps aren't. Since the packets are making it to the ASAs, I would like to troubleshoot the traffic flow through ASA. I need the following output to understand why the traffic isn't getting encrypted:\n1. show crypto ipsec sa\n2. show asp table vpn-context detail\n3. show asp table classify crypto\n\n Would you like me to grab this output from the ASA? Or can you provide me with the data?";
    $resp13 = "OK sure";
    $resp141 = "Thank you. Let me check\nAnalyzing output";
    $resp142 = "Based on the details, it looks like you're running into a known bug that impacts the software version you're running on the ASA - CSCup37416<https://tools.cisco.com/bugsearch/bug/CSCup37416/?reffering_site=dumpcr>.\nYou can upgrade to the code version 9.1(7.9) to get the fix for this bug. You can also discuss this bug with your support engineer.";
    $resp15 = "Ok. I will update the case with our findings and request the engineer to call you back as soon as possible.";
 
    $trigger = strtolower(substr ($msgtxt, 0, 5));
 
    // include the route that posts to the spark room
    include 'spark-put.php';
    include 'send-chat-hist.php';
 
    switch ($trigger) {
      case $customer1:
           PostToSpark($roomId, $botauth, $resp1, "");
           break;
      case $customer2:
           PostToSpark($roomId, $botauth, $resp2, "");
           break;
      case $customer3:
           PostToSpark($roomId, $botauth, $resp3, "");
           break;
      case $customer4:
           PostToSpark($roomId, $botauth, $resp4, "");
           break;
      case $customer5:
           PostToSpark($roomId, $botauth, $resp5, "");
           break;
      case $customer6:
           PostToSpark($roomId, $botauth, $resp61, "");
           for ($x = 0; $x < 5; $x++) {
           		sleep(1);
           		PostToSpark($roomId, $botauth, ".", "");
           } 
           PostToSpark($roomId, $botauth, $resp63, $resp62);
           break;
      case $customer7:
           PostToSpark($roomId, $botauth, $resp61, "");
           for ($x = 0; $x < 5; $x++) {
           		sleep(1);
           		PostToSpark($roomId, $botauth, ".", "");
           }
           PostToSpark($roomId, $botauth, $resp71, "");
           PostToSpark($roomId, $botauth, $resp72, "");
           PostToSpark($roomId, $botauth, $resp73, "");
		   for ($x = 0; $x < 8; $x++) {
           		sleep(1);
           		PostToSpark($roomId, $botauth, ".", "");
           }
           PostToSpark($roomId, $botauth, $resp74, $resp75);
           PostToSpark($roomId, $botauth, $resp76, "");
           break;
      case $customer8:
           $out = $resp8;
           break;
      case $customer9:
           $out = $resp9;
           break;
      case $customer10:
           $out = $resp10;
           break;
      case $customer11:
           PostToSpark($roomId, $botauth, $resp111, "");
           for ($x = 0; $x < 3; $x++) {
           		sleep(1);
           		PostToSpark($roomId, $botauth, ".", "");
           }
           PostToSpark($roomId, $botauth, $resp113, $resp112);
           break;
      case $customer12:
           PostToSpark($roomId, $botauth, $resp121, $resp122);
           PostToSpark($roomId, $botauth, $resp123, "");
           for ($x = 0; $x < 6; $x++) {
           		sleep(1);
           		PostToSpark($roomId, $botauth, ".", "");
           }
           PostToSpark($roomId, $botauth, $resp124, "");
           break;
      case $customer13:
           $out = $resp13;
           break;
      case $customer14:
           PostToSpark($roomId, $botauth, $resp141, "");                      
		   for ($x = 0; $x < 3; $x++) {
           		sleep(1);
           		PostToSpark($roomId, $botauth, ".", "");
           }
           PostToSpark($roomId, $botauth, $resp142, "");
           break;
      case $customer15:
           PostToSpark($roomId, $botauth, $resp15);
           SendChatHist();
           PostToSpark($roomId, $botauth, "Please let me know if I can be of any further assistance", "");
           break;     
      default:
           exit;
    }
    
?>