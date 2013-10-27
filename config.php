<?php
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
V1.0
Script created by Juraj Nyíri, 29.10.2013.
New versions with changelogs at http://cal.jurajnyiri.eu .
My portfolio: jurajnyiri.eu. Contact: juraj.nyiri@gmal.com

You are free to modify source code (and update changes and modifications to cal.jurajnyiri.eu).
You are FORBIDDEN to modify or delete this comment about author etc.
If you copy substantial portions from this code you also have to copy this notice.
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

//Zadanie prihlasovacich udajov
$username->IS = ""; // priklad 123456
$username->Google = ""; // priklad user@gmail.com
$password->IS = ""; //heslo do IS
$password->Google = ""; //heslo do gmailu, alebo heslo pre aplikaciu na google account

//dalsie nastavenia
$config->agent = "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/29.0.1547.62 Safari/537.36";
$config->timezone = "+01"; // musi byt vo formate [+/-]XX !!! priklady: +02, -08, +11 ...
$config->eventprefix = "[IS]";
$config->skuskaprefix = "[!SKÚŠKA!]";
$config->odpovednikprefix = "[O]";
$config->nazovkalendaru = "IS Udalosti";

//odporucam vytvorit cron na automaticke spustanie scriptu.
?>
