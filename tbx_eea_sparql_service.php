<?php
    /**
     * Making a SPARQL SELECT query
     *
     * This example creates a new SPARQL client, pointing at the
     * dbpedia.org endpoint. It then makes a SELECT query that
     * returns all of the countries in DBpedia along with an
     * english label.
     *
     * Note how the namespace prefix declarations are automatically
     * added to the query.
     *
     * @package    EasyRdf
     * @copyright  Copyright (c) 2009-2013 Nicholas J Humfrey
     * @license    http://unlicense.org/
     */

    set_include_path(get_include_path() . PATH_SEPARATOR . 'easyrdf/lib/');
    require_once "easyrdf/lib/EasyRdf.php";

    EasyRdf_Namespace::set('a', 'http://www.eea.europa.eu/portal_types/Data#');
    EasyRdf_Namespace::set('dcterms', 'http://purl.org/dc/terms/');

    $sparql = new EasyRdf_Sparql_Client('http://semantic.eea.europa.eu/sparql');

    $result = $sparql->query(
        'SELECT DISTINCT ?subj ?issued ?title  ?description WHERE {'.
          '?subj a a:Data ;'.
                'dcterms:title ?title ;'.
                'dcterms:description ?description;'.
                'dcterms:issued ?issued.'.
          'FILTER (regex(?title,"climate") || regex(?description,"climate")).'.
        '} ORDER BY ?effective'
    );
    foreach ($result as $row) {
        //echo "<li>".$row->title."</li>\n";
    }


print_r($result); 


?>