<?php
  ob_start();
  require_once("dompdf/dompdf_config.inc.php");
  
  $date = new DateTime();
  $date->sub(new DateInterval('P1D'));
  $date = $date->format('F j, Y');

?>

<html>
<head>
<!--      <link rel = stylesheet type = "text/css" href = "report_format.css" />  -->
<title></title>
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
</style>
</head>
<body>
<script type="text/php">
        if (isset($pdf)){
          $font = Font_Metrics::get_font("helvetica", "bold");
          $pdf->page_text(420, 565, "{PAGE_NUM}", $font, 9, array(0,0,0));
        }
</script>
     <div>                                                                                            
         <img src = "images/globe_logo.png"><br>                                                      
     </div>                                                                                           
     <div id = "header">
           <h1 style="margin-bottom:0;">Less Than 30 Days of Installation with Troubles</h1>
     </div>
     <div id = "dateRange">
         <?php

           echo "<b>".$date."</b><br>";
         ?>
      </div><br>
<?php
    
  include 'dbcon.php';
  include 'ticket_functions2.php';
  require('Mail.php');
  $closedTickets = getDailyClosedTickets();

  $prevCircuitId = NULL;
  $count = NULL;

  while($row = mysql_fetch_row($closedTickets)){

   $ticketId = $row[1];
   $ticketNumber = $row[2];
   $troubleReported = $row[3];

   $circuitDetails = getCircuitId($ticketId);
   $circuit = mysql_fetch_row($circuitDetails);
   $circuitId = $circuit[0];

   if($circuitId != NULL){

    $xml_key = $circuit[1];
    $source_key = $circuit[2];
    $activationDate_text = getActivationDate($xml_key);
   
    if($activationDate_text != NULL){

      $ticketCreateTime = getCreateTime($ticketId);
      //$activationDate = DateTime::createFromFormat('Y-m-d', $activationDate_text)->format('m/d/Y');
      $activationDate = date('m/d/Y',strtotime($activationDate_text));
      $checkDateRange = checkDateWithin30Days($ticketCreateTime,$activationDate);
      $activated_Circuit = getDeploymentState($xml_key);

     //get Circuit Maturity
     $oldestTicketId = getOldestTicketId($source_key);
     $oldestCreateTime = getCreateTime($oldestTicketId);
     $circuitMaturity = getCircuitMaturity($activationDate,$oldestCreateTime);
     $maturity = strtok($circuitMaturity," ");
     $maturity = (int)$maturity;
    }//end if
   }//end if

   if($circuitId != NULL && $prevCircuitId != $circuitId && $checkDateRange && $activated_Circuit && $maturity <= 30){

     if($prevCircuitId != NULL){
        echo "</table><br><br><br>";
     }

//     var_dump($source_key);   

    //-----CIRCUIT DETAILS---------------------------------------------------- 
    
     $affectedSite = getAffectedSite($xml_key);
     
  
     $projectEngineer = getProjectEngineer($xml_key);
     
     //----CIRCUIT DETAILS---------------------------------------------------

     //----TICKET INFORMATION DETAILS-----------------------------------------
     
     $closeDateTime = getCloseDateTime($ticketId);

     //get Ticket Duration
     $ticketDuration = getTicketDuration($ticketId);
     if($ticketDuration == 0){
       $ticketDuration = getTicketDuration2($ticketCreateTime,$closeDateTime);
     }
     
     $pendingTime = getPendingTime($ticketCreateTime,$closeDateTime,$ticketId);
     $reasonOutage = getReasonForOutage($ticketId);     
     $lastActionTaken = getLastActionTaken($ticketId);
     $age = getTicketAge($activationDate,$ticketCreateTime);
     
     //----TICKET INFORMATION DETAILS-----------------------------------------
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
           <td id = "circuitData"><?=$activationDate?></td>
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
           <tr>
              <td id = "ticketsData"><?=$ticketNumber?></td>
              <td id = "ticketsData"><?=$ticketCreateTime?></td>
              <td id = "ticketsData"><?=$troubleReported?></td>
              <td id = "ticketsData"><?=$closeDateTime?></td>
              <td id = "ticketsData"><?=$ticketDuration?></td>
              <td id = "ticketsData"><?=$pendingTime?></td>
              <td id = "ticketsData"><?=$reasonOutage?></td>
              <td id = "ticketsData"><?=$lastActionTaken?></td>
              <td id = "ticketsData"><?=$age?></td>
           </tr>

    

<?php
   
    $prevCircuitId = $circuitId;  
    $count = 1; 
   }//end if
     
   elseif($circuitId != NULL && $prevCircuitId == $circuitId && $checkDateRange && $activated_Circuit && $maturity <= 30){?>

          <tr>
              <td id = "ticketsData"><?=$ticketNumber?></td>
              <td id = "ticketsData"><?=$ticketCreateTime?></td>
              <td id = "ticketsData"><?=$troubleReported?></td>
              <td id = "ticketsData"><?=$closeDateTime?></td>
              <td id = "ticketsData"><?=$ticketDuration?></td>
              <td id = "ticketsData"><?=$pendingTime?></td>
              <td id = "ticketsData"><?=$reasonOutage?></td>
              <td id = "ticketsData"><?=$lastActionTaken?></td>
              <td id = "ticketsData"><?=$age?></td>
           </tr>
<?php
     $prevCircuitId = $circuitId;
   }//end elseif
    
  }//end while loop

  if($count == NULL){ 
     echo "<b>There were no tickets closed on this day related to newly installed circuits.</b>";
  }
?>
 </table>
</body>
</html>

<?php
  
/** $body = ob_get_clean();
   
 $pdfFilename = generatePDF($body);  


#Mail stuff

        //File
        $file = fopen('pdf/'.$pdfFilename, 'rb');
        $data = fread($file,filesize('pdf/'.$pdfFilename));
        fclose($file);
       
        $semi_rand = md5(time());
        $mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
        $fileatttype = "application/pdf";


        $from = "Imperium Touchpoint <touchpoint@imperium.ph>";
        $subject = "Less Than 30 Days Installation With Troubles - ".$date;

        $host = "ssl://imperium.mail.pairserver.com";
        $port = "465";
        //$to = "karen@imperium.ph,karenmae.convicto@gmail.com";
        $to = "gary@imperium.ph,jmalonso@globetel.com.ph,karen@imperium.ph,touchpoint@imperium.ph,zbsbaraquel@globetel.com.ph,ldleopoldo@globetel.com.ph"; 
        //$to = "ldleopoldo@globetel.com.ph";
        $username = "outbound@imperium.ph";
        $password = "imperiummail";

/**        $headers = array ('From' => $from,
          'To' => $to,
          'Subject' => $subject,
            'Content-type' => 'text/html;charset=ISO-8859-1',
            'MIME-Version' => 1.0);*/
/**        $headers = array ('From' => $from,
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
**/
?>
