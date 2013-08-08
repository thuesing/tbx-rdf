<?php
    /**
     * @depends http://www.easyrdf.org/
     * @see examples/graph_direct.php example
     * validate here: http://www.rdfabout.com/demo/validator/
     * php demo.php > tbx:models.$(date +"%Y-%m-%d-%T").turtle
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

foreach ($json['items'] as $item) {
  map_item($graph, $item);
}

// other formats: ntriples, rdfxml
$data = $graph->serialise('turtle');
if (!is_scalar($data)) {
    $data = var_export($data, true);
}

print($data);

/*
 * @params $graph easyrdf graph obj
 * @params $item json item
 */
function map_item(&$graph, &$item) { 

  # preprocess item   
    $resource_uri = 'tbx:'.$item['url']; # single resource URI
    $resource_type = 'tbx:'.$item['type'];

    unset($item['url']);
    unset($item['type']);

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

    # set editor if null
    $item['editor'] = isset($item['editor']) ?: $item['author'];
    $graph->addResource($resource_uri, "tbx:editor", 'tbx:' . $item['editor']);
    unset($item['editor']);
    unset($item['author']); # the machine
  # end preprocess item 

  # process plain literals
    foreach ($item as $key => &$values) {
        if(!is_array($values)) {
            $values = array($values); 
        } 
        foreach ($values as $val) {
            $graph->addLiteral($resource_uri, 'tbx:'.$key, $val);
        }
    }

}


?>
