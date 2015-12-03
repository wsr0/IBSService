<?php

// Converts a DOMNodeList to an Array that can be easily foreached
function dnl2array($domnodelist) {
    $return = array();
    for ($i = 0; $i < $domnodelist->length; ++$i) {
        $return[] = $domnodelist->item($i);
    }
    return $return;
}

 function nodeContent($n, $outer=false) {
    $d = new DOMDocument('1.0','cp1251');
    // $d = new DOMDocument();
    $b = $d->importNode($n->cloneNode(true),true);
    $d->appendChild($b); 
   // $h = $d->saveHTML();
    $h = $d->saveXML();

    if (!$outer) $h =  
            str_replace('td','',
            str_replace('class=','',
            str_replace('td class="text-right"','',
            str_replace('td width="30%" class="text-right"','',
                  
                       str_replace('>','',
                       str_replace('<','',
                       str_replace('&nbsp;','',
                       substr($h,strpos($h,'>')+2,-(strlen($n->nodeName)+4))
            )))))));                
    
    return $h;
}

function cp1251_to_utf8($s) 
  { 
  if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "WINDOWS-1251") 
    { 
    $c209 = chr(209); $c208 = chr(208); $c129 = chr(129); 
    for($i=0; $i<strlen($s); $i++) 
      { 
      $c=ord($s[$i]); 
      if ($c>=192 and $c<=239) $t.=$c208.chr($c-48); 
      elseif ($c>239) $t.=$c209.chr($c-112); 
      elseif ($c==184) $t.=$c209.$c209; 
      elseif ($c==168)    $t.=$c208.$c129; 
      else $t.=$s[$i]; 
      } 
    return $t; 
    } 
  else 
    { 
    return $s; 
    } 
   } 
   
   
function utf8_to_cp1251($s) 
  { 
  if ((mb_detect_encoding($s,'UTF-8,CP1251')) == "UTF-8") 
    { 
    for ($c=0;$c<strlen($s);$c++) 
      { 
      $i=ord($s[$c]); 
      if ($i<=127) $out.=$s[$c]; 
      if ($byte2) 
        { 
        $new_c2=($c1&3)*64+($i&63); 
        $new_c1=($c1>>2)&5; 
        $new_i=$new_c1*256+$new_c2; 
        if ($new_i==1025) 
          { 
          $out_i=168; 
          } else { 
          if ($new_i==1105) 
            { 
            $out_i=184; 
            } else { 
            $out_i=$new_i-848; 
            } 
          } 
        $out.=chr($out_i); 
        $byte2=false; 
        } 
        if (($i>>5)==6) 
          { 
          $c1=$i; 
          $byte2=true; 
          } 
      } 
    return $out; 
    } 
  else 
    { 
    return $s; 
    } 
  } 

// Универсальная функция для HTTP(S) GET/POST запросов
	function http_request($url,$post=FALSE, $data='', $referer=FALSE, $cookie=FALSE, $user_agent=FALSE, $timeout=30) {
	    /*
	      ПОДРОБНАЯ ИНФОРМАЦИЯ: 
	      $url          -   URL адрес запроса
	      $post         -   POST запрос: TRUE или FALSE (не обязательно)
	      $data         -   Данные POST запроса (не обязательно)
	      $referer      -   HTTP Referer (не обязательно)
	      $cookie       -   Строка значений cookies (не обязательно)
	      $user_agent   -   Используемый User Agent (не обязательно)
	      $timeout      -   Максимальное время ожидания в секундах (не обязательно)
	    */
	    $http = FALSE;
	    $url = trim($url);
	    if(!empty($url)) {
	        $post = ($post?TRUE:FALSE);
	        $timeout = ($timeout<0?0:intval($timeout));
	        if(function_exists('curl_init')) {
	            if($curl = curl_init()) {
	                curl_setopt($curl, CURLOPT_URL, $url);
                        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);
                        curl_setopt($curl, CURLOPT_HEADER, TRUE);
                    //  curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
	                curl_setopt($curl, CURLOPT_POST, $post);
	                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
	                curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
	                curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
	                if($referer) curl_setopt($curl, CURLOPT_REFERER, $referer);
	                if($cookie) curl_setopt($curl, CURLOPT_COOKIE, $cookie);
	                if($user_agent) curl_setopt($curl, CURLOPT_USERAGENT, $user_agent);
	                if($post) curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
	                curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
	                $http = curl_exec($curl);
                        $header=substr($http,0,  curl_getinfo($curl,CURLINFO_HEADER_SIZE));
                        $body=substr($http,curl_getinfo($curl,CURLINFO_HEADER_SIZE));
	                curl_close($curl);
                        
                        preg_match_all('/Set-Cookie: (.*?)=(.*?);/i',$header,$res);
                        $cookie_='';
                        foreach ($res[1] as $key => $value) {
                            $cookie_.=$value.'='.$res[2][$key].'; ';
                        }
                            
	            }
	        }
	    }
	   //return $http;
            return 'header='.$header.';body='.$body.';cookie='.$cookie_;
	}



