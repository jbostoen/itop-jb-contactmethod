<?php

/**
 * @copyright   Copyright (c) 2019-2023 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Localized data
 */

Dict::Add('NL NL', 'Dutch', 'Dutch', array(

	//	'Class:SomeClass' => 'Class name',
	//	'Class:SomeClass+' => 'More info on class name',
	//	'Class:SomeClass/Attribute:some_attribute' => 'your translation for the label',
    //	'Class:SomeClass/Attribute:some_attribute/Value:some_value' => 'your translation for a value',
    //	'Class:SomeClass/Attribute:some_attribute/Value:some_value+' => 'your translation for more info on the value',
		
	'Class:Person/Attribute:contactmethods_list' => 'Contactmethodes',
	'Class:Person/Attribute:contactmethods_list+' => 'Overzicht van contactmethodes voor deze persoon.',
	
	'Class:ContactMethod/Attribute:person_id' => 'Persoon',
	'Class:ContactMethod/Attribute:contact_method' => 'Contactmethode',
	'Class:ContactMethod/Attribute:contact_method/Value:phone' => 'Telefoon',
	'Class:ContactMethod/Attribute:contact_method/Value:mobile_phone' => 'Mobiele telefoon',
	'Class:ContactMethod/Attribute:contact_method/Value:email' => 'E-mailadres',
	'Class:ContactMethod/Attribute:contact_detail' => 'Contactgegeven',
	
	'Errors/ContactMethod/InvalidPhoneNumber' => 'Ongeldig telefoonnummer.',
	'Errors/ContactMethod/InvalidMobilePhoneNumber' => 'Ongeldig mobiel nummer.',
	'Errors/ContactMethod/InvalidEmail' => 'Ongeldig e-mailadres.',
	
));
