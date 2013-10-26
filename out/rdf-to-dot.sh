#!/bin/bash
#rapper -o dot tbx:models.2013-09-03-E3ME.xml | neato -Tpng -Goverlap=scale -o rapper-E3ME.test.png
rapper -o dot impact_areas.rdf | dot -Tpng -o output.png