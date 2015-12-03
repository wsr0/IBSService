<?php
require_once 'autoload.php';
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class soapServerClass
{
     /**
     * @param string $LoginName
     * @param string $Password
     * @return string $XML
     */
function SDMRestAccount($LoginName,$Password)
{  
 
// Create the logger
$logger = new Logger('logger_service');
// Now add some handlers
$logger->pushHandler(new StreamHandler(__DIR__.'/Log/service.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());
// You can now use your logger
$logger->addInfo('Start running SDMRestAccount class');

$sLogin=$LoginName;
$sPassword=$Password;
$postData = 'password='.$sPassword.'&username='.$sLogin; //Логин и пароль
$data=  http_request('https://retail.sdm.ru/logon?ReturnUrl=%2f',true,$postData,false,'','Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',60);
if (!$data) return 'нет ответа от сервера';
//echo($data);
$cookie=substr($data,strpos($data,'cookie=')+7,strlen($data));
if (!$cookie)    return 'не смогли залогиниться - нет кукисов';
//echo($cookie);
    $data=  http_request('https://retail.sdm.ru/',false,'',false,$cookie,'Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)',60);
    if (!$data) return 'не можем получить информацию по счетам';
    $body=substr($data,strpos($data,'body=')+5,strlen($body)-8);
    //echo($body);
    $doc= new DOMDocument();
    libxml_use_internal_errors(true);
    $doc->loadHTML($body);
    libxml_clear_errors();
    
    $xpath = new DOMXPath($doc);
    $xml = new DOMDocument('1.0','UTF-8');
    // $xml = new DOMDocument('1.0','CP1251');
    $xmlRoot=$xml->createElement('GetAccountRestAndStmSDM');
    $xml->appendChild($xmlRoot);
    
    $TableTrAccount = $xpath->query('//div[@class="Content-Center"]/div[1]//table/tr/td');
    $TableTrCard = $xpath->query('//div[@class="Content-Center"]/div[2]//table/tr/td');
    $CountAccount= ($TableTrAccount->length/4);
    $CountCard=$TableTrCard->length/6;

    
    //По счетам
    $xmlAccountList = $xml->createElement("AccountsList");
    $xmlAccountListAttribute= $xml->createAttribute("count");
    $xmlAccountListAttribute->value=$CountAccount;
    $xmlAccountList->appendChild($xmlAccountListAttribute);
    $xmlRoot->appendChild($xmlAccountList);
    for ($i=0; $i<$CountAccount;$i++) {
       $xmlAccount=$xml->createElement("Account");
       $xmlAccountList->appendChild($xmlAccount);
        for ($j = 0; $j < 4; $j++) //4 <td>
        {
          //  printf('i='.$i.';j='.$j.';'.nodeContent($TableTrAccount->item($i*4+$j)).'<br>');
                      switch ($j)
                    {
                        case 0:
                            $xmlCurrCode=$xml->createElement("CurrCode");
                            $xmlAccount->appendChild($xmlCurrCode);
                            $xmlCurrCode->nodeValue=
                                    str_replace('"/','',
                                    str_replace(' ','',
                                    str_replace(' "ico-l" ','',
                                    str_replace('img src="/img/ico_usd.gif" width="16" height="19" alt="','',
                                    str_replace('img src="/img/ico_rur.gif" width="16" height="19" alt="','',
                                    str_replace('img src="/img/ico_eur.gif" width="16" height="19" alt="','', 
                                    nodeContent($TableTrAccount->item($i*4 + j))   
                                   ))))));
                            
                            break;
                        case 1:
                            $xmlAccountName=$xml->createElement("AccountName");
                            $xmlAccount->appendChild($xmlAccountName);
                            $xmlAccountName->nodeValue=nodeContent($TableTrAccount->item($i*4 +j+1));
                            break;
                        case 2:
                            $xmlRest=$xml->createElement("Rest");
                            $xmlAccount->appendChild($xmlRest);
                            $xmlRest->nodeValue= nodeContent($TableTrAccount->item($i*4 + $j));
                            break;
                        case 3:
                            $xmlAcountID=$xml->createElement("AcountID");
                            $xmlAccount->appendChild($xmlAcountID);
                            $xmlAcountID->nodeValue=
                                          str_replace('" "ajax-window"История платежей/a','',
                                          str_replace('a href="/finances/account/','',  
                                          nodeContent($TableTrAccount->item($i*4 + $j)) 
                                          ));
                            break;
                        default:
                         //   Xml_.WriteStartElement("Param" + (j + 1).ToString());
                            break;
                    }
        }
    }
    
    
    //По картам
    $xmlCardList = $xml->createElement("CardList");
    $xmlCardListAttribute= $xml->createAttribute("count");
    $xmlCardListAttribute->value=$CountCard;
    $xmlCardList->appendChild($xmlCardListAttribute);
    $xmlRoot->appendChild($xmlCardList);
    for ($i=0; $i<$CountCard;$i++) {
       $xmlCard=$xml->createElement("Cards");
       $xmlCardList->appendChild($xmlCard);
        for ($j = 0; $j < 6; $j++) //6 <td>
        {
          //  printf('i='.$i.';j='.$j.';'.nodeContent($TableTrCard->item($i*6+$j)).'<br>');
                     switch ($j)
                    {
                        case 0:
                            $xmlCurrCodeCard=$xml->createElement("CurrCode");
                            $xmlCard->appendChild($xmlCurrCodeCard);
                            $xmlCurrCodeCard->nodeValue=
                                    str_replace('"/','',
                                    str_replace(' ','',
                                    str_replace(' "ico-l" ','',
                                    str_replace('img src="/img/ico_usd.gif" width="16" height="19" alt="','',
                                    str_replace('img src="/img/ico_rur.gif" width="16" height="19" alt="','',
                                    str_replace('img src="/img/ico_eur.gif" width="16" height="19" alt="','', 
                                    nodeContent($TableTrCard->item($i*6+$j))
                                    ))))));
                            break;
                        case 1:
                            $xmlCardType=$xml->createElement("CardType");
                            $xmlCard->appendChild($xmlCardType);
                            $xmlCardType->nodeValue=
                                        str_replace(' ','',
                                        str_replace('img src=img/ico_mastercard.gif" width="32" height="20" alt="','',
                                        str_replace('"/','',
                                        str_replace(' "ico-r"','',
                                        str_replace('img src="/img/ico_visa.gif" width="32" height="20" alt="','',
                                        nodeContent($TableTrCard->item($i*6+$j))
                                        )))));
                            break;
                        case 2:
                            $xmlCardID=$xml->createElement("CardID");
                            $xmlCard->appendChild($xmlCardID);
                            $xmlCardID->nodeValue=
                                      str_replace('"MASTERCARD GOLD','',
                                      str_replace('"VISA PLATINUM','',
                                      str_replace('"VISA GOLD','',
                                      str_replace('"VISA CLASSIC','',
                                    
                                      str_replace('"MASTERCARD ELECTRONIC','',
                                      str_replace('"VISA ELECTRON','',
                                      str_replace('/a','',
                                      str_replace('a "ajax-window" href="/finances/card/','',
                                      nodeContent($TableTrCard->item($i*6+$j))
                                      ))))))));
                            break;
                        case 4:
                            $xmlRestCard=$xml->createElement("Rest");
                            $xmlCard->appendChild($xmlRestCard);
                            $xmlRestCard->nodeValue= nodeContent($TableTrCard->item($i*6+$j));
                            break;
                        case 5:
                            $xmlCardClose=$xml->createElement("CloseCard");
                            $xmlCard->appendChild($xmlCardClose);
                            $xmlCardClose->nodeValue=  str_replace('срок действия: ','',nodeContent($TableTrCard->item($i*6+$j)));
                            break;
                        default:
                            break;
                    }
                    
        }
    }
    
/*
    foreach ($TableTrAccount as $tag) {
         echo $tag->C14N();
        echo nodeContent($tag);
       }

    foreach ($TableTrAccount as $tag)
    {
        echo ($tag->nodeValue.'<br>');
    }
    
        foreach ($TableTrCard as $tag)
    {
        echo ($tag->nodeValue.'<br>');
    }
*/

 //return cp1251_to_utf8($xml->saveXML());
    $logger->addInfo('Good result: '.$xml->saveXML());
    $logger->addInfo('End SDMRestAccount class');
     return $xml->saveXML();
       
}

    /**
     * @return int test
     */
public function Test()
{

            return 5;
}

}


