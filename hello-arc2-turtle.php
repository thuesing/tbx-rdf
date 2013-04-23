<?php
# https://github.com/semsol/arc2/wiki/Turtle-Templates
# @see http://data-gov.tw.rpi.edu/wiki/ARC2

include_once('arc2/ARC2.php');

 $aux = array();
 
 //Subject
 $aux['s'] = "http://graves.cl/foaf.rdf#me";
 $aux['s_type'] = 'uri';
 
 //Predicate
 $aux['p'] = "http://xmlns.com/foaf/0.1/name";
 
 //Object
 $aux['o'] = "Alvaro Graves";
 $aux['o_type'] = "literal";
 $aux['o_datatype'] = "xsd:string";
 $aux['o_lang'] = "es";
 
 
 $triples = array();
 array_push($triples, $aux);
 
 $conf = array('ns' => array('foaf' => 'http://xmlns.com/foaf/0.1/'));


/* Serializer instantiation */
$ser = ARC2::getTurtleSerializer();

/* Serialize a triples array */
$doc = $ser->getSerializedTriples($triples);

 echo $doc;
 echo "\n";