<html>
<head><title></title>
</head>
<script>

   function validateForm(){
      var customerName = document.forms["addRecipient"]["customer"].value;
      if(customerName == ""){
        alert("Please enter a customer name to be added in the recipient list.");
        return false;
      }

   }
 
   function updateSuccessful(){
      alert("Successfully updated customer name.");
      window.location = "incidentReport_Recipients.php";
   }
</script>
<body background = "white_plain.jpg">
<?php
    include 'dbcon.php';
     
     $id = $_GET['id'];
     $customer = $_GET['customer'];

     if(isset($_POST['save'])){
       $customer = strtoupper($_POST["customer"]);
       $sql = "UPDATE ir_recipients_list SET customer = '$customer' WHERE id = '$id'";
       $result = mysql_query($sql,$db);

       echo '<script type = "text/javascript">';
       echo 'updateSuccessful()';
       echo '</script>';

     }

     //  echo $customer;

?>
<table border = 1>
<tr><td><br><br>
       <form method="post" action name="editRecipient" onsubmit="return validateForm()">
       <table>
<!--           <tr>
              <td><font name ="Arial"><b>CUSTOMER ID:</font></b></td>
              <td><input type = "TEXT" name = "id" value=<?=$id?> readonly disabled></td>
           </tr>-->
           <tr>
              <td><font name = "Arial"><b>RECIPIENT:</font></b></td>
            <?php
              echo "<td><input type = \"TEXT\" name = \"customer\" placeholder=\"recipient\" required value='" . htmlspecialchars($customer) ."'></td>";
            ?>
          </tr>
          <tr>
             <td></td>
             <td align = "right">
                 <input type="submit" value="Save" name="save">
                 <a href= "incidentReport_Recipients.php" style = "text-decoration:none"><input type = "button" value = "Cancel" name = "cancel"></a>
             </td>
          </tr>
       </table>
       </form><br>
</td></tr>
</table>
</body>
</html>
