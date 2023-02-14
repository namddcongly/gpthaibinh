<?php
class AminoAcid 
{
    function AminoAcid ($aa) 
    {
        foreach ($aa as $k=>$v)
            $this->$k = $aa[$k];
    }
}
function readDatabase($filename) 
{
	$data = implode("", file($filename));
    $parser = xml_parser_create();
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, $data, $values, $tags);
    xml_parser_free($parser);
    foreach ($tags as $key=>$val) {
        if ($key == "url") {
            $molranges = $val;
            for ($i=0; $i < count($molranges); $i+=2) {
                $offset = $molranges[$i] + 1;
                $len = $molranges[$i + 1] - $offset;
                $tdb[] = parseMol(array_slice($values, $offset, $len));
            }
        } else {
            continue;
        }
    }
    return $tdb;
}

function parseMol($mvalues) 
{
    for ($i=0; $i < count($mvalues); $i++) {
    	if(isset($mvalues[$i]["value"]))
        	$mol[$mvalues[$i]["tag"]] = $mvalues[$i]["value"];
        else
        	$mol[$mvalues[$i]["tag"]]='';	
    }
    return new AminoAcid($mol);
}
global$db_link_esn;
//$db_link_esn=readDatabase('http://congly.esnc.net/linkmarket/getupdate.php?hour=00&mini=00&second=00&day='.date('D',time()).'&month='.date('n',time()).'&year=2013');
$G=(int)date('G',time());
if($_GET['update_link'])
file_put_contents(ROOT_PATH.'esn.link.xml',file_get_contents('http://congly.esnc.net/linkmarket/getupdate.php?hour=00&mini=00&second=00&day='.date('D',time()).'&month='.date('n',time()).'&year=2013'));
$db_link_esn=readDatabase(ROOT_PATH.'esn.link.xml');
?> 

