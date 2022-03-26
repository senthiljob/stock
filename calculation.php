<?php
 if (session_status() !== PHP_SESSION_ACTIVE) {session_start();}

$fileSource = json_decode($_SESSION['session_source']);
$startFrom = strtotime(date("d-m-Y",strtotime($_REQUEST['stockFrom'])));
$startTo = strtotime(date("d-m-Y",strtotime($_REQUEST['stockTo'])));
$stockName = $_REQUEST['stockname'];

$stockDetails = [];

$output = [];
$stockInfo = [];
/*$date = date("Y-m-d", strtotime("-30 days"));
echo '======>'.$date.'<br/>';
die();*/
//echo '<PRE>';print_r($fileSource);echo '</PRE>';
if(!empty($fileSource))
{
    $stockDetails = parseSourceFile($fileSource,$startFrom,$startTo,$stockName);

    if(empty($stockDetails)) // if the stock is not available for the date range take before 30 days
    {
        $newStart =  strtotime("-30 days", $startFrom);; //strtotime(date("Y-m-d", strtotime("-30 days")));
        $toStart = $startFrom;
        $stockDetails = parseSourceFile($fileSource,$newStart,$toStart,$stockName);
    }
    
    if(!empty($stockDetails))
    {
        /*$buyStockDetail = array_slice($stockDetails, 0, 1,true);
        echo '<PRE>';print_r($buyStockDetail);echo '</PRE>';*/
    
        $stockDiff = 0;
        $stockDates = [];
        $stockPrices = [];
       // echo '<PRE>';print_r($stockDetails);echo '</PRE>';
        for($i=0;$i<count($stockDetails);$i++)
        {
            $startPrice = $stockDetails[$i]['stockPrice'];
            array_push($stockPrices,$startPrice);
            for($j = $i+1;$j<count($stockDetails);$j++)
            {
                if($startPrice <= $stockDetails[$j]['stockPrice'])
                {
                    $diffStock = (int)($stockDetails[$j]['stockPrice']) - (int)$startPrice;
                    if($diffStock > 0 && $stockDiff < $diffStock)
                    {
                        $stockDiff = $diffStock; 
                        $stockDates = [];
                        $stockDates[$stockDetails[$i]['stockDate']."_".$stockDetails[$j]['stockDate']] = (int)($stockDetails[$j]['stockPrice']) - (int)$startPrice;
                    }                    
                }
            }
        }
    
        
        if(!empty($stockDates))
        {
            $stockDate = array_keys($stockDates)[0];
            $stockPrice = array_values($stockDates)[0];
    
            list($buyDate,$sellDate) = explode("_",$stockDate);
            $output['buyDate'] = $buyDate;
            $output['sellDate'] = $sellDate;
            $output['stockProfit'] = round(($stockPrice * 200),2);
    
            if(!empty($stockPrices))
            {
                $mean_stock = (array_sum($stockPrices) / count($stockPrices));
                $mean_value = round($mean_stock,2);
    
                // Mean
                $output['meanStock'] = $mean_value;
                $price_sum = 0;
                foreach($stockPrices as $price)
                {
                    $price_sum = $price_sum + pow(($price - $mean_value),2);
                }
               
                // Standard Deviation
                $n = count($stockPrices) - 1;
    
                $standard_deviation = sqrt($price_sum / $n);
                $output['standard_deviation'] = round($standard_deviation,2);
    
            }
        }
    }
}



function parseSourceFile($fileSource,$startFrom,$startTo,$stockName,$i=0)
{
        $stockDetails = [];        
        $i = 0;
        foreach($fileSource as $dateKey => $fsource)
        {
        
            if($startFrom <= $dateKey && $startTo >= $dateKey)
            {
                $stockInfo = array_filter($fsource,function ($source) use ($stockName){
                    if($source->stockName == $stockName)
                    {
                        return true;
                    }
                });
                $stockInfo = array_values($stockInfo);
                //echo 'StockInfo<PRE>';print_r($stockInfo);echo '</PRE>';

                if(!empty($stockInfo))
                {
                    $stockKey = strtotime(date('d-m-Y',strtotime($stockInfo[0]->stockDate)));
                    $stockDetails[$stockKey]['stockDate'] = $stockInfo[0]->stockDate;
                    $stockDetails[$stockKey]['stockName'] = $stockInfo[0]->stockName;
                    $stockDetails[$stockKey]['stockPrice'] = $stockInfo[0]->stockPrice;
                    $i++;
                }
            }

            if(!empty($stockDetails))
            {
                ksort($stockDetails);
                usort($stockDetails, function ($item1, $item2) {
                    return strtotime(date("d-m-Y",strtotime($item1['stockDate']))) <=> strtotime(date("d-m-Y",strtotime($item2['stockDate'])));
                });
            }
        }

        return $stockDetails;
       
}

echo json_encode($output);

die();