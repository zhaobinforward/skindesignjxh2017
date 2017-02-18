<?php

$array = array(
	0 => 'a',
	1 => 'b',
	2 => 'c',
	'a' => 0,
	'b' => 1,
	'c' => 2,
);

echo'<pre>';print_r($array);echo'</pre>';
echo'<pre>';echo@json_encode($array);echo'</pre>';
echo'<pre>';print_r(@json_decode(json_encode($array),true));echo'</pre>';
?>