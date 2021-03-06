<?php

/**
 * @copyright   Copyright (C) 2019 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 *
 * Localized data
 */

Dict::Add('EN US', 'English', 'English', array(

	//	'Class:SomeClass' => 'Class name',
	//	'Class:SomeClass+' => 'More info on class name',
	//	'Class:SomeClass/Attribute:some_attribute' => 'your translation for the label',
    //	'Class:SomeClass/Attribute:some_attribute/Value:some_value' => 'your translation for a value',
    //	'Class:SomeClass/Attribute:some_attribute/Value:some_value+' => 'your translation for more info on the value',
	
	'Class:Person/Attribute:contactmethods_list' => 'Contact methods',
	'Class:Person/Attribute:contactmethods_list+' => 'Overview of contact methods related to this person.',
	
	'Class:ContactMethod/Attribute:person_id' => 'Person',
	'Class:ContactMethod/Attribute:contact_method' => 'Contact method',
	'Class:ContactMethod/Attribute:contact_method/Value:phone' => 'Phone',
	'Class:ContactMethod/Attribute:contact_method/Value:mobile_phone' => 'Mobile phone',
	'Class:ContactMethod/Attribute:contact_method/Value:email' => 'Email',
	'Class:ContactMethod/Attribute:contact_detail' => 'Contact detail',
	
	'Errors/ContactMethod/InvalidPhoneNumber' => 'Invalid phone number.',
	'Errors/ContactMethod/InvalidMobilePhoneNumber' => 'Invalid mobile phone number.',
	'Errors/ContactMethod/InvalidEmail' => 'Invalid email address.',
	
));
