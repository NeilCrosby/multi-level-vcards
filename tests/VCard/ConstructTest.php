<?php

class VCard_ConstructTest extends PHPUnit_Framework_TestCase {
    public function setUp() {
    }

    public function tearDown() {
    }

    /**
     * @dataProvider VCardTestSuite::validVCardProvider
     */
	 public function testInstantiatesIfValidInputGiven( $input ) {
		if ( !file_exists($input['filename']) ) {
	        $this->markTestSkipped();
		}

	    $obj = new VCard($input['filename']);
		$this->assertThat(
			$obj,
			$this->isInstanceOf('VCard')
		);
	 }
   
	 public function testDoesNotInstantiateIfInvalidInputGiven( ) {
        $this->setExpectedException('Exception');
	    $obj = new VCard();
		$this->assertThat(
			$obj,
			$this->logicalNot(
				$this->isInstanceOf('VCard')
			)
		);
	 }
   
}
?>