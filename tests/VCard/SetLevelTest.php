<?php

class VCard_SetLevelTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
	}

    public function tearDown() {
    }

    /**
     * @dataProvider VCardTestSuite::validLevelProvider
     */
	 public function testReturnsTrueIfValidLevelInput( $input ) {
		$obj = new VCard('../testdata/test1.vcf');
	    $this->assertTrue($obj->setLevel($input));
	 }
   
    /**
     * @dataProvider VCardTestSuite::invalidLevelProvider
     */
	 public function testReturnsFalseIfInvalidLevelInput( $input ) {
		$obj = new VCard('../testdata/test1.vcf');
	    $this->assertFalse($obj->setLevel($input));
	 }
   
}
?>