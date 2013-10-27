<?php
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
V1.0
Script created by Juraj Nyíri, 29.10.2013.
New versions with changelogs at http://cal.jurajnyiri.eu .
My portfolio: jurajnyiri.eu. Contact: juraj.nyiri@gmal.com

You are free to modify source code (and update changes and modifications to cal.jurajnyiri.eu).
You are FORBIDDEN to modify or delete this comment about author etc.
If you copy anything from this code you also have to copy this comment.
Please respect this :).

If you don't want to host and run this script on your own contact me and i will add your IS and GAcc to mine, 
however if you choose this alternative I WILL NOT be responsible for whatever might happen with your IS or Google Account.

If you find any bugs, please report at http://cal.jurajnyiri.eu .

@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
The MIT License (MIT)

Copyright (c) 2013 Juraj Nyíri

Permission is hereby granted, free of charge, to any person obtaining a copy of
this software and associated documentation files (the "Software"), to deal in
the Software without restriction, including without limitation the rights to
use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of
the Software, and to permit persons to whom the Software is furnished to do so,
subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS
FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR
COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER
IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN
CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
	
	function CutStr($str,$zac,$kon) //funkcia na vystrihnutie stringu zo stringu
	{
		return substr($str,(strpos($str,$zac)+strlen($zac)),-(strlen($str)-strpos($str,$kon)-1));
	}
	
	function Login($user, $pass) //prihlásenie do IS cez SSL a získanie cookies
	{
		$data = array(
     		'credential_0' => $user,
     		'credential_1' => $pass,
     		'destination'  => '/auth/',
     		'submit' => 'P%C5%99ihl%C3%A1sit+se',
     		'credential_2' => 345600
		);
		
		$agent = $config->agent;
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, "https://is.muni.cz/system/login_form.pl");
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
		curl_setopt($ch, CURLOPT_HEADER, true);  
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); 
		$data = curl_exec($ch);
		curl_close($ch);
		preg_match_all('|Set-Cookie: (.*);|U', $data, $matches);   
		$cookies = implode('; ', $matches[1]); 
		if(strpos($cookies, "iscreds") !== false)
		{
			return $cookies;
		}
		else
		{
			echo  "Chyba do prihlasovania do IS účtu " . $user . ": Zlé prihlasovacie údaje!";
			return $cookies = false;
		}
	}
	
	//podmienka či existuje event s názvom v kalendári, nefunguje správne pri určitých znakoch a diakritike no je to ošetrené (preto sa aj všetko najprv maže)
	function ExistujeEvent($client, $event, $calid)
	{
		$calid = substr(CutStr($calid,"feeds/","/private/full"),0, -1);
		$gdataCal = new Zend_Gdata_Calendar($client);
		$query = $gdataCal->newEventQuery();
		$query->setUser($calid);
		$query->setVisibility('private');
		$query->setProjection('full');
		$query->setQuery($event);
		$eventFeed = $gdataCal->getCalendarEventFeed($query);
		
		return (count($eventFeed) > 0);
	}
	
	
	//funkcia na vytvorenie eventu, v premennej client su prihlasovacie data, v appcalurl je link specialneho kalendaru do ktoreho sa bude pridavat event
	function createEvent($client, $title, $desc, $where, $startDate, $startTime, $endDate, $endTime, $tzOffset, $appCalUrl)
	{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$newEvent = $gdataCal->newEventEntry();

		$newEvent->title = $gdataCal->newTitle($title);
		$newEvent->where = array($gdataCal->newWhere($where));
		$newEvent->content = $gdataCal->newContent("$desc");
		$when = $gdataCal->newWhen();
		
		if(($startTime == "") || ($endTime == "")) //celodenny event, else od casu do casu
		{
			$when->startTime = "{$startDate}";
			$when->endTime = "{$endDate}";
		}
		else
		{
			$when->startTime = "{$startDate}T{$startTime}:00.000{$tzOffset}:00";
			$when->endTime = "{$endDate}T{$endTime}:00.000{$tzOffset}:00";
		}
		
		$newEvent->when = array($when);

		$createdEvent = $gdataCal->insertEvent($newEvent, $appCalUrl);
		return substr($createdEvent->id->text, strrpos($createdEvent->id->text,"/")+1); //return"link" na event z ktoreho sa da exportnut id eventu
	}
	
	//funkcia na zmazanie vsetkych eventov (nespoliehat sa na 100% ze zmaze iba eventy podla vyhladavaneho stringu :D, google kalendar vyhladava divne... 
	//ale nezmaze nic z ineho kalendaru cize ucel plni :) ... ma zmazat cely kalnedar pred syncom
	function DeleteAllEventsByQuery($client, $calid, $fullTextQuery)
	{
		$gdataCal = new Zend_Gdata_Calendar($client);
		$query = $gdataCal->newEventQuery();
		$calid = substr(CutStr($calid,"feeds/","/private/full"),0, -1);
		$query->setUser($calid);
		$query->setVisibility('private');
		$query->setProjection('full');
		$query->setQuery($fullTextQuery);
		$eventFeed = $gdataCal->getCalendarEventFeed($query);
		foreach ($eventFeed as $event) 
		{
			$event->delete();
		}
	}
	
	function GetISCalendar($cookies)
	{
		$ch = curl_init ("https://is.muni.cz/auth/udalosti/diar_vrat_ajax.pl");
		curl_setopt ($ch, CURLOPT_COOKIE, $cookies);
		curl_setopt ($ch, CURLOPT_RETURNTRANSFER, true);
		$output = curl_exec ($ch);    
		
		//ziskanie iba jsonu s datami bez funkcii a ineho bordelu v subore
		$output = CutStr($output,"var events =", " Tato funkce nastavi classes v bunkach kalendariku"); 
		$output = htmlspecialchars_decode(substr($output, 0, -12));
		
		return $output ;
	}
	function DecodeJson($json)
	{
		$caljson = json_decode($json);
		//cast scriptu na najdenie chyby zo stack overflow 
		if($caljson == null)
		{
			echo "Chyba pri dekódovaní JSON! Chyba:";
			switch (json_last_error())
			{
		     case JSON_ERROR_NONE:
			 echo ' - No errors';
		    break;
		    case JSON_ERROR_DEPTH:
			echo ' - Maximum stack depth exceeded';
		    break;
		    case JSON_ERROR_STATE_MISMATCH:
			echo ' - Underflow or the modes mismatch';
		     break;
		     case JSON_ERROR_CTRL_CHAR:
			 echo ' - Unexpected control character found';
		     break;
		     case JSON_ERROR_SYNTAX:
			echo ' - Syntax error, malformed JSON';
		    break;
		    case JSON_ERROR_UTF8:
		       echo ' - Malformed UTF-8 characters, possibly incorrectly encoded';
		  break;
		   default:
		      echo ' - Unknown error';
		 break;
		}
		echo PHP_EOL;
		}
		return $caljson;
	}
	
	//prekonvertovanie IS dat do spracovatelnejsej (a citatelnejsej) formy pre Google Kalendar
	Function ConvertISDataToGData($jsondata)
	{
		$eventcislo = 0;
		foreach($jsondata as $day=>$arraydna)
		{ 
			for($i = 0; $i < count($arraydna); $i++)
			{
				foreach($arraydna[$i] as $key=>$val)
				{
					if($key == "do")
					{
						if(!empty($val))
						{	
							//konvertovanie casu do Google kalendarovej podoby
							$events[$eventcislo]->dodatumutimestamp = strtotime(preg_replace('/\s+/', "", $val));
							$events[$eventcislo]->dodatumu = date("Y-m-d",strtotime(preg_replace('/\s+/', "", $val)));
							$events[$eventcislo]->docasu = date("h:i",strtotime(preg_replace('/\s+/', "", $val)));
						}
					}
					elseif($key == "od")
					{
						if(!empty($val))
						{
							//konvertovanie casu do Google kalendarovej podoby
							$events[$eventcislo]->oddatumutimestamp = strtotime(preg_replace('/\s+/', "", $val));
							$events[$eventcislo]->oddatumu = date("Y-m-d",strtotime(preg_replace('/\s+/', "", $val)));
							$events[$eventcislo]->odcasu = date("h:i",strtotime(preg_replace('/\s+/', "", $val)));
						}
					}
					elseif($key == "nazev")
					{
						//pridavanie prefixu do kalendaru a nahradenie - za _ kvoli problemovym znakom
						$events[$eventcislo]->nazev =  $config->eventprefix . preg_replace('/_+/', '-',  $val);
					}
					else
					{
						$events[$eventcislo]->$key = $val;
					}
				}
			$eventcislo++;
			}
		}
		return $events; // vracia pekne naformatovanu array
	}
	
	//ziskanie kalendaru ID, prihlasenie do google
	function GoogleLogin($user, $pass, $config)
	{
		try 
		{
			$service = Zend_Gdata_Calendar::AUTH_SERVICE_NAME;
			$client = Zend_Gdata_ClientLogin::getHttpClient($user,$pass,$service);
			$gdataCal = new Zend_Gdata_Calendar($client);
			$calFeed = $gdataCal->getCalendarListFeed();
			$noAppCal = true;
			foreach ($calFeed as $calendar) 
			{
				if($calendar -> title -> text == $config->nazovkalendaru) 
				{
					$noAppCal = false;
					$appCalUrl = $calendar->content->src;
				}
			}
			if($noAppCal) 
			{
				$appCal = $gdataCal -> newListEntry();
				$appCal -> title = $gdataCal-> newTitle($config->nazovkalendaru); 
				$own_cal = "http://www.google.com/calendar/feeds/default/owncalendars/full";
				$gdataCal->insertEvent($appCal, $own_cal);
				
				$calFeed = $gdataCal->getCalendarListFeed();
				foreach ($calFeed as $calendar) 
				{
					if($calendar -> title -> text == $config->nazovkalendaru) 
					{
						$noAppCal = false;
						$appCalUrl = $calendar->content->src;
					}
				}
			}
			$data[0] = $client;
			$data[1] = $appCalUrl;
			return $data;
		}	
		catch (Exception $e) {
			echo ('Chyba pri komunikacií s Google serverom '.  $e->getMessage(). "\n");
			return false;
		}
	}
	
	function AddToGoogleCalendar($client, $appCalUrl, $events, $config)
	{
		$pridane = array();
		try 
		{
			while(ExistujeEvent($client, $config->eventprefix, $appCalUrl)) // zmazanie vsetkych eventov
			{
				DeleteAllEventsByQuery($client,$appCalUrl, $config->eventprefix);
				sleep(10);
			}
			
			$count_events = count($events);
			While($i  < $count_events)
			{
				if($events[$i ]->typ == "z")
				{
					$events[$i ]->nazev = str_replace($config->eventprefix, $config->eventprefix . $config->skuskaprefix,$events[$i ]->nazev);
				}	
				elseif($events[$i ]->typ == "d")
				{
					$events[$i ]->nazev = str_replace($config->eventprefix, $config->eventprefix . $config->odpovednikprefix,$events[$i ]->nazev);
				}
				
				$pridane = array_map('trim', $pridane);
				$events[$i ]->nazev = trim($events[$i ]->nazev);
				
				if(!isset($events[$i ]->dodatumu))
				{
					$events[$i ]->dodatumu = $events[$i ]->oddatumu;
					$events[$i ]->docasu = "";
					$events[$i ]->odcasu = "";
				}
				elseif(!isset($events[$i ]->oddatumu))
				{
					$events[$i ]->oddatumu = $events[$i ]->dodatumu;
					$events[$i ]->docasu = "";
					$events[$i ]->odcasu = "";
				}
							
				if($events[$i ]->dodatumutimestamp - $events[$i ]->oddatumutimestamp > 2592000) // eventy dlhsie ako mesiac
				{
					$nazev = $events[$i ]->nazev;
					//vytvorenie zaciatocneho 1 denneho evenetu
					$dodatumu = $events[$i ]->dodatumu;
					$events[$i ]->dodatumu = $events[$i ]->oddatumu;
					$events[$i ]->nazev = str_replace($config->eventprefix, $config->eventprefix,$nazev );
					$events[$i ]->docasu = "";
					$events[$i ]->odcasu = "";
						
					//vytvorenie konecneho 1 denneho eventu
					$c = count($events);
					$events[$c]->dodatumu = $dodatumu;
					$events[$c]->oddatumu = $dodatumu;
					$events[$c]->nazev = str_replace($config->eventprefix, $config->eventprefix,$nazev );
					$events[$c]->popis = $events[$i ]->popis;
					$events[$c]->podtyp = $events[$i ]->podtyp;
					$events[$c]->typ = $events[$i ]->typ;
					$events[$c]->url = $events[$i ]->url;
					$count_events++;
				}
				
				$tempnazev = md5($events[$i ]->nazev . $events[$i ]->oddatumu . $events[$i ]->dodatumu);
				if(!in_array($tempnazev, $pridane))
				{
					if(($events[$i ]->podtyp == "1b") || ($events[$i ]->podtyp == "1a"))
					{
						//vdaka mazaniu vsetkcyh predtym netreba porovnavat, je to rychlejsie + preciznejsie a vsetko je potom aktualne
						//if(!ExistujeEvent($client, $events[$i ]->nazev, $appCalUrl))
						//{
							$pridane[count($pridane)] = md5($events[$i ]->nazev . $events[$i ]->oddatumu . $events[$i ]->dodatumu);
							createEvent($client, $events[$i ]->nazev,
							$events[$i ]->popis,
							$config->eventprefix,
							$events[$i]->oddatumu, $events[$i]->odcasu, $events[$i]->dodatumu, $events[$i]->docasu, $config->timezone, $appCalUrl );	
						//}
					}
					elseif($events[$i ]->podtyp == "3a")
					{
						//vdaka mazaniu vsetkcyh predtym netreba porovnavat, je to rychlejsie + preciznejsie a vsetko je potom aktualne
						//if(!ExistujeEvent($client, $events[$i ]->nazev, $appCalUrl))
						//{
							$pridane[count($pridane)] = md5($events[$i ]->nazev . $events[$i ]->oddatumu . $events[$i ]->dodatumu);
							createEvent($client, $events[$i ]->nazev,
							$events[$i ]->popis,
							$config->eventprefix,
							$events[$i]->oddatumu, $events[$i]->odcasu, $events[$i]->dodatumu, $events[$i]->docasu, $config->timezone, $appCalUrl );
						//}
					}
					//else // sviatky atd sa ignoruju a nepridavaju
					//{
					//echo $events[$i ]->podtyp . ":" . $events[$i ]->nazev . "<br/>";
					//}
				}
				$i++;
			}
			
		}
		catch (Exception $e) {
			die ('Chyba pri komunikacií s Google serverom '.  $e->getMessage(). "\n");
		}
	}
	
	?>