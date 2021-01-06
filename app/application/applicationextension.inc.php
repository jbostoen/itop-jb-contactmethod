<?php

/**
 * @copyright   Copyright (C) 2019 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2020-12-02 13:56:48
 *
 * PHP Main file
 */

namespace jb_itop_extensions\contact_method;

// iTop internals
use \CMDBChange;
use \DBObject;
use \DBObjectSearch;
use \DBOBjectSet;
use \Dict;
use \iApplicationObjectExtension;
use \MetaModel;

// iTop classes
use \ContactMethod;
use \Person;


class ApplicationObjectExtension_ContactMethod implements iApplicationObjectExtension {
	 	  
	/**
	 * Invoked to determine whether an object can be written to the database 
	 *	
	 * The GUI calls this verb and reports any issue.
	 * Anyhow, this API can be called in other contexts such as the CSV import tool.
	 * 
	 * @param DBObject $oObject The target object
	 * @return string[] A list of errors message. An error message is made of one line and it can be displayed to the end-user.
	 */	
	public function OnCheckToWrite($oObject) {
		
		// No errors		
		return [];
				
	}	
	
	/**
	 * Invoked to determine whether an object has been modified in memory
	 *
	 *	The GUI calls this verb to determine the message that will be displayed to the end-user.
	 *	Anyhow, this API can be called in other contexts such as the CSV import tool.
	 *	
	 * If the extension returns false, then the framework will perform the usual evaluation.
	 * Otherwise, the answer is definitively "yes, the object has changed".	 	 	 
	 *	 
	 * @param DBObject $oObject The target object
	 * @return boolean True if something has changed for the target object
	 */	
	public function OnIsModified($oObject) {
		return false;
	}
	
	/**
	 * Invoked to determine whether an object can be deleted from the database
	 *	
	 * The GUI calls this verb and stops the deletion process if any issue is reported.
	 * 	 
	 * Please note that it is not possible to cascade deletion by this mean: only stopper issues can be handled. 	 
	 * 
	 * @param DBObject $oObject The target object
	 * @return string[] A list of errors message. An error message is made of one line and it can be displayed to the end-user.
	 */	
	public function OnCheckToDelete($oObject) {
		return [];
		
	}

	/**
	 * Invoked when an object is updated into the database
	 *	
	 * The method is called right <b>after</b> the object has been written to the database.
	 * 
	 * @param DBObject $oObject The target object
	 * @param CMDBChange|null $oChange A change context. Since 2.0 it is fine to ignore it, as the framework does maintain this information once for all the changes made within the current page
	 * @return void
	 */	
	public function OnDBUpdate($oObject, $oChange = null) {
		$this->OnContactMethodChanged($oObject);
		return;
	}

	/**
	 * Invoked when an object is created into the database
	 *	
	 * The method is called right <b>after</b> the object has been written to the database.
	 * 
	 * @param DBObject $oObject The target object
	 * @param CMDBChange|null $oChange A change context. Since 2.0 it is fine to ignore it, as the framework does maintain this information once for all the changes made within the current page
	 * @return void
	 */	
	public function OnDBInsert($oObject, $oChange = null) {
		$this->OnContactMethodChanged($oObject);
		return;
	}

	/**
	 * Invoked when an object is deleted from the database
	 *	
	 * The method is called right <b>before</b> the object will be deleted from the database.
	 * 
	 * @param DBObject $oObject The target object
	 * @param CMDBChange|null $oChange A change context. Since 2.0 it is fine to ignore it, as the framework does maintain this information once for all the changes made within the current page
	 * @return void
	 */	
	public function OnDBDelete($oObject, $oChange = null) {
		$this->OnContactMethodDeleted($oObject);
		return;
	}
	
	
	/**
	 * 
	 * Updates related Person object each time after a ContactMethod is updated and the other way around.
	 * Triggered on both insert and update.
	 *
	 */
	public function OnContactMethodChanged($oObject) {
		
		// If a ContactMethod changed, validate and port back to Person object
		if($oObject instanceof ContactMethod) {
			
			$oContactMethod = $oObject;
			$sContactMethod = $oContactMethod->Get('contact_method');
			$sContactDetail = $oContactMethod->Get('contact_detail');
						
			// Might have been changed above (from phone to mobile_phone , from mobile_phone to phone )
			// This should be updated properly in Person object.
			
			// Write back to Person
			if(in_array($sContactMethod, ['phone', 'mobile_phone', 'email']) == true) {
				
				// Write back to Person. Latest change should be primary.						
				$sOQL = 'SELECT Person WHERE id = :person_id';
				$oSet_Person = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), [], [
					'person_id' => $oContactMethod->Get('person_id')
				]);
							
				// Only 1 person will be retrieved (assuming person_id was valid)
				$oPerson = $oSet_Person->Fetch();
				
				// Prevent loop: only if the Person property is not equal to this new detail: update().
				if($oPerson !== null && $oPerson->Get($sContactMethod) != $sContactDetail) {
					$oPerson->Set($sContactMethod, $sContactDetail);
					$oPerson->DBUpdate();					
				}
				
			}
			
			
		}
		
		// If contact info on the Person object changed, update ContactMethods if necessary
		elseif($oObject instanceof Person) {
			
			// Check if a ContactMethod exists for email, phone, mobile_phone. 
			// If not, create.
			$oPerson = $oObject;
			$aPreviousValues = $oObject->ListPreviousValuesForUpdatedAttributes();
			$aUpdatedAttCodes = array_keys($aPreviousValues);
			
			$aContactMethods = ['email', 'phone', 'mobile_phone'];
			
			foreach($aContactMethods as $sContactMethod) {
				
				// Is updated? If not, just try next method
				if(in_array($sContactMethod, $aUpdatedAttCodes) == false) {
					continue;
				}
				
				$sContactDetail = $oPerson->Get($sContactMethod);
			
				// Should a new ContactMethod be created?
				if($sContactMethod == 'phone' && $sContactDetail == '+00 000 000 000') {
					// Do nothing
				}
				elseif($sContactDetail != '') {
						
					// Select ContactMethod
					// Use LIKE without wildcards to enforce case insensitivity (email)
					$sOQL = 'SELECT ContactMethod WHERE person_id = :person_id AND contact_method LIKE :contact_method AND contact_detail LIKE :contact_detail';
					$oSet_ContactMethods = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), [], [
						'person_id' => $oPerson->GetKey(),
						'contact_method' => $sContactMethod,
						'contact_detail' => $sContactDetail
					]);
					
					// There shouldn't be a ContactMethod with the same details if a new one is added
					if($oSet_ContactMethods->Count() == 0) {
						
						// Create ContactMethod
						$oContactMethod = MetaModel::NewObject('ContactMethod', [
							'person_id' => $oPerson->GetKey(),
							'contact_method' => $sContactMethod,
							'contact_detail' => $sContactDetail
						]);
						$oContactMethod->DBInsert();	
						
					}
				
				}
				
				// Per design, outdated ContactMethods are NOT deleted automatically.
				// After all, it's possible there is a new contact method which doesn't invalidate the old one.
				
			}			
		}		
	}
	
	/**
	 * 
	 * Updates related Person object each time a ContactMethod is removed.
	 * It checks if it's one of the default contact details (phone, mobile phone, email) and sets the info to blank or the last other known info.
	 *  	 
	 */
	public function OnContactMethodDeleted($oObject) {
		
		// If a ContactMethod is deleted, the related Person object should be updated to reflect these changes 
		if($oObject instanceof ContactMethod) {
			
			$oContactMethod = $oObject;
			$sContactMethod = $oContactMethod->Get('contact_method');
			
			switch($sContactMethod) {
				
				case 'phone':
				case 'mobile_phone':
				case 'email':
					
					// Retrieve Person
					$sOQL = 'SELECT Person WHERE id = :person_id';
					$oSet_Person = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), [], [
						'person_id' => $oContactMethod->Get('person_id')
					]);
			
					// Only 1 person should be retrieved
					$oPerson = $oSet_Person->Fetch();

					// Set to empty
					$oPerson->Set($sContactMethod, '');
					
					// But what if a fallback is possible, to update the Person object with another most recent ContactMethod of the same contact_method type?
					// Since this query is executed before ContactMethod is really deleted: 
					// Don't include the current (deleted) ContactMethod object in t his query.
					$sOQL = 'SELECT ContactMethod WHERE person_id = :person_id AND contact_method = :contact_method AND id != :id';			
					
					// Return maximum one. Descending by id.
					$oSet_ContactMethod = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), ['id' => false], [
						'person_id' => $oPerson->GetKey(),
						'contact_method' => $sContactMethod,
						'id' => $oContactMethod->GetKey()
					], [], 1);
						
					// But maybe there's another last known ContactMethod.
					// Simply look at 'id' and take the last one, not date of last change (yet)
					while($oContactMethod = $oSet_ContactMethod->Fetch()){
						$oPerson->Set($sContactMethod, $oContactMethod->Get('contact_detail'));	
					}
					
					// Reset person attribute
					$oPerson->DBUpdate();
					break;
					
				default:
					break;
		
			}
			
		}
		
		// if Person is deleted, iTop should automatically remove all ContactMethods by default
		
		return;
	}
	
}
