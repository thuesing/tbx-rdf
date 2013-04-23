<?php
# https://github.com/semsol/arc2/wiki/Turtle-Templates
# @see http://data-gov.tw.rpi.edu/wiki/ARC2

include_once('arc2/ARC2.php');

$template = '
  ?res a rss:item ;
     dc:title ?title ;
     rss:link ?link ;
     dc:creator ?creator ;
     rss:description ?description ;
     dc:date ?now .
';

$vals = $_POST;
$vals['link'] = "http://graves.cl/foaf.rdf#me";
$vals['now'] = date('Y-m-d', time());
$vals['creator']  = "Alvaro Graves";
$vals['description']  = "Lorem ipsum dolor.";

# new stdClass
$conf = array('ns' => array('foaf' => 'http://xmlns.com/foaf/0.1/',
							'rss' => 'http://xmlns.com/rss/0.1/',
							'dc' => 'http://xmlns.com/dc/0.1/'
							)
);


$obj = ARC2::getComponent('Class', $conf); /* any component will do */
$index = $obj->getFilledTemplate($template, $vals);
$turtle = $obj->toTurtle($index);

print_r($turtle);

 #echo $turtle;
 echo "\n";


 /*
 ARC2_class 420

   function getFilledTemplate($t, $vals, $g = '') {
    $parser = ARC2::getTurtleParser();
    $parser->parse($g, $this->getTurtleHead() . $t);
    return $parser->getSimpleIndex(0, $vals);

    */