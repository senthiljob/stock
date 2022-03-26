<?php

$filename = $_FILES['stockfile']['name'];

$location = "source/".$filename;
$file_error = "";

array_map( 'unlink', array_filter((array) glob("source/*") ) );

$urls = explode('?',$_SERVER['HTTP_REFERER']);
$redirect = $urls[0];


$tmpName = $_FILES['stockfile']['tmp_name'];
$csvAsArray = array_map('str_getcsv', file($tmpName));
//echo '<PRE>';print_r($csvAsArray);echo '</PRE>';


// validate csv header first

$header_values = array("id_no","date","stock_name","price");

if($header_values !== array_values($csvAsArray[0]))
{
    $file_error = "Header Mismatch error excel.";
}else {
    $formatError = false;
    $i=1;
    while($i<count($csvAsArray) && !$formatError)
    {
        if(!is_numeric($csvAsArray[$i][0]) ||
           !checkdate (date("m",strtotime($csvAsArray[$i][1])),date("d",strtotime($csvAsArray[$i][1])), date("Y",strtotime($csvAsArray[$i][1])) ) ||
           !is_numeric($csvAsArray[$i][3])
        )
        {
            $formatError = true;
        }
        $i = $i + 1;
    }
}

if($formatError)
{

    header("Location: ".$redirect."?status=errorformat");
    die();
}

if ( move_uploaded_file($_FILES['stockfile']['tmp_name'], $location) ) { 
    header("Location: ".$redirect."?status=success");
    die();
} else { 
    header("Location: ".$redirect."?status=error");
    die();
}

?>