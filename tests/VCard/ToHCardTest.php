<?php

class VCard_ToHCardTest extends TheCodeTrainBaseValidatorTestCase {
    public function setUp() {
    }

    public function tearDown() {
    }

    /**
     * @dataProvider VCardTestSuite::validVCardProvider
     */
	public function testReturnsAString($input) {
		if ( !file_exists($input['filename']) ) {
	        $this->markTestSkipped('File: '.$input['filename']. "does not exist.");
		}
		
        $obj = new VCard($input['filename']);
		$obj->setLevel($input['level']);

		$this->assertType(
			PHPUnit_Framework_Constraint_IsType::TYPE_STRING,
			$obj->toHCard()
		);
	}
      
    /**
     * @dataProvider VCardTestSuite::validVCardProvider
     */
	public function testReturnsWellFormedHtml($input) {
		if ( !file_exists($input['filename']) ) {
	        $this->markTestSkipped('File: '.$input['filename']. "does not exist.");
		}
		
        $obj = new VCard($input['filename']);
		$obj->setLevel($input['level']);

		$this->assertFalse($this->getValidationError($obj->toHCard()));
	}
      
}
?>