<?php

/**
 * @copyright   Copyright (c) 2019-2024 Jeffrey Bostoen
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
	
	'Class:ContactMethod' => 'Contact method',
	'Class:ContactMethod/Attribute:person_id' => 'Person',
	'Class:ContactMethod/Attribute:contact_method' => 'Contact method',
	'Class:ContactMethod/Attribute:contact_method+' => 'Known contact method to get in touch with this person.',
	'Class:ContactMethod/Attribute:contact_method/Value:phone' => 'Phone',
	'Class:ContactMethod/Attribute:contact_method/Value:mobile_phone' => 'Mobile phone',
	'Class:ContactMethod/Attribute:contact_method/Value:email' => 'Email',
	'Class:ContactMethod/Attribute:contact_method/Value:github' => 'GitHub',
	'Class:ContactMethod/Attribute:contact_method/Value:linkedin' => 'LinkedIn',
	'Class:ContactMethod/Attribute:contact_detail' => 'Contact detail',
	'Class:ContactMethod/Attribute:contact_detail+' => 'Contact detail should be the user id (UID) for this user, although there is no validation check.',
	
));
