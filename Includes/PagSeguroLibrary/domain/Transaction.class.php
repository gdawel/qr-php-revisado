<?php
/**
* Represents a PagSeguro transaction
*/
class Transaction {

	/**
	 * Transaction date
	 */
    private $date;

    /**
     * Last event date
     * Date the last notification about this transaction was sent
     */
    private $lastEventDate;

    /**
     * Transaction code
     */
    private $code;

    /**
     *  Reference code
     *  You can use the reference code to store an identifier so you can
     *  associate the PagSeguro transaction to a transaction in your system.
     */
    private $reference;

	/**
	 * Transaction type
	 * @see TransactionType::
	 */
    private $type;

    /**
     * Transaction Status
     * @see TransactionStatus::
     */
    private $status;

    /**
     * Payment method
     * @see PaymentMethod
     */
    private $paymentMethod;

    /**
     * Groos amount of the transaction
     */
    private $grossAmount;

    /**
     * Discount amount
     */
    private $discountAmount;

    /**
     * Fee amount
     */
    private $feeAmount;

    /**
     * Net amount
     */
    private $netAmount;

    /**
     * Extra amount
     */
    private $extraAmount;

    /**
     * Installment count
     */
    private $installmentCount;

    /**
     * item/product list in this transaction
     * @see Item
     */
    private $items;

    /**
     * Payer information, who is sending money
     * @see Sender
     */
    private $sender;

    /**
     * Shipping information
     * See Shipping
     */
    private $shipping;

    /**
     * Date the last notification about this transaction was sent
     * @return the last event date
     */
    public function getLastEventDate() {
        return $this->lastEventDate;
    }

    /**
     * Sets the last event date
     *
     * @param lastEventDate
     */
    public function setLastEventDate($lastEventDate) {
        $this->lastEventDate = $lastEventDate;
    }

    /**
     * @return the transaction date
     */
    public function getDate() {
        return $this->date;
    }

    /**
     * Sets the transaction date
     *
     * @param date
     */
    public function setDate($date) {
        $this->date = $date;
    }

    /**
     * @return the transaction code
     */
    public function getCode() {
        return $this->code;
    }

    /**
     * Sets the transaction code
     *
     * @param code
     */
    public function setCode($code) {
        $this->code = $code;
    }

    /**
     * You can use the reference code to store an identifier so you can
     *  associate the PagSeguro transaction to a transaction in your system.
     *
     * @return the reference code
     */
    public function getReference() {
        return $this->reference;
    }

    /**
     * Sets the reference code
     *
     * @param reference
     */
    public function setReference($reference) {
        $this->reference = $reference;
    }

    /**
     * @return the transaction type
     * @see TransactionType
     */
     public function getType() {
        return $this->type;
    }

    /**
     * Sets the transaction type
     * @param TransactionType $type
     */
    public function setType(TransactionType $type) {
        $this->type = $type;
    }

    /**
     * @return the transaction status
     * @see TransactionStatus::
     */
    public function getStatus() {
        return $this->status;
    }

    /**
     * Sets the transaction status
     * @param TransactionStatus $status
     */
    public function setStatus(TransactionStatus $status) {
        $this->status = $status;
    }

    /**
     * @return the payment method used in this transaction
     */
    public function getPaymentMethod() {
        return $this->paymentMethod;
    }

    /**
     * Sets the payment method used in this transaction
     * @param paymentMethod $paymentMethod
     */
    public function setPaymentMethod(paymentMethod $paymentMethod) {
        $this->paymentMethod = $paymentMethod;
    }

    /**
     * @return the transaction gross amount
     */
    public function getGrossAmount() {
        return $this->grossAmount;
    }

    /**
     * Sets the transaction gross amount
     * @param float $totalValue
     */
    public function setGrossAmount($totalValue) {
        $this->grossAmount = $totalValue;
    }

    /**
     * @return the transaction gross amount
     */
    public function getDiscountAmount() {
        return $this->discountAmount;
    }

    /**
     * Sets the transaction gross amount
     * @param float $discountAmount
     */
    public function setDiscountAmount($discountAmount) {
        $this->discountAmount = $discountAmount;
    }

    /**
     * @return the fee amount
     */
    public function getFeeAmount() {
        return $this->feeAmount;
    }

    /**
     * Sets the transaction fee amount
     * @param float $feeAmount
     */
    public function setFeeAmount($feeAmount) {
        $this->feeAmount = $feeAmount;
    }

    /**
     * @return the net amount
     */
    public function getNetAmount() {
        return $this->netAmount;
    }

    /**
     * Sets the net amount
     * @param float $netAmount
     */
    public function setNetAmount($netAmount) {
        $this->netAmount = $netAmount;
    }

    /**
     * @return the extra amount
     */
    public function getExtraAmount() {
        return $this->extraAmount;
    }

    /**
     * Sets the extra amount
     * @param float $extraAmount
     */
    public function setExtraAmount($extraAmount) {
        $this->extraAmount = $extraAmount;
    }

    /**
     * @return the installment count
     */
    public function getInstallmentCount() {
        return $this->installmentCount;
    }

    /**
     * Sets the installment count
     * @param integer $installmentCount
     */
    public function setInstallmentCount($installmentCount) {
        $this->installmentCount = $installmentCount;
    }

    /**
     * @return the items/products list in this transaction
     * @see Item
     */
    public function getItems() {
        return $this->items;
    }

    /**
     * Sets the list of items/products in this transaction
     * @param array $items
     * @see Item
     */
    public function setItems(Array $items) {
        $this->items = $items;
    }

    /**
     * @return the items/products count in this transaction
     */
    public function getItemCount() {
        return $this->items == null ? null : count($this->items);
    }    

    /**
     * @return the sender information, who is sending money in this transaction
     * @see Sender
     */
    public function getSender() {
        return $this->sender;
    }

    /**
     * Sets the sender information, who is sending money in this transaction
     * @param Sender $sender
     */
    public function setSender(Sender $sender) {
        $this->sender = $sender;
    }

    /**
     * @return the shipping information
     * @see Shipping
     */
    public function getShipping() {
        return $this->shipping;
    }

    /**
     * sets the shipping information for this transaction
     * @param Shipping $shipping
     */
    public function setShipping(Shipping $shipping) {
        $this->shipping = $shipping;
    }
	
    /**
    * @return a string that represents the current object
    */    
    public function toString(){
    	
    	$code  		= $this->code;
    	$email 		= $this->sender ? $this->sender->getEmail() : "null";
    	$date  		= $this->date;
    	$reference  = $this->reference;
    	$status  	= $this->status ? $this->status->getValue() : "null";
    	$itemsCount = is_array($this->items) ? count($this->items) : "null";
    	
    	return	"Transaction("
			."Code=$code"
			.", SenderEmail=$email"
			.", Date=$date"
			.", Reference=$reference"
			.", status=$status"
			.", itemsCount=$itemsCount"
		.")";
    }    
    
}


?>