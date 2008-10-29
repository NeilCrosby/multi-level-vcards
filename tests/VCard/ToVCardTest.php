<?php

class VCard_ToVCardTest extends TheCodeTrainBaseValidatorTestCase {
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
			$obj->toVCard()
		);
	}
      
    /**
     * @dataProvider VCardTestSuite::validVCardProvider
     */
	public function testOutputSameAsInputForFriends($input) {
		if ( !file_exists($input['filename']) ) {
	        $this->markTestSkipped('File: '.$input['filename']. "does not exist.");
		}
		
        $obj = new VCard($input['filename']);
		$obj->setLevel(VCard::LEVEL_FRIEND);

		$this->assertStringEqualsFile(
			$input['filename'],
			$obj->toVCard()
		);
	}
	
    /**
     * @dataProvider VCardTestSuite::validVCardProvider
     */
	public function testOutputReturnsOnlyExpectedFieldSubsetForAcquaintance($input) {
		if ( !file_exists($input['filename']) ) {
	        $this->markTestSkipped('File: '.$input['filename']. "does not exist.");
		}
		
        $obj = new VCard($input['filename']);
		$obj->setLevel(VCard::LEVEL_ACQUAINTANCE);
		$output = $obj->toVCard();
		$aOutput = explode("\n", $output);

		$prevLine = false;
		$inImage = false;
		foreach ( $aOutput as $line=>$item ) {
			if ($prevLine) {
				$posPrev = mb_strpos($prevLine, '.');
				$posCur  = mb_strpos($item, '.');
				if ( $posPrev && $posCur && mb_substr($prevLine, 0, $posPrev) == mb_substr($item, 0, $posCur) ) {
					continue;
				}
			}
			$prevLine = $item;

			$foundField = false;
			foreach ( $obj->aFilters['boilerplate'] as $rule ) {
				if (mb_strstr($item, $rule)) {
					$foundField = true;
					continue;
				}
			}
			if ( '' == trim($item) ) {
				$foundField = true;
			}
			foreach ( $obj->aFilters['everyone'] as $rule ) {
				if (mb_strstr($item, $rule)) {
					$foundField = true;
					if ('PHOTO;BASE64' == $rule) {
						$inImage = true;
					}
					continue;
				}
			}
			foreach ( $obj->aFilters['acquaintance'] as $rule ) {
				if (mb_strstr($item, $rule)) {
					$foundField = true;
					if ('PHOTO;BASE64' == $rule) {
						$inImage = true;
					}
					continue;
				}
			}
			
			if ($inImage && !$foundField) {
				// do a regex to see if this is an image line
				// 78 chars of characters preceeded by two spaces
				if (preg_match('/^\s\s.*$/', $item)) {
					$foundField = true;
				}
				
				if ( '==' == substr($item, -2) ) {
					$inImage = false;
				}
			}
			$this->assertTrue($foundField, "Line $line: $item");
		}
	}
      
    /**
     * @dataProvider VCardTestSuite::validVCardProvider
     */
	public function testOutputReturnsOnlyExpectedFieldSubsetForEveryone($input) {
		if ( !file_exists($input['filename']) ) {
	        $this->markTestSkipped('File: '.$input['filename']. "does not exist.");
		}
		
        $obj = new VCard($input['filename']);
		$obj->setLevel(VCard::LEVEL_ALL);
		$output = $obj->toVCard();
		$aOutput = explode("\n", $output);

		$prevLine = false;
		$inImage = false;
		foreach ( $aOutput as $line=>$item ) {
			if ($prevLine) {
				$posPrev = mb_strpos($prevLine, '.');
				$posCur  = mb_strpos($item, '.');
				if ( $posPrev && $posCur && mb_substr($prevLine, 0, $posPrev) == mb_substr($item, 0, $posCur) ) {
					continue;
				}
			}
			$prevLine = $item;

			$foundField = false;
			foreach ( $obj->aFilters['boilerplate'] as $rule ) {
				if (mb_strstr($item, $rule)) {
					$foundField = true;
					continue;
				}
			}
			if ( '' == trim($item) ) {
				$foundField = true;
			}
			foreach ( $obj->aFilters['everyone'] as $rule ) {
				if (mb_strstr($item, $rule)) {
					$foundField = true;
					if ('PHOTO;BASE64' == $rule) {
						$inImage = true;
					} else {
						$inImage = false;
					}
					continue;
				}
			}
			
			if ($inImage && !$foundField) {
				// do a regex to see if this is an image line
				// 78 chars of characters preceeded by two spaces
				if (preg_match('/^\s\s.*$/', $item)) {
					$foundField = true;
				}
				
				if ( '==' == substr($item, -2) ) {
					$inImage = false;
				}
			}
			$this->assertTrue($foundField, "Line $line: $item");
		}

	}
      
}
?>