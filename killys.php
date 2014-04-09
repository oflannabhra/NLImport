<?php

$fi = fopen("/Users/benflannery/Desktop/WY.E&O.ELINK.TXT", "r+");
$filename2 = "/Users/benflannery/Desktop/WY.TXT";
if (!$fi2 = fopen($filename2, "w+")){
	file_put_contents($filename2, "");
	$fi2 = fopen($filename2, "w+");
}
 while ($line = fgets($fi))
 {
 	$line = utf8_encode($line);
 	$line2 = str_replace('ý', '*', $line);
 	fputs($fi2, $line2);
 }

?>