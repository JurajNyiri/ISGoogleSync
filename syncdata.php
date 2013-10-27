<?php
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
V1.0
Script created by Juraj Nyíri, 29.10.2013.
New versions with changelogs at http://cal.jurajnyiri.eu .
My portfolio: jurajnyiri.eu. Contact: juraj.nyiri@gmal.com

You are free to modify source code (and update changes and modifications to cal.jurajnyiri.eu).
You are FORBIDDEN to modify or delete this comment about author etc.
If you copy substantial portion from this code you also have to copy this notice.
Please respect this :).

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

if (is_file('run.txt')) die('Run.txt exists.'); // proti viacnasobnemu spusteniu
file_put_contents('run.txt', '');

	include "config.php";
	include "functions.php";
	set_time_limit(86400); //maximálny čas na exekúciu jeden deň, defaultných 30 sekúnd by občas nestačilo, záleží od rýchlosti serverov
	require_once 'Zend/Loader.php';
	Zend_Loader::loadClass('Zend_Gdata');
	Zend_Loader::loadClass('Zend_Gdata_AuthSub');
	Zend_Loader::loadClass('Zend_Gdata_ClientLogin');
	Zend_Loader::loadClass('Zend_Gdata_Calendar');

	
	
	//ziskanie cookies po prihlaseni
	$cookies = Login($username->IS,$password->IS);
	
	if($cookies !== false) //prihlasenie do IS uspesne
	{
		//získanie kalendar eventov
		$iskalendar = GetISCalendar($cookies);
		
		//spracovanie jsonu
		$caljson = DecodeJson($iskalendar);
		
		//prekonvertovanie IS dat do spracovatelnejsej (a citatelnejsej) formy pre Google Kalendar
		$events = ConvertISDataToGData($caljson);
		
		//prihlasenie sa do google a ziskanie kalendar adresy
		$data = GoogleLogin($username->Google, $password->Google, $config);
		
		if($data !== false) // prihlasenie do Google uspesne
		{
			$appCalUrl = $data[1];
			$client = $data[0];
			//odoslanie dat na google server
			AddToGoogleCalendar($client, $appCalUrl, $events, $config);
		}
	}
unlink('run.txt');
?>
