<html>
<head>
<title>Incident Report Recipients List</title>
<style type = "text/css">
    
   #header{
      font-style:italic;
      color:#0B243B;
      font-family:"Arial","Calibri","Verdana",sans-serif;
      text-align:center;
    }

    table{
       border-collapse:collapse;
       margin:auto;1
    }

    table.recipients td,th{
       border:1px solid black;
       padding:6px;
       width:130px;
       height:25px;
       border-color:#81BEF7;
    }
    
    #recipientsHeader{
      color:#FF0000;
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:13px;
      font-weight:bold;
      background-color:#A9F5F2;
    }

    #recipientsData{
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:13px;
      width:25%;
    }
    
    #recipientsData2{
      font-family:"Arial","Calibri","Verdana",sans-serif;
      font-size:13px;
      background-color:#CEECF5;
      width:25%;
    }
</style>
<!--refresh page every 5 seconds 
<meta http-equiv="refresh" content="7"> -->
<script>

   function validateForm(){
      var customerName = document.forms["addRecipient"]["customer"].value;
      if(customerName == ""){
        alert("Please enter a customer name to be added in the recipient list.");
        return false;
      }
   
   }

   function successfullyDeleted(){
       alert("Recipient/s successfully deleted.");
   }


</script>
</head>

<body background="images/white_plain.jpg">
     <div id = "header">
          <img src = "images/globe_logo.png"><h1 style = "margin-bottom:0;">Incident Report(IR) Recipients List</h1>
     </div><br>

<?php

    include 'dbcon.php';

    /* Set current, prev and next page */
    $page = (!isset($_GET['page']))? 1 : $_GET['page']; 
    $prev = ($page - 1);
    $next = ($page + 1);
    
    /* Max results per page */
    $max_results = 18;

    /* Calculate the offset */
    $from = (($page * $max_results) - $max_results);

    /* Query the db for total results. 
    You need to edit the sql to fit your needs */
    $result = mysql_query("select * from ir_recipients_list");

    $total_results = mysql_num_rows($result);

    $total_pages = ceil($total_results / $max_results);

    $pagination = '';

    /* Create a PREV link if there is one */
    if($page > 1)
    {
        $pagination .= '<a href="?page='.$prev.'">Previous</a>&nbsp;&nbsp; ';
    }

   /* Loop through the total pages */
   for($i = 1; $i <= $total_pages; $i++)
   {
    if(($page) == $i)
    {
        $pagination .= $i;
    }
    else
    {
        $pagination .= '<a href="incidentReport_Recipients.php?page='.$i.'">'.$i.'</a>&nbsp;&nbsp;';
    }
  }

   /* Print NEXT link if there is one */
   if($page < $total_pages)
   {
    $pagination .= '<a href=incidentReport_Recipients.php?page='.$next.'"> Next&nbsp;</a>';
   }

     $sql_recipients = "SELECT * FROM ir_recipients_list ORDER BY customer LIMIT $from,$max_results";
     $result_recipients = mysql_query("$sql_recipients");

?>
     <form method="post" action="" name="addRecipient" onsubmit="return validateForm()">
     <table>
           <tr>
              <td><font name = "Arial"><b>RECIPIENT:</b></font></td>
              <td><input type = "TEXT" name = "customer" placeholder="recipient" required></td>
          </tr>
          <tr>
             <td></td>
             <td align = "right"><input type="submit" value="Add Recipient" name="submit"></td>
          </tr>
    </table><br>
    </form>
    <?php
    
     if(isset($_POST['submit'])){

         $customer = strtoupper($_POST["customer"]);
         $sql = "INSERT INTO ir_recipients_list(customer) VALUES ('$customer')";
         $result = mysql_query($sql,$db);

         
         echo "<table border = '2' bordercolor='green' cellpadding = '6'>"; 
         echo "<tr>";
         echo "<td>";
            echo "<i><font color = \"green\" name = 'Arial'>Successfully added <b>$customer</b> to recipient list.</font></i>";
         echo "</td>";
         echo "</tr>";
        echo "</table><br>";
        echo'<meta http-equiv="refresh" content="2">';
     
     }

    ?>

     <table class = "recipients">
     <tr>
        <th id = "recipientsHeader">Recipients</th>
        <th id = "recipientsHeader">Edit</th>
        <th id = "recipientsHeader">Delete</th>
     </tr>
     <?php 
       $color = 1;
       while($row = mysql_fetch_array($result_recipients)){
       $recipient = $row['customer'];
       $id = $row['id'];


      if($color == 1){
     ?>
  
     <tr>
         <td id = "recipientsData"><b><i><?php echo $recipient;?></b></i></td>
         <td id = "recipientsData"><a href= "recipient_Edit.php?id=<?=$id?>&customer=<?=urlencode($recipient)?>" style = "text-decoration:none">Edit</a></td>
         <form method = "post" action = "" name = "deleteRecipient">
         <td><input type = "checkbox" name = "ids[]" value = <?=$id?>></td>
     </tr>
    <?php
       $color = 2;
    } 

    else{
     ?>
     <tr>
         <td id = "recipientsData2"><b><i><?=$recipient;?></b></i></td>
         <td id = "recipientsData2"><a href= "recipient_Edit.php?id=<?=$id?>&customer=<?=urlencode($recipient)?>" style = "text-decoration:none">Edit</a></td>
         <form method = "post" action = "" name = "deleteRecipient">
         <td id = "recipientsData2"><input type = "checkbox" name = "ids[]" value = <?=$id?>></td>
     </tr>
<?php
     $color = 1;
   }

       }//end of while 
       
    ?>
    <tr>
        <td colspan =3 align="right">         
           <input type = "submit" value = "Delete Recipient" name = "delete" onClick="return confirm('Are you  certain that you want to DELETE this record?')">
           </form><br>
        </td>
    <tr>
     <tr>
        <td align = center colspan = 3><?=$pagination?></td>
     </tr>
     <?php
        if($_POST['delete']){
           foreach ($_POST['ids'] as $id){
             $sql_delete = "DELETE FROM ir_recipients_list WHERE id = '$id'";
             $result = mysql_query($sql_delete,$db);
             echo '<meta http-equiv="refresh" content="1">';
             echo '<script type = "text/javascript">';
             echo 'successfullyDeleted()';
             echo '</script>';
           }
        }

     ?>
</table>
</body>
</html>
