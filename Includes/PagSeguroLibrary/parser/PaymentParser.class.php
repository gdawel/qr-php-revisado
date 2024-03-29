<?php
	
class PaymentParser extends ServiceParser {
	
	public static function getData($payment) {
		
		// reference
		if ($payment->getReference() != null) {
			$data["reference"] = $payment->getReference();
		}			
		
		// sender
		if ($payment->getSender() != null) {
			
			if ($payment->getSender()->getName() != null) {
				$data['senderName'] = $payment->getSender()->getName();
			}
			if ($payment->getSender()->getEmail() != null) {
				$data['senderEmail'] = $payment->getSender()->getEmail();
			}

			// phone
			if ($payment->getSender()->getPhone() != null) {
				if ($payment->getSender()->getPhone()->getAreaCode() != null) {
					$data['senderAreaCode'] = $payment->getSender()->getPhone()->getAreaCode();
				}
				if ($payment->getSender()->getPhone()->getNumber() != null) {
					$data['senderPhone'] = $payment->getSender()->getPhone()->getNumber();
				}
			}
			
		}
		
		// currency
		if ($payment->getCurrency() != null) {
			$data['currency'] = $payment->getCurrency();
		}

		// items
		$items = $payment->getItems();
		if (count($items) > 0) {
			
			$i = 0;
			
			foreach ($items as $key => $value) {
				$i++;
				if ($items[$key]->getId() != null) {
					$data["itemId$i"] = $items[$key]->getId();
				}
				if ($items[$key]->getDescription() != null) {
					$data["itemDescription$i"] = $items[$key]->getDescription();
				}
				if ($items[$key]->getQuantity() != null) {
					$data["itemQuantity$i"] = $items[$key]->getQuantity();
				}
				if ($items[$key]->getAmount() != null) {
					$amount = PagSeguroHelper::decimalFormat($items[$key]->getAmount());
					$data["itemAmount$i"] = $amount;
				}
				if ($items[$key]->getWeight() != null) {
					$data["itemWeight$i"] = $items[$key]->getWeight();
				}
				if ($items[$key]->getShippingCost() != null) {
					$data["itemShippingCost$i"] = PagSeguroHelper::decimalFormat($items[$key]->getShippingCost());
				}
			}
			
		}
		
		// extraAmount
		if ($payment->getExtraAmount() != null) {
			$data['extraAmount'] = PagSeguroHelper::decimalFormat($payment->getExtraAmount());
		}

		// shipping
		if ($payment->getShipping() != null) {
			
			if ($payment->getShipping()->getType() != null && $payment->getShipping()->getType()->getValue() != null) {
				$data['shippingType'] = $payment->getShipping()->getType()->getValue();
			}

			// address
			if ($payment->getShipping()->getAddress() != null) {
				if ($payment->getShipping()->getAddress()->getStreet() != null) {
					$data['shippingAddressStreet'] = $payment->getShipping()->getAddress()->getStreet();
				}
				if ($payment->getShipping()->getAddress()->getNumber() != null) {
					$data['shippingAddressNumber'] = $payment->getShipping()->getAddress()->getNumber();
				}
				if ($payment->getShipping()->getAddress()->getComplement() != null) {
					$data['shippingAddressComplement'] = $payment->getShipping()->getAddress()->getComplement();
				}
				if ($payment->getShipping()->getAddress()->getCity() != null) {
					$data['shippingAddressCity'] = $payment->getShipping()->getAddress()->getCity();
				}
				if ($payment->getShipping()->getAddress()->getState() != null) {
					$data['shippingAddressState'] = $payment->getShipping()->getAddress()->getState();
				}
				if ($payment->getShipping()->getAddress()->getDistrict() != null) {
					$data['shippingAddressDistrict'] = $payment->getShipping()->getAddress()->getDistrict();
				}
				if ($payment->getShipping()->getAddress()->getPostalCode() != null) {
					$data['shippingAddressPostalCode'] = $payment->getShipping()->getAddress()->getPostalCode();
				}
				if ($payment->getShipping()->getAddress()->getCountry() != null) {
					$data['shippingAddressCountry'] = $payment->getShipping()->getAddress()->getCountry();
				}
			}
			
		}

		// maxAge
		if ($payment->getMaxAge() != null) {
			$data['maxAge'] = $payment->getMaxAge();
		}
		// maxUses
		if ($payment->getMaxUses() != null) {
			$data['maxUses'] = $payment->getMaxUses();
		}
		
		// redirectURL
		if ($payment->getRedirectURL() != null) {
			$data['redirectURL'] = $payment->getRedirectURL();
		}

		return $data;
		
	}
	
	public static function readSuccessXml($str_xml) {
		$parser = new xmlParser($str_xml);
		$data = $parser->getResult('checkout');
		$PaymentParserData = new PaymentParserData();
		$PaymentParserData->setCode($data['code']);
		$PaymentParserData->setRegistrationDate($data['date']);
		return $PaymentParserData;
	}
	
}


?>