<?php

class TheCodeTrainBaseTestSuite extends PHPUnit_Framework_TestSuite {
    public static function main() {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }
 
    protected function getTests( $baseFile ) {
        $return = array();
        
        $pathParts = pathinfo($baseFile);
        $dir = $pathParts['dirname'];
        $class = $pathParts['filename'];
        
        $includeDir = substr( $class, 0, -strlen('TestSuite') );
        
        if ($handle = opendir("$dir/$includeDir")) {
            while (false !== ($file = readdir($handle))) {
                if ($file != "." && $file != "..") {
                    if ( 'Test.php' == substr($file, -strlen('Test.php')) ) {
                        array_push($return, $includeDir.'_'.substr($file, 0, -strlen('.php')));
                    }
                }
            }
            closedir($handle);
        }
        
        return $return;
    }
 
	protected function autoload($class) {
		$class = str_replace( '_', '/', $class );
	    $aLocations = array('../../../classes', '../../config', '.');

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

    protected function setUp() {
//		spl_autoload_register('TheCodeTrainBaseTestSuite::autoload');
    }
 
    protected function tearDown() {
//		spl_autoload_unregister('TheCodeTrainBaseTestSuite::autoload');
    }

}

?>