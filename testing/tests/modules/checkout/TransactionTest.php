<?php

require_once(ASCMS_MODULE_PATH.'/checkout/lib/Transaction.class.php');
require_once(ASCMS_DOCUMENT_ROOT.'/testing/testCases/MySQLTestCase.php');

class TransactionTest extends MySQLTestCase {

	private function getTestData() {
		return array(
			'status' => 'waiting',
			'time' => time(),
			'invoice_number' => 789456123,
			'invoice_currency' => 1,
			'invoice_amount' => 123456,
			'contact_title' => 'mister',
			'contact_forename' => 'Hans',
			'contact_surname' => 'Muster',
			'contact_company' => 'Muster AG',
			'contact_street' => 'Musterstrasse 1',
			'contact_postcode' => '1234',
			'contact_place' => 'Musterort',
			'contact_country' => 'Switzerland',
			'contact_phone' => '012 345 67 89',
			'contact_email' => 'info@example.com',
		);
	}

	public function testAddAndGet() {
		$objTransaction = new Transaction(self::$database);

		$arrInput = $this->getTestData();
		$arrInput['id'] = $objTransaction->add(
			$arrInput['status'],
			$arrInput['invoice_number'],
			$arrInput['invoice_currency'],
			$arrInput['invoice_amount'],
			$arrInput['contact_title'],
			$arrInput['contact_forename'],
			$arrInput['contact_surname'],
			$arrInput['contact_company'],
			$arrInput['contact_street'],
			$arrInput['contact_postcode'],
			$arrInput['contact_place'],
			204,
			$arrInput['contact_phone'],
			$arrInput['contact_email']
		);

		$arrOutput = $objTransaction->get(array($arrInput['id']));

		$this->assertEquals($arrInput, $arrOutput[0]);
	}

	public function testUpdate() {
		$objTransaction = new Transaction(self::$database);

		$arrInput = $this->getTestData();
		$arrInput['id'] = $objTransaction->add(
			$arrInput['status'],
			$arrInput['invoice_number'],
			$arrInput['invoice_currency'],
			$arrInput['invoice_amount'],
			$arrInput['contact_title'],
			$arrInput['contact_forename'],
			$arrInput['contact_surname'],
			$arrInput['contact_company'],
			$arrInput['contact_street'],
			$arrInput['contact_postcode'],
			$arrInput['contact_place'],
			204,
			$arrInput['contact_phone'],
			$arrInput['contact_email']
		);

		$newStatus = 'confirmed';
		$objTransaction->updateStatus($arrInput['id'], $newStatus);

		$arrOutput = $objTransaction->get(array($arrInput['id']));

		$this->assertEquals($arrOutput[0]['status'], $newStatus);
	}

	public function testDelete() {
		$objTransaction = new Transaction(self::$database);

		$arrInput = $this->getTestData();
		$arrInput['id'] = $objTransaction->add(
			$arrInput['status'],
			$arrInput['invoice_number'],
			$arrInput['invoice_currency'],
			$arrInput['invoice_amount'],
			$arrInput['contact_title'],
			$arrInput['contact_forename'],
			$arrInput['contact_surname'],
			$arrInput['contact_company'],
			$arrInput['contact_street'],
			$arrInput['contact_postcode'],
			$arrInput['contact_place'],
			204,
			$arrInput['contact_phone'],
			$arrInput['contact_email']
		);

		$objTransaction->delete($arrInput['id']);

		$this->assertFalse($objTransaction->get(array($arrInput['id'])));
	}

}

