<!doctype html>

<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <title>Stocks</title>
  <meta name="description" content="A simple HTML5 Template for new projects.">
  <meta name="author" content="SitePoint">

  <meta property="og:title" content="Stock File Upload">
  <meta property="og:type" content="website">
  <meta property="og:description" content="Stock file Upload">
  <meta property="og:image" content="image.png">

  <link rel="apple-touch-icon" href="/apple-touch-icon.png">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@100;300;400;700&display=swap" rel="stylesheet"> 
  <link rel="stylesheet" href="css/style.css?v=1.0"> 
  <link rel="stylesheet" href="css/autocomplete.css?v=1.0">
  <link rel="stylesheet" href="//code.jquery.com/ui/1.13.1/themes/base/jquery-ui.css">
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"
			  integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4="
			  crossorigin="anonymous"></script>
  <script src="https://code.jquery.com/ui/1.13.1/jquery-ui.js"></script>
  <script src="js/scripts.js"></script>
  <script src="js/autocomplete.js"></script>

</head>

<body>
  <div class="stockDiv">
  <!-- your content here... -->
  <form action="upload.php" class="uploadForm" method="post" enctype="multipart/form-data" > 
    <input id="stockfile" type="file" name="stockfile" onChange='validateFileName(event)'/> 
    <input id="upload-button" type="submit" value="Upload Stocks" /> 
  </form>
  <span class="error fileuploaderror"></span>
  <?php

$files1 = array_diff( scandir( 'source' ), array('..', '.'));
$fileNames = array_filter((array) glob("source/*") );//array_shift($files1);

$fileSource = [];
$stockCompanies =[];
$fileFormatError = "";

if(isset($_REQUEST['status']) && $_REQUEST['status']=="errorformat")
{
  echo "<span class='error' style='display:block;'>Wrong file format uploaded</span>";
  die();
}

if(!empty($fileNames))
{
  $lines = file($fileNames[0], FILE_IGNORE_NEW_LINES);
  for($i=1;$i<count($lines);$i++)
  {
    list($idno,$stockDate,$stockName,$stockPrice) = explode(",",$lines[$i]);
    array_push($stockCompanies,$stockName);
    $stockDate = date('d-m-Y',strtotime($stockDate));
    $fileSource[strtotime($stockDate)] [] = array("stockDate"=>$stockDate,"stockName"=>$stockName,"stockPrice"=>$stockPrice);
  }
  $stockCompanies = array_unique($stockCompanies);
}

if(!empty($fileSource)) {
  if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}
  $_SESSION['session_source'] = json_encode($fileSource);
  ?>
    <div class="stockElements">

      <div class="stockleft">
        <label>Stock Company</label>
        <input type="text" id="companySearchTxt" name="stockCompanySearch" value="" class="formElement"/>
        <span class="error companySearchError"></span>
        <?php if(!empty($stockCompanies)) : ?>
           <ul class="companylist">
              <?php foreach($stockCompanies as $companies): ?>
                 <li data-value="<?php echo $companies; ?>"><?php echo $companies; ?></li>
              <?php endforeach; ?>  
           </ul>
        <?php endif; ?>
      </div>

      <div class="stockright">
        <label>Date Range</label>
        <div class="dateContainer">
          <div class="pickerContainer">
            <input type="text" name="fromDate" id="fromDate" value=""  class="formElement"/>
          </div>
          <div class="pickerContainer">
            <input type="text"  name="todate" id="todate" value=""  class="formElement"/>
          </div>
        </div>        
        <span class="error daterangeError"></span>
      </div>
      
      <div class="button_container">
        <input type="button" class="clsGetData" value="Get Stock Data"/>
      </div>

      <div class="results">
        <h4 id="notfound">Stocks not found on the date range.</h4>
        <div class="goal1">
            <ul>
              <li>Buy the stock on</li>
              <li id="buyStock">&nbsp;</li>
              <li>Sell the stock on</li>
              <li id="sellStock">&nbsp;</li>
            </ul>
        </div>

        <div class="goal2">
            <ul>
              <li>Mean of Stock Price</li>
              <li id="stockMean">&nbsp;</li>
              <li>Standard Deviation of the Stock Prices</li>
              <li id="sdStock">&nbsp;</li>
              <li>Profit Made for 200 Shares</li>
              <li id="profitMade">&nbsp;</li>
            </ul>
        </div>      
      </div>
    </div>
  <?php
}

  ?>
  </div>
</body>
</html>