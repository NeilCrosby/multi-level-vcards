<?php

class TheCodeTrainBaseExpectedMethodsTestCase extends PHPUnit_Framework_TestCase
{
    public function testPublicApiOnlyContainsExpectedMethods() {
        $tempExpectedMessages = $this->expectedMethodsProvider();
        $expectedMessages = array();
        foreach ( $tempExpectedMessages as $temp ) {
            $expectedMessages[] = $temp[0];
        }
        
        $actualMethods = get_class_methods($this->class);

        foreach ( $expectedMessages as $method ) {
            $this->assertTrue( 
                in_array( $method, $actualMethods ), 
                $this->class."->$method() does not exist publically" 
            );
        }

        foreach ( $actualMethods as $method ) {
            $this->assertTrue( 
                in_array( $method, $expectedMessages ), 
                $this->class."->$method() exists publically(), but should not" 
            );
        }

        $this->assertEquals( sizeof($expectedMessages), sizeof($actualMethods) );
    }
    
    /**
     * @dataProvider expectedMethodsProvider
     */
    public function testATestCaseExistsForEachExpectedMethod($input) {
        if ( '__' == substr($input, 0, 2) ) {
            $input = substr($input, 2);
        }
        
        $expectedFilename = ucfirst($input).'Test.php';
        
        $pathParts = pathinfo($this->classFile);
        $dir = $pathParts['dirname'];
        $basename = $pathParts['basename'];
        
        $foundTestCase = false;
        
        if ($handle = opendir($dir)) {
            while (false !== ($file = readdir($handle))) {
                //echo "$expectedFilename $file\n";
                if ($file != "." && $file != ".." && $file != $basename ) {
                    if ( $expectedFilename == $file ) {
                        //echo "It's true!\n";
                        $foundTestCase = true;
                    }
                }
            }
            closedir($handle);
        }
        
        $this->assertTrue($foundTestCase, "Did not find $dir/$expectedFilename");
        
    }
}
?>