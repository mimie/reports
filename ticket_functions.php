<?php
   /**
    * target key is the id in the ticket table
    * type_id is 4 - Alternate To
    * source key is the id in configitem table
    * @return linked trouble ticket numbers of each circuit ID
    */
   function getLinkedTickets($source_key){

   	$sql_linkedTickets = "SELECT lr.source_key,lr.target_key,t.tn,t.title,t.id FROM `link_relation` lr, ticket t \n"
    			   . "WHERE lr.source_key = '".$source_key."' \n"
    			   . "AND lr.type_id = '4' \n"
    			   . "AND lr.state_id = '1' \n"
    		           . "AND lr.source_object_id = '2' \n"
    			   . "AND lr.target_object_id = '1'\n"
    			   . "AND lr.target_key = t.id \n"
                           . "ORDER BY id DESC";

    	$result_linkedTickets = mysql_query("$sql_linkedTickets") or die(mysql_error());

        $sql_linkedClosedTickets = "SELECT lr.source_key,lr.target_key,t.tn,t.title,t.id FROM `link_relation` lr, ticket t \n"
                           . "WHERE lr.source_key = '".$source_key."' \n"
                           . "AND lr.type_id = '4' \n"
                           . "AND lr.state_id = '1' \n"
                           . "AND lr.source_object_id = '2' \n"
                           . "AND lr.target_object_id = '1'\n"
                           . "AND lr.target_key = t.id \n";

         while($row = mysql_fetch_row($result_linkedTickets)){
              $ticketId = $row[4];

          $sql_closeDateTime = mysql_query("SELECT create_time, ticket_id FROM `ticket_history` WHERE history_type_id = 27 AND state_id =2 AND ticket_id = '$ticketId'") or die(mysql_error());
          $count_results = mysql_num_rows($sql_closeDateTime);

          if($count_results > 0){
              
             $sql_linkedClosedTickets = $sql_linkedClosedTickets . "AND t.id = '".$ticketId."'\n";
            
          }
          
           $sql_linkedClosedTickets = $sql_linkedClosedTickets;
         }

        $sql_linkedClosedTickets = $sql_linkedClosedTickets . "ORDER BY id DESC";
       // var_dump($sql_linkedClosedTickets);

        if($sql_linkedClosedTickets == $sql_linkedTickets){
              return NULL;
          }

        else{

        $result_linkedClosedTickets = mysql_query("$sql_linkedClosedTickets") or die(mysql_error());

        return $result_linkedClosedTickets;

       }
    	//return $result_linkedTickets;

  }
 
  /**
   *value_text is the reason for outage
   *field_id = 57, 57 is the id for reason for outage in dynamic_field table
   *object_id is the id in the ticket table 
   *@return reason for outage
   */ 
  function getReasonForOutage($ticket_id){
   	$sql_reasonOutage = mysql_query("SELECT object_id,value_text FROM dynamic_field_value WHERE field_id = 57 AND object_id = '$ticket_id'") or die(mysql_error());
   
   	$row = mysql_fetch_row($sql_reasonOutage);
   	$reasonForOutage = $row[1];
   
  	return $reasonForOutage;
  }

  /**
   *a_body is the field for the last action taken
   *article_type_id = 10, note external from article_type table
   *@return the last action taken of the linked ticket
   */
  function getLastActionTaken($ticket_id){
        $sql_lastAction = mysql_query("SELECT id, a_body, ticket_id FROM article WHERE ticket_id = '$ticket_id' AND article_type_id = '10' ORDER BY create_time DESC LIMIT 1") or die(mysql_error());

        $row = mysql_fetch_row($sql_lastAction);
        $lastActionTaken = $row[1];
   
        return $lastActionTaken;
   }

   /**
    *create_time is the time when the ticket is successfully closed
    *history_type_id = 27 from ticket_history_type table with value "StateUpdate"
    *state_id = 2 from ticket_state table with value "closed successful"
    *count results 0 - ticket is not yet closed, close date/time is not available
    *@return close date time in format: 01/23/2013 04:31:00
    */
   function getCloseDateTime($ticket_id){
   	$sql_closeDateTime = mysql_query("SELECT create_time, ticket_id FROM `ticket_history` WHERE history_type_id = 27 AND state_id =2 AND ticket_id = '$ticket_id'") or die(mysql_error());
        $count_results = mysql_num_rows($sql_closeDateTime);
        
        if($count_results == 0){
            return NULL;
        }
     
        else{

            $row = mysql_fetch_row($sql_closeDateTime);
            $closeDateTime = $row[0];
            $formatted_closeDateTime = date('m/d/Y H:i:s',strtotime($closeDateTime));

            return $formatted_closeDateTime;
        }
  }
  
  /**
   *@return create time of the linked ticket in format: 01/23/2013 04:31:00
   */
  function getCreateTime($ticket_id){
       $sql_createTime = mysql_query("SELECT create_time FROM ticket WHERE id = '$ticket_id'") or die(mysql_error());
       $row = mysql_fetch_row($sql_createTime);
       $createTime = $row[0];
       $formatted_createTime = date('m/d/Y H:i:s', strtotime($createTime));

       return $formatted_createTime;
  }

  /**
   *Get the ticket age from circuit activation date to ticket create date
   *@return ticket age
   */
  function getTicketAge($circuitActivationDate,$ticketCreateDate){

      $ticketDate = new DateTime($ticketCreateDate);
      $circuitDate = new DateTime($circuitActivationDate);

      $interval = $circuitDate->diff($ticketDate);
      $age = $interval->format('%a');
 
      if($age == 0){
        
        $age = $interval->format('%a') + 1;
      }

      return $age;
  }

   /**
    *Get the circuit maturity from circuit 
    *activation date to oldest ticket
    *@return circuit maturity
    */
   function getCircuitMaturity($circuitActivationDate, $oldestTicketCreateDate){
   
      $oldestTicketDate = new DateTime($oldestTicketCreateDate);
      $circuitDate = new DateTime($circuitActivationDate);

      $interval = $circuitDate->diff($oldestTicketDate);
      $maturity = $interval->format('%a');

      if($maturity == 0){

       $maturity = $interval->format('%a') + 1;
     }

      return $maturity." Days old from first outage";
   }

   /**
    *Get the ticket Id of the oldest linked ticket of the circuit Id
    *@return oldest ticket Id
    */
   function getOldestTicketId($source_key){
   
      $sql_oldestTicketId = "SELECT lr.source_key,lr.target_key,t.tn, MIN(t.id) FROM link_relation lr, ticket t\n"
    		          . "WHERE lr.source_key = '$source_key' \n"
    			  . "AND lr.type_id = '4' \n"
    			  . "AND lr.state_id = '1' \n"
    		          . "AND lr.source_object_id = '2' \n"
    		          . "AND lr.target_object_id = '1'\n"
                          . "AND lr.target_key = t.id\n"
                          . "GROUP BY lr.source_key";
     $result_oldestTicketId = mysql_query("$sql_oldestTicketId") or die(mysql_error());
     $row = mysql_fetch_row($result_oldestTicketId);
     $ticketId = $row[3];

     return $ticketId;

   } 

   /**
    *Get the Project Engineer of the Circuit Id
    *@return Project Engineer
    */
   function getProjectEngineer($xml_key){
     
     $sql_projectEngineer = "SELECT x.xml_content_value, x.xml_key FROM xml_storage AS x\n"
          . "WHERE x.xml_type = 'ITSM::ConfigItem::145' AND x.xml_content_key = \"[1]{\'Version\'}[1]{\'ProjectManager\'}[1]{\'Content\'}\"\n"
          . "AND x.xml_content_value <> '' AND x.xml_key = '$xml_key'";

     $result_projectEngineer = mysql_query("$sql_projectEngineer") or die(mysql_error());
     $row = mysql_fetch_row($result_projectEngineer);
     $projectEngineer = $row[0];
     $projectEngineer = substr($projectEngineer,4);

     return $projectEngineer;
   }
   
   
   /**
    *@return affected site 
    */
   function getAffectedSite($xml_key){
     
     $sql_xmlContentValue = "SELECT x.xml_content_value, x.xml_key FROM xml_storage AS x\n"
          . "WHERE x.xml_type = 'ITSM::ConfigItem::145' AND x.xml_content_key = \"[1]{\'Version\'}[1]{\'NodeRemote\'}[1]{\'Content\'}\"\n"
          . "AND x.xml_content_value <> '' AND x.xml_key = '$xml_key'";

     $result_xmlContentValue = mysql_query("$sql_xmlContentValue") or die(mysql_error());
     $row = mysql_fetch_row($result_xmlContentValue);
     $login = $row[0];

     $result_customer = mysql_query("SELECT customer_id,login, location FROM `customer_user` WHERE login ='$login'") or die(mysql_error());
     $customer = mysql_fetch_row($result_customer);
     $customer_id = $customer[0];
     $location = $customer[2];
     
     if($customer_id = 'GT'){
 
         $affectedSite = $location;    
         return $affectedSite;

    }

    else{
     
        $sql_xmlContentValue_HQ = "SELECT x.xml_content_value, x.xml_key FROM xml_storage AS x\n"
                                . "WHERE x.xml_type = 'ITSM::ConfigItem::145' AND x.xml_content_key = \"[1]{\'Version\'}[1]{\'NodeHQ\'}[1]{\'Content\'}\"\n"
                                . "AND x.xml_content_value <> '' AND x.xml_key = '$xml_key'";
        $result_contentHQ = mysql_query("$sql_xmlContentValue_HQ") or die(mysql_error());
        $login = mysql_fetch_row($result_contentHQ);
       
        $result_customer = mysql_query("SELECT customer_id,login, location FROM `customer_user` WHERE login ='$login'") or die(mysql_error());
        $customer = mysql_fetch_row($result_customer);
        $customer_id = $customer[0];
        $location = $customer[2];

        $affectedSite = $location;
        return $affectedSite;
    }

  }

  /**
   *get closed tickets per day
   *if report is generated today, the closed ticket of yesterday's date will be shown
   *history_type_id = 27 from ticket_history_type table with value "StateUpdate"
   *state_id = 2 from ticket_state table with value "closed successful"
   */
  function getDailyClosedTickets(){

       //$yesterday = date("Y-m-d", strtotime("-1 day"));
       $result_closedTickets = mysql_query("SELECT th.create_time, ticket_id,tn,title FROM ticket_history th, ticket t
                               WHERE th.create_time BETWEEN '2013-04-O3 00:00:00:00' AND '2013-04-03 23:59:59:59'
                               AND history_type_id = '27' AND th.state_id = '2' AND t.id = th.ticket_id") or die(mysql_error());
       return $result_closedTickets;

  }

  /**
   *get the ticket duration based on monitor1 and monitor2 time
   */
  function getTicketDuration($ticketId){
       //monitor2 is the close date/time of the ticket
       $result_monitor2 = mysql_query("SELECT value_date,object_id FROM dynamic_field_value WHERE field_id = 37 AND object_id = '$ticketId'") or die(mysql_error());
       $ticket = mysql_fetch_row("$result_monitor2");
       $closeTime = $ticket[0];
       //monitor1 is the open date/time of the ticket     
       $result_monitor1 = mysql_query("SELECT value_date, object_id FROM dynamic_field_value WHERE field_id = 36 AND object_id = '$ticketId'") or die(mysql_error());

       $ticket = mysql_fetch_row("$result_monitor1");
       $openTime = $ticket[0];
       
       $timeDifference = strtotime($closeTime)-strtotime($openTime);
       
      //duration in minutes
      $duration = $timeDifference/60;
      $duration = round($duration,0);

      return $duration;
 }

  /*
   *this function is used only when there is no available
   *monitor1 and monitor2 time
   *@return ticket duration
   */
  function getTicketDuration2($createTime,$closeTime){
 
      $timeDifference = strtotime($closeTime)-strtotime($createTime);

      //duration in minutes
      $duration = $timeDifference/60;
      $duration = round($duration,0);

      return $duration;
  }

  function getPendingTime($createTime,$closeTime,$ticketId){
     //monitor2 is the close date/time of the ticket
     $sql_monitor2 = mysql_query("SELECT value_date, object_id FROM dynamic_field_value WHERE field_id = 37 AND object_id = '$ticketId'") or die(mysql_error());
     $monitor2 = mysql_fetch_row($sql_monitor2);
     $monitor2Time = $monitor2[0];

     if($monitor2Time){
        $monitor2Time = $monitor2Time;
     }

     else{
        $monitor2Time = $closeTime;
     }

     //monitor1 is the open date/time of the ticket
     $sql_monitor1 = mysql_query("SELECT value_date, object_id FROM dynamic_field_value WHERE field_id = 36 AND object_id = '$ticketId'") or die(mysql_error());
     $monitor1 = mysql_fetch_row($sql_monitor1);
     $monitor1Time = $monitor1[0];

     if($monitor1Time){
        $monitor1Time = $monitor1Time;
     }

     else{
        $monitor1Time = $createTime;
     }

    $pending_sql = mysql_query("SELECT create_time FROM ticket_history WHERE history_type_id = 27 AND ticket_id = '$ticketId' AND state_id IN (2,3,6,7,8,12) AND create_time > '".$monitor1Time."' AND create_time < '".$monitor2Time."' ORDER BY create_time ASC");     

    $cnt = 0;
    $cnt2 = 0;
    $total_pending = 0;
    
    unset($pending);

      while($row = mysql_fetch_assoc($pending_sql)){
                $pending[] = $row['create_time'];
                $cnt++;
      }

        if ($cnt == 1){
                $non_pending = "";
                $non_pending_sql = mysql_query("SELECT create_time FROM ticket_history WHERE history_type_id = 27 AND ticket_id = '$ticketId' AND create_time > '".$pending[0]."' AND create_time <= '".$monitor2Time."' ORDER BY create_time ASC");
                $non_pending = mysql_fetch_assoc($non_pending_sql);

                if(!$non_pending){
                        $non_pending['create_time'] = $monitor2Time;
                }

                $total_pending = $total_pending + (strtotime($non_pending['create_time']) - strtotime($pending[0]));
        } else if ($cnt > 1) {
                while ($cnt2 != ($cnt - 1)){
                        $non_pending = "";
                        $non_pending_sql = mysql_query("SELECT create_time FROM ticket_history WHERE history_type_id = 27 AND ticket_id = '$ticketId' AND create_time > '".$pending[$cnt2]."' AND create_time <= '".$pending[$cnt2+1]."' ORDER BY create_time ASC");
                        $non_pending = mysql_fetch_assoc($non_pending_sql);
                        $total_pending = $total_pending + (strtotime($non_pending['create_time']) - strtotime($pending[$cnt2]));

                        $cnt2++;
                }

                $non_pending = "";
                $non_pending_sql = mysql_query("SELECT create_time FROM ticket_history WHERE history_type_id = 27 AND ticket_id = '$ticketId' AND create_time > '".$pending[$cnt2]."' AND create_time <= '".$monitor2Time."' ORDER BY create_time ASC");
                $non_pending = mysql_fetch_assoc($non_pending_sql);

                if(!$non_pending){
                        $non_pending['create_time'] = $monitor2Time;
                }

                $total_pending = $total_pending + (strtotime($non_pending['create_time']) - strtotime($pending[$cnt2]));

        }

       $total_pending = floor($total_pending/60);

       return $total_pending;

  }

  /**
   *@return the circuit id of the ticket Id
   */
  function getCircuitID($ticketId){

   $sql_circuitId = "SELECT cv.name, cv.id,lr.source_key FROM configitem_version cv, link_relation lr, configitem ci WHERE cv.id = ci.last_version_id AND CAST(ci.id AS CHAR) = lr.source_key AND lr.target_key='$ticketId'";
   $result_circuitId = mysql_query("$sql_circuitId") or die(mysql_error());
   /**$row = mysql_fetch_row($result_circuitId);
   $circuitId = $row[0];
   return $circuitId;**/

   return $result_circuitId; 
  }


  function getActivationDate($xml_key){

    $sql_activationDate = "SELECT xml_content_value FROM xml_storage\n" 
                        . "WHERE xml_key = '$xml_key' \n"
                        . "AND xml_content_key = \"[1]{\'Version\'}[1]{\'ActivationDate\'}[1]{\'Content\'}\"";

    $result_activationDate = mysql_query($sql_activationDate);
    $row = mysql_fetch_row($result_activationDate);
    $activationDate = $row[0];
    
    return $activationDate;   

  }

  function checkDateWithin30Days($ticketCreateTime,$circuitActivationDate){

     $date1 = strtotime(date('Y-m-d H:i:s',strtotime($circuitActivationDate)));
     $date2 = strtotime(date('Y-m-d H:i:s',strtotime($ticketCreateTime)));    

    if($date1 > $date2){
      return false;
    }

    else{
     $seconds_diff = $date2-$date1;
     $dateDifference = floor($seconds_diff/(60*60*24));
    // var_dump($dateDifference);
     return $dateDifference <= 30;

  }
 }

?>
