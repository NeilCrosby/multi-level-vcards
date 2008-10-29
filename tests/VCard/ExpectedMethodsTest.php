<?php

class VCard_ExpectedMethodsTest extends TheCodeTrainBaseExpectedMethodsTestCase {
    public function setUp() {
        $this->class = 'VCard';
        $this->classFile = __FILE__;
    }

    public function tearDown() {
    }

    public static function expectedMethodsProvider() {
        return array(
            array('__construct'),
            array('toVCard'),
            array('toHCard'),
            array('setLevel'),
        );
    }
}
?>