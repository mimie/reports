<?php

   ob_start();
/**
  $date = date("Ymd");  

  $filename = $date.'_closedTickets.html';
  header('Content-type: text/html');
  header('Content-disposition: attachment; filename=' . $filename);


   $fileName = date("Ymd");
   $fileLocation = "pdf/".$fileName."_closedTickets.pdf";
   $fileHandle = fopen($fileLocation, 'w+') or die("can't open file");
   fclose($fileHandle);
**/

?>
<html>
<head>
<style type="text/css">

    table{
       border-collapse:collapse;
    }

    table.circuitDetail td{
       <!--border:1px solid black;-->
       padding:5px;
       width:130px;
       height:25px;
    }

    #tdlabel{
      color:#0000FF;
      font-style:italic;
      font-weight:bold;
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:13px;
      width:35%;

    }

    #circuitData{
      font-style:italic;
      font-weight:bold;
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:13px;
      width:65%;
    }

    #header{
      font-style:italic;
      color:#0B243B;
      font-family:"Arial","Calibri","Verdana",sans-serif;
    }

    #dateRange{
      font-style:bold;
      color:#0404B4;
      font-family:"Arial","Calibri","Verdana",sans-serif;
    }

    table.troubleTickets td,th{
       border: 1px solid black;
       padding: 6px;
       border-color:#81BEF7;

    }

    #ticketsHeader{
      color:#FF0000;
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:13px;
      font-weight:bold;
      background-color:#A9F5F2;
    }

    #ticketsData{
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:12px;
      font-weight:bold;
    }

    #ticketsData2{
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:12px;
      font-weight:bold;
      background-color:#A9F5F2;
     }

</style>
 
 

<!--</style>-->
</head>
<body>
     <div>
         <img src = "images/globe_logo.png"><br>
     </div>
     <div id = "header">
           <h1 style="margin-bottom:0;">Less Than 30 Days of Installation with Troubles</h1>
     </div>
     <div id = "dateRange">
         <?php

           echo "<b>From ".date('F j, Y', strtotime('-30 days'))." to ";
           echo date("F j, Y")."</b>";
         ?>
      </div>

<?php
   require('Mail.php');
    
   include 'dbcon.php';
   include 'ticket_functions2.php';
     
    $sql = "SELECT name, xml_content_value, ci.last_version_id, ci.id\n"
         . "FROM configitem_version cv, configitem ci, xml_storage xs\n"
         . "WHERE ci.cur_depl_state_id =147\n"
         . "AND cv.configitem_id = ci.id\n"
         . "AND cv.id IN (SELECT MAX(id) FROM configitem_version GROUP BY configitem_id)\n"
         . "AND ci.last_version_id = xs.xml_key\n"
         . "AND xml_content_key = \"[1]{\'Version\'}[1]{\'ActivationDate\'}[1]{\'Content\'}\"\n"
         . "AND CURDATE() >= STR_TO_DATE( xml_content_value, \"%Y-%m-%d\" ) \n"
         . "AND STR_TO_DATE(xml_content_value, \"%Y-%m-%d\") >= DATE_ADD(CURDATE(),INTERVAL -30 DAY)";

     $result_activatedTickets = mysql_query("$sql") or die(mysql_error());   
     while($row = mysql_fetch_array($result_activatedTickets)){
           
           $circuitId = $row['name'];
           $source_key = $row['id'];
           $activationDate = $row['xml_content_value'];
           $xml_key = $row['last_version_id'];
           $format_activation = DateTime::createFromFormat('Y-m-d', $activationDate)->format('m/d/Y');

           $affectedSite = getAffectedSite($xml_key);
           $projectEngineer = getProjectEngineer($xml_key);
           $oldestTicketId = getOldestTicketId($source_key);
           $oldestCreateTime = getCreateTime($oldestTicketId);
           $result_troubleTickets = getLinkedTickets($source_key,$activationDate);
           
           if (!$result_troubleTickets) {
             continue;
           }
           $num_result_troubleTickets = mysql_num_rows($result_troubleTickets);

           $circuitMaturity = getCircuitMaturity($format_activation,$oldestCreateTime);

           //if no trouble tickets found on particular Circuit ID
           //do not print Circuit ID on the report
           if($num_result_troubleTickets > 0){
           
               $text_activationDate = $row['xml_content_value'];
               $activationDate_format = DateTime::createFromFormat('Y-m-d', $text_activationDate)->format('m/d/Y');
?>          
      <table class = "circuitDetail">
        <tr>
           <td id = "tdLabel">Affected Site</td>
           <td id = "circuitData"><?=$affectedSite?></td>
        </tr>
        <tr>
           <td id = "tdLabel">Service/Circuit ID</td>
           <td id = "circuitData"><?=$circuitId?></td>
        </tr>
        <tr>
           <td id = "tdLabel">Activation Date</td>
           <td id = "circuitData"><?=$activationDate_format?></td>
        </tr>
        <tr>
           <td id = "tdLabel">Circuit Maturity</td>
           <td id = "circuitData"><?=$circuitMaturity?></td>
        </tr>
        <tr>
           <td id = "tdLabel">Project Engr</td>
           <td id = "circuitData"><?=$projectEngineer?></td>
        </tr>
        <tr>
           <td id = "tdLabel">Status</td>
           <td id = "circuitData">NEW ACTIVATION</td>
       </tr>
     </table><br>

     <table class = "troubleTickets">
           <tr>
              <th id = "ticketsHeader">Ticket Id</th>
              <th id = "ticketsHeader">Rcvd Date/Time</th>
              <th id = "ticketsHeader">Trouble Reported</th>
              <th id = "ticketsHeader">Close Date/Time</th>
              <th id = "ticketsHeader">Duration(in minutes)</th>
              <th id = "ticketsHeader">Pending Time(in minutes)</th>
              <th id = "ticketsHeader">Reason For Outage</th>
              <th id = "ticketsHeader">Last Action Taken</th>
              <th id = "ticketsHeader">Aging(in days)</th>
           </tr>
<?php
              $color = 1;
              $ids = array();
              while($row = mysql_fetch_array($result_troubleTickets)){
                 $ticketNumber = $row['tn'];
                 $troubleReported = $row['title'];
                 $ticketId = $row['id'];
                 $ids[] = $ticketId;
                 $reasonOutage = getReasonForOutage($ticketId);
                 $lastActionTaken = getLastActionTaken($ticketId);
                 $closeDateTime = getCloseDateTime($ticketId);
                 $ticketCreateTime = getCreateTime($ticketId);
                 $age = getTicketAge($activationDate_format,$ticketCreateTime);
                 $ticketDuration = getTicketDuration($ticketId);
                 $yesterday = date("m/d/y",strtotime("-1 day"));
                 $closeTime = date('m/d/y',strtotime($closeDateTime));
                 $checkDateRange = checkDateWithin30Days($ticketCreateTime,$activationDate_format);
                
                 //echo $format;
                 if($ticketDuration == 0){

                    $ticketDuration = getTicketDuration2($ticketCreateTime,$closeDateTime);
                 }            
                 if ($closeDateTime == NULL && $checkDateRange == TRUE) {
                   continue;
                 }
                 $pendingTime = getPendingTime($ticketCreateTime,$closeDateTime,$ticketId);
                 if($color == 1 && $closeDateTime != NULL && $checkDateRange == TRUE){
                   echo "<tr>";
                   echo "<td id = 'ticketsData'>".$ticketNumber."</td>";
                   echo "<td id = 'ticketsData'>".$ticketCreateTime."</td>";
                   echo "<td id = 'ticketsData'>".$troubleReported."</td>";
                   echo "<td id = 'ticketsData'>".$closeDateTime."</td>";
                   echo "<td id = 'ticketsData'>".$ticketDuration."</td>";
                   echo "<td id = 'ticketsData'>".$pendingTime."</td>";
                   echo "<td id = 'ticketsData'>".$reasonOutage."</td>";
                   echo "<td id = 'ticketsData'>".$lastActionTaken."</td>";
                   echo "<td id = 'ticketsData' align = 'center'>".$age."</td>";
                   echo "</tr>";
                    
                   $color = 2;
                  }
                 elseif($color == 2 && $closeDateTime != NULL && $checkDateRange == TRUE){
                   echo "<tr>";
                   echo "<td id = 'ticketsData2'>".$ticketNumber."</td>";
                   echo "<td id = 'ticketsData2'>".$ticketCreateTime."</td>";
                   echo "<td id = 'ticketsData2'>".$troubleReported."</td>";
                   echo "<td id = 'ticketsData2'>".$closeDateTime."</td>";
                   echo "<td id = 'ticketsData2'>".$ticketDuration."</td>";
                   echo "<td id = 'ticketsData2'>".$pendingTime."</td>";
                   echo "<td id = 'ticketsData2'>".$reasonOutage."</td>";
                   echo "<td id = 'ticketsData2'>".$lastActionTaken."</td>";
                   echo "<td id = 'ticketsData2' align = 'center'>".$age."</td>";
                   echo "</tr>";
                    
                   $color = 1;

                 }
              }//end while loop
                  
                echo"</table><br>";
                echo "<br>";
           }//endif
    }//end while loop
      
  mysql_close($db); 
?>
</body>
</html>
<?php
//print_r($ids);  
//echo implode(',',$ids);

/**

   //$contents = ob_get_contents();
   //$contents = file_get_contents($filename);
    $contents = ob_end_flush();
    $fileName = date("Ymd");

    $fileLocation = "html/".$fileName."_closedTickets.html";

    //save the file...
    $fileHandle = fopen($fileLocation,'w+') or die("can't open the file");
    fwrite($fileHandle,$contents);
    fclose($fileHandle);

    //display link to the file you just saved...
    //echo "<a href='".$filename."'>Click Here</a> to download the file...";
     var_dump($contents);


 /*generateHtml(trouble_tickets.php);

 **/
   $body = ob_get_clean();
   
   $pdfFilename = generatePDF($body);  

   $rangeDate = "From ".date('F j, Y', strtotime('-30 days'))." to ".date("F j, Y");


#Mail stuff

        //File
        $file = fopen('pdf/'.$pdfFilename, 'rb');
        $data = fread($file,filesize('pdf/'.$pdfFilename));
        fclose($file);
       
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        $fileatttype = "application/pdf";


        $from = "Imperium Touchpoint <touchpoint@imperium.ph>";
        $subject = "Less Than 30 Days Installation With Troubles - ".$rangeDate;

        $host = "ssl://imperium.mail.pairserver.com";
        $port = "465";
        $to = "karen@imperium.ph";
        $username = "outbound@imperium.ph";
        $password = "imperiummail";

        /**$headers = array ('From' => $from,
          'To' => $to,
          'Subject' => $subject,
            'Content-type' => 'text/html;charset=ISO-8859-1',
            'MIME-Version' => 1.0);*/
        $headers = array ('From' => $from,
          'To' => $to,
          'Subject' => $subject,
            'Content-type' => 'multipart/mixed; boundary = "'.$mime_boundary.'"',
            'MIME-Version' => 1.0);
        $smtp = Mail::factory('smtp',
          array ('host' => $host,
            'port' => $port,
            'auth' => true,
            'username' => $username,
            'password' => $password,));

       
        $message = " \n\n" .
                   "--{$mime_boundary}\n" .
                   "Content-Type: text/html;charset=\"ISO-8859-1\n" .
                   "Content-Transfer-Encoding: 7bit\n\n" .
                   "\n\n".
                   "$body";

       $data = chunk_split(base64_encode($data));
       $message .= "--{$mime_boundary}\n" .
                   "Content-Type: {$fileatttype};\n" .
                   " name=\"{$pdfFilename}\"\n" .
                   "Content-Disposition: attachment;\n" .
                   " filename=\"{$pdfFilename}\"\n" .
                   "Content-Transfer-Encoding: base64\n\n" .
                   $data . "\n\n" .
                   "-{$mime_boundary}-\n";

       $mail = $smtp->send($to, $headers, $message);
       echo "Sending mail to: $to". "<br>";
       if (PEAR::isError($mail)) {
         echo ("<p>" . $mail->getMessage() . "</p>");
        } else {
         echo ("<p>Message successfully sent!</p>");
        }
?>
