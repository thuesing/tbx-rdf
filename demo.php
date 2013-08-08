<?php
    /**
     * Basic serialisation example
     *
     * This example create a simple FOAF graph in memory and then
     * serialises it to the page in the format of choice.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . 'easyrdf/lib/');
    require_once "EasyRdf.php";

# load node
$string = file_get_contents("model.exhibit.json");
$json=json_decode($string,true);

# test item 84
# ECME 6
$item = $json['items'][6];

#print_r(array_keys($json['items']));
#exit;
EasyRdf_Namespace::set('tbx', 'http://beta.liaise-toolbox.eu/');


$graph = new EasyRdf_Graph();

# preprocess item   
$resource_uri = 'tbx:'.$item['url']; # single resource URI
$resource_type = 'tbx:'.$item['type'];

unset($item['url']);
unset($item['type']);
unset($item['author']); # the machine

# unset html text  for debugging
unset($item['field_body']);
unset($item['field_enduser_documentation']);
unset($item['field_scientific_documentation']);
unset($item['field_input']);
unset($item['field_output']);
unset($item['field_contact']);
# process item 

#$graph->setType('tbx:'.$item['url'],  'tbx:'.$item['type']); same as
$graph->addResource($resource_uri, "rdf:type", $resource_type);

# experimental process resource
$graph->addResource($resource_uri, "tbx:editor", 'tbx:' . $item['editor']);
unset($item['editor']);

# process literals
foreach ($item as $key => &$values) {
    if(!is_array($values)) {
        $values = array($values); 
    } 
    foreach ($values as $val) {
        $graph->addLiteral($resource_uri, 'tbx:'.$key, $val);
    }
}

/*
# from graph_direct.php example

  $graph->addResource("http://example.com/joe", "rdf:type", "foaf:Person");
  $graph->addLiteral("http://example.com/joe", "foaf:name", "Joe Bloggs");
  $graph->addLiteral("http://example.com/joe", "foaf:name", "Joseph Bloggs");
  $graph->add("http://example.com/joe", "rdfs:label", "Joe");

  $graph->setType("http://njh.me/", "foaf:Person");
  $graph->add("http://njh.me/", "rdfs:label", "Nick");
  $graph->addLiteral("http://njh.me/", "foaf:name", "Nicholas Humfrey");
  $graph->addResource("http://njh.me/", "foaf:homepage", "http://www.aelius.com/njh/");

*/


// ntriples, rdfxml

$data = $graph->serialise('turtle');
if (!is_scalar($data)) {
    $data = var_export($data, true);
}


print($data);
?>
