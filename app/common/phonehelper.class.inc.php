<?php

/**
 * @copyright   Copyright (C) 2019-2020 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2020-12-02 13:56:48
 * @experimental
 *
 * Defines classes for phone number validation, which offers some methods to validate phone numbers. Warning: specifically for Belgian use.
 *
 */
 
 namespace jb_itop_extensions\contact_method;
	
	/**
	 * Class which is able to validate phone numbers.
	 */
	class PhoneNumberValidator {
		
		/** @var \String $sDigitsOnly Only digits */
		public $sDigitsOnly;
		
		/** @var \String $sOriginalNumber Original phone number */
		public $sOriginalNumber;
		
		
		/**
		 * Constructor for phone number validator. Immediately keeps a copy of only the digits.
		 * 
		 * @param \String $sPhoneNumber Phone number as specified by the user
		 * @return void
		 * 
		 */
		function __construct($sPhoneNumber) {
			$this->sOriginalNumber = $sPhoneNumber;
			$this->sDigitsOnly = preg_replace('/[^\d]/', '', $this->sOriginalNumber);
		}
		
		/**
		 * Gets a number without digits.
		 * 
		 * @param \String Returns only the digits of the specified number.
		 * @return void
		 * 
		 */
		public function GetDigits() {
			return $this->sDigitsOnly;
		}
		
		/**
		 * Checks whether the phone number only contains allowed characters
		 * 
		 * @return \Boolean
		 * 
		 */
		public function ContainsOnlyAllowedCharacters() {
			// Could start with +countrycode
			// Starts with zonal code
			// Might contain spaces
			// Often a slash, but not required
			// Then numbers, sometimes with a space or dot in between
			return preg_match('/^(+|)[0-9 \.\/]{1,}$/', $this->sOriginalNumber);
		}
		
	}


	/**
	 * Class which is able to validate Belgian phone numbers.
	 */
	class BelgianPhoneNumberValidator extends PhoneNumberValidator {
		
		/**
		 * @var \Integer $iCountryCode Country code
		 */
		public $iCountryCode = 32;
		
		/**
		 * @var \Integer $iDigitsMobile Max number of digits for a land line number, excluding country code or starting 0
		 */
		public $iDigitsLandLine = 8;
		
		/**
		 * @var \Integer $iDigitsMobile Max number of digits for a mobile number, excluding country code or starting 0
		 */
		public $iDigitsMobile = 9;
		
		/**
		 * Constructor for phone number validator. Immediately keeps a copy of only the digits.
		 * 
		 * @param \String $sPhoneNumber Phone number as specified by the user
		 * @return void
		 * 
		 */
		function __construct($sPhoneNumber) {

			parent::__construct($sPhoneNumber);
			$this->sDigitsOnlyWithoutCountryPrefix = preg_replace('/^(0|'.$this->iCountryCode.')/', '', $this->sDigitsOnly);
			
		}
		
		/**
		 * Returns only the local digits. No +32 or 0
		 * 
		 * @return \String Local digits
		 * 
		 */
		public function GetLocalDigits() {
		
			// Adapted to Belgian situation
			return $this->sDigitsOnlyWithoutCountryPrefix;
			
		}
		
		/**
		 * Returns whether this is has a valid country code (or 0)
		 *
		 * @return \Boolean
		 */
		public function HasValidBelgianCountryPrefix() {
			return (Bool)preg_match('/^(0|32)/', $this->GetDigits());
		}
		
		/**
		 * Returns whether this has a mobile prefix for a Belgian mobile phone number
		 *
		 * @param \String $sPhone Phone number
		 *
		 * @return \Boolean
		 */
		public function HasValidBelgianMobilePrefix() {
			
			// https://www.bipt.be/en/consumers/telephone/numbering/numbering-principles
			// 046, 047, 048, 049
			// 04 = land line too, Liège and Voeren. Less digits!
			// Hence a check for the first 2 digits and the total number of digits.			
			
			switch(true) {
				case preg_match('/^(46|47|48|49)/', $this->sDigitsOnlyWithoutCountryPrefix) && $this->HasValidNumberOfDigitsMobileNumber():
					return true;
					break;
					
				default:
					break;
					
			}
		
			return false;

		}
		
		/**
		 * Returns whether this is a valid Belgian phone number.
		 * 
		 * @param \Boolean $bStrict Optional setting to also enforce use of only valid characters. Defaults to false.
		 * 
		 * @return \Boolean
		 * 
		 */
		public function IsValidBelgianNumber($bStrict = false) {
			
			if($bStrict == true && $this->ContainsOnlyAllowedCharacters() == false) {
				return false;
			}
			
			return ($this->IsValidBelgianLandLineNumber() == true || $this->IsValidBelgianMobileNumber() == true);
		}
		
		/**
		 * Returns whether this is a valid Belgian phone number.
		 * 
		 * @return \Boolean
		 * 
		 */
		public function IsValidBelgianLandLineNumber() {
			// 1) must have a Belgian country code or 0
			// 2) must have a length of 8 non-country digits
			return ($this->HasValidBelgianCountryPrefix() == true && $this->HasValidNumberOfDigitsLandLineNumber() == true);
		}
		
		/**
		 * Returns whether this is a valid Belgian phone number.
		 * 
		 * @return \Boolean
		 * 
		 */
		public function IsValidBelgianMobileNumber() {
			// 1) must have a Belgian country code or 0
			// 2) must have a valid Belgian mobile prefix
			// 3) must have a length of 9 non-country digits
			return ($this->HasValidBelgianCountryPrefix() == true && $this->HasValidBelgianMobilePrefix() == true && $this->HasValidNumberOfDigitsMobileNumber() == true);
		}
		
		/**
		 * Returns whether the number of digits for a land line number is correct
		 * 
		 * @return \Boolean
		 * 
		 */
		public function HasValidNumberOfDigitsLandLineNumber() {
			return (strlen($this->sDigitsOnlyWithoutCountryPrefix) == $this->iDigitsLandLine);
		}
		
		/**
		 * Returns whether the number of digits for a mobile number is correct
		 * 
		 * @return \Boolean
		 * 
		 */
		public function HasValidNumberOfDigitsMobileNumber() {
			return (strlen($this->sDigitsOnlyWithoutCountryPrefix) == $this->iDigitsMobile);
		}
		
	}