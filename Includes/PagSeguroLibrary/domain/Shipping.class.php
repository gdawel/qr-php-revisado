<?php
/**
* Shipping information
*/
class Shipping {

	/**  
	 * Shipping address
	 * @see Address
	 */
    private $address;
    
    /**
    * Shipping type. See the ShippingType helper class for a list of known shipping types.
    * @see ShippingType::
    */
    private $type;
    
    /**
    * shipping cost.
    */
    private $cost;
    
	/**
	 * Initializes a new instance of the Shipping class 
	 * @param array $data
	 */
	public function __constuct(Array $data = null) {
		if ($data) {
			if (isset($data['address']) && $data['address'] instanceof Address) {
				$this->address = $data['address'];
			}
			if (isset($data['type']) && $data['type'] instanceof ShippingType) {
				$this->type = $data['type'];
			}
			if (isset($data['cost'])) {
				$this->cost = $data['cost'];
			}
		}
	}
	
	/**
	 * Sets the shipping address
	 * @see Address
	 * @param Address $address
	 */
    public function setAddress(Address $address) {
        $this->address = $address;
    }
	
    /**
     * @return the shipping Address
     * @see Address
     */
    public function getAddress() {
        return $this->address;
    }

    /**
     * Sets the shipping type
     * @param ShippingType $type
     * @see ShippingType::
     */
    public function setType(ShippingType $type) {
        $this->type = $type;
    }

    /**
     * @return the shipping type
     * @see ShippingType::
     */
    public function getType() {
        return $this->type;
    }
	
    public function setCost($cost) {
        $this->cost = $cost;
    }

    /**
     * @return the shipping cost
     */
    public function getCost() {
        return $this->cost;
    }
	
}	

?>