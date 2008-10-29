<?php

require_once('TheCodeTrainBaseTestSuite.php');

function __autoload($class) {
	$class = str_replace( '_', '/', $class );
    $aLocations = array('..', '.');

    foreach( $aLocations as $location ) {
        $file = "$location/$class.php";
        if ( file_exists( $file ) ) {
            include_once( $file );
            return;
        }
    }

    // Check to see if we managed to declare the class
    if (!class_exists($class, false)) {
        trigger_error("Unable to load class: $class", E_USER_WARNING);
    }
}

class VCardTestSuite extends TheCodeTrainBaseTestSuite {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    public static function suite() {
        $tests = self::getTests(__FILE__);

        $suite = new VCardTestSuite();
        foreach ( $tests as $test ) {
			$suite->addTestSuite($test);
        }

        return $suite;
    }
    
    protected function setUp() {
    }
 
    protected function tearDown() {
    }

    public static function validVCardProvider() {
        return array(
            array(array('filename'=>'../testdata/test1.vcf', 'level'=>0)),
            array(array('filename'=>'../testdata/test1.vcf', 'level'=>5)),
            array(array('filename'=>'../testdata/test1.vcf', 'level'=>10)),
            array(array('filename'=>'../testdata/test2.vcf', 'level'=>10)),
        );
    }
 
    public static function validLevelProvider() {
        return array(
            array(0),
            array(5),
            array(10),
        );
    }

    public static function invalidLevelProvider() {
        return array(
            array(-1),
            array(2),
            array("seven"),
			array('12'),
        );
    }
 
}

if (PHPUnit_MAIN_METHOD == 'VCardTestSuite::main') {
     VCardTestSuite::main();
}
?>