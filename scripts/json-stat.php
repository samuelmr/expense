<?php
/*
	JSON-stat PHP Sample Code
	http://json-stat.org/tools/php.txt
	Author: Xavier Badosa (http://xavierbadosa.com)
	Date: 2015-12-22
	Version: 1.0.1

	Copyright 2015 Xavier Badosa
	License: Apache License, Version 2.0 http://www.apache.org/licenses/LICENSE-2.0
*/

////Functions

//getValue() converts a dimension/category assoc array into a data value in three steps.
//Input example: array('concept'=>'UNR','area'=>'US','year'=>'2010')
//Output example: 9.627692959
function getValue( $jsonstat , $query ){

	//1. array('concept'=>'UNR','area'=>'US','year'=>'2010') ==> array(0, 33, 7)
	$indices=getDimIndices( $jsonstat , $query );

	//2. array(0, 33, 7) ==> 403
	$index=getValueIndex( $jsonstat , $indices );

	//3. 403 ==> 9.627692959
	$value=getValueByIndex( $jsonstat->value , $index );

	return $value;
}

//getDimIndices() converts a dimension/category assoc array into an array of dimensions' indices.
//Input example: array('concept'=>'UNR','area'=>'US','year'=>'2010')
//Output example: array(0, 33, 7)
function getDimIndices( $jsonstat , $query ){
	$dim=$jsonstat->dimension;
	//JSON-stat 2.0-ready
	$ids=( isset( $jsonstat->id ) ) ? $jsonstat->id : $dim->id;
	$ndims=count( $ids );

	$arr=array();
	for( $i=0; $i<$ndims ; $i++ ){
		$arr[$i]=getDimIndex( $dim , $ids[$i] , $query[$ids[$i]] );
	}

	return $arr;
}

//getValueIndex() converts an array of dimensions' indices into a numeric value index.
//Input example: array(0, 33, 7)
//Output example: 403
function getValueIndex( $jsonstat , $indices ){
	//JSON-stat 2.0-ready
	$size=( isset( $jsonstat->size ) ) ? $jsonstat->size : $jsonstat->dimension->size;
	$ndims=count( $size );
	$num=0;
	$mult=1;

	for( $i=0; $i<$ndims; $i++ ){
		$mult*=( $i>0 ) ? $size[$ndims-$i] : 1;
		$num+=$mult*$indices[$ndims-$i-1];
	}
	return $num;
}

//getDimIndex() converts a dimension ID string and a category ID string into the numeric index of that category in that dimension.
//Input example: "area", "US"
//Output example: 33
function getDimIndex( $dim , $name , $value ){
	//In single category dimensions, "index" is optional
	if( !isset( $dim->$name->category->index ) ){
		return 0;
	}

	$ndx=$dim->$name->category->index;

	//"index" can be an object or an array
	if( is_object( $ndx ) ){ //Object
		return $ndx->$value;
	}else{ //Array
		return array_search( $value , $ndx , TRUE );
	}
}

//getValueByIndex() converts a numeric value index into its data value.
//Input example: 403
//Output example: 9.627692959
function getValueByIndex( $val , $index ){
	//"value" can be an array or an object (sparse cube)
	return
		is_array( $val ) ?
		$val[$index] :
		$val->$index
	;
	//This check is avoidable if JSON is read into a
	//PHP assoc array (instead of an object). This is
	//not recommended, though, as the distinction
	//between assoc array and sequential array in
	//getDimIndex() would be less safe.
}

//JSONstat() connects to a URL and, if the response is valid JSON-stat, returns a PHP object.
function JSONstat( $url ){
	# $resp=file_get_contents( $url );
	$resp=http_get( $url );
	if( $resp===FALSE ){
		exit( 'Error: the contents of ' . $url . ' could not be retrieved.' . "\n" );
	}

	//Convert into object (instead of assoc array: safer to detect if category index is array or not)
	$jsonstat=json_decode( $resp );
	if( $jsonstat===NULL ){
		exit( 'Error: response was not valid JSON.' . "\n" );
	}

	//If no "class", "bundle" response:
	//use the first dataset available
	//(assuming single dataset bundle response)
	//[Of course, it'd be better to add an argument
	//to the function to pass a dataset ID if
	//bundle responses must be properly supported.]
	if( !isset( $jsonstat->class ) ){
		# $dsname=each( $jsonstat )['key'];
		# var_dump(get_object_vars($jsonstat));
		$dsname=key(get_object_vars($jsonstat));
		$jsonstat=$jsonstat->$dsname;
	}else{ //Verify it's a "dataset" response
		if( $jsonstat->class!='dataset' ){
			exit( 'Error: response was not a JSON-stat bundle or dataset response.' . "\n" );
		}
	}

	//Program requires "value" and "dimension" properties
	if( !isset( $jsonstat->value ) || !isset( $jsonstat->dimension ) ){
		exit( 'Error: response is not valid JSON-stat or does not contain required information.' . "\n" );
	}

	return $jsonstat;
}

?>
