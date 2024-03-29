<?php
/**
 * Defines a list of known payment method codes.
 */	
class PaymentMethodCode {
	
	private static $codeList = array(
		
		/**
		 * VISA
		 */
		'VISA_CREDIT_CARD' => 101,

		/**
		 * MasterCard
		 */
		'MASTERCARD_CREDIT_CARD' => 102,

		/**
		 * American Express
		 */
		'AMEX_CREDIT_CARD' => 103,

		/**
		 * Diners
		 */
		'DINERS_CREDIT_CARD' => 104,

		/**
		 * Hipercard
		 */
		'HIPERCARD_CREDIT_CARD' => 105,

		/**
		 * Aura
		 */
		'AURA_CREDIT_CARD' => 106,
		
		/**
		 * Elo
		 */
		'ELO_CREDIT_CARD' => 107,		
		
		/**
		 * Bradesco - boleto -  is a form of invoicing in Brazil
		 */
		'BRADESCO_BOLETO' => 201,

		/**
		 * Santander - boleto -  is a form of invoicing in Brazil
		 */
		'SANTANDER_BOLETO' => 202,

		/**
		 * Bradesco on-line transfer
		 */
		'BRADESCO_ONLINE_TRANSFER' => 301,

		/**
		 * Itau on-line transfer
		 */
		'ITAU_ONLINE_TRANSFER' => 302,

		/**
		 * Unibanco on-line transfer
		 */
		'UNIBANCO_ONLINE_TRANSFER' => 303,

		/**
		 * Banco do Brasil on-line transfer
		 */
		'BANCO_BRASIL_ONLINE_TRANSFER' => 304,

		/**
		 * Banco Real on-line transfer
		 */
		'REAL_ONLINE_TRANSFER' => 305,

		/**
		 * Banrisul on-line transfer
		 */
		'BANRISUL_ONLINE_TRANSFER' => 306,

		/**
		 * PagSeguro account balance
		 */
		'PS_BALANCE' => 401,

		/**
		 * OiPaggo
		 */
		'OI_PAGGO' => 501
		
	);
	
	/**
	 * Payment method code
	 * Example: 101
	 */
	private $value;
	
	public function __construct($value = null){
		if ($value) {
			$this->value = $value;
		}
	}
	
	public function setValue($value) {
		$this->value = $value;
	}	
	
	public function setByType($type) {
		if (isset(self::$codeList[$type])) {
			$this->value = self::$codeList[$type];
		} else {
			throw new Exception("undefined index $type");
		}
	}
	
	/**
	 * @return the payment method code value
	 * Example: 101
	 */
	public function getValue(){
		return $this->value;
	}
	
	/**
	 * @param value
	 * @return the PaymentMethodCode corresponding to the informed value
	 */
	public function getTypeFromValue($value = null) {
		$value = ($value == null ? $this->value : $value);
		return array_search($this->value, self::$codeList);
	}
	
	
}

?>