<?php
    /**
     * Using EasyRdf_Graph directly without EasyRdf_Resource
     *
     * Triple data is inserted and retrieved directly from a graph object,
     * where it is stored internally as an associative array.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2011 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

  set_include_path('easyrdf-0.7.2/lib/');
    #require_once "easyrdf-0.7.2/lib/EasyRdf.php";
  require_once "EasyRdf.php";

  $graph = new EasyRdf_Graph();
  $graph->addResource("http://example.com/joe", "rdf:type", "foaf:Person");
  $graph->addLiteral("http://example.com/joe", "foaf:name", "Joe Bloggs");
  $graph->addLiteral("http://example.com/joe", "foaf:name", "Joseph Bloggs");
  $graph->add("http://example.com/joe", "rdfs:label", "Joe");

  $graph->setType("http://aelius.com/njh#me", "foaf:Person");
  $graph->add("http://aelius.com/njh#me", "rdfs:label", "Nick");
  $graph->addLiteral("http://aelius.com/njh#me", "foaf:name", "Nicholas Humfrey");
  $graph->addResource("http://aelius.com/njh#me", "foaf:homepage", "http://aelius.com/njh");


  print_r($graph->toArray());




  // Lookup the output format
        $format = EasyRdf_Format::getFormat('turtle');

        // Serialise to the new output format
        $output = $graph->serialise($format);


print_r($output);

            #print '<pre>'.htmlspecialchars($output).'</pre>';


?>
