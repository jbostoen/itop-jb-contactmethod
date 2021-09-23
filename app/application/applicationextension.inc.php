<?php

/**
 * @copyright   Copyright (C) 2019 Jeffrey Bostoen
 * @license     https://www.gnu.org/licenses/gpl-3.0.en.html
 * @version     2.6.210803
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
		
		// Return errors		
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
		$this->AfterContactMethodChanged($oObject);
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
		$this->AfterContactMethodChanged($oObject);
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
		$this->AfterContactMethodDeleted($oObject);
		return;
	}
	
	
	/**
	 * 
	 * Updates related Person object each time after a ContactMethod is updated and the other way around.
	 * Triggered on both insert and update.
	 *
	 */
	public function AfterContactMethodChanged($oObject) {
		
		// If contact info on the Person object changed, update ContactMethods if necessary
		// Do NOT port info back from ContactMethod to Person. Reason: if a ContactMethod is created for outdated information, it will update person with old info.
		// It's also not recommended to list changes here, since some attribute values might simply be new or altered in different parts.
		if($oObject instanceof Person) {
			
			// Check if a ContactMethod exists for changed attributes: email, phone, mobile_phone. 
			// If not, create.
			/** @var \Person $oPerson iTop Person */
			$oPerson = $oObject;
	
			// Obtain current ContactMethods
			$sOQL = 'SELECT ContactMethod WHERE person_id = :person_id';
			$oSet_CurrentContactMethods = new DBObjectSet(DBObjectSearch::FromOQL($sOQL), [], [
				'person_id' => $oPerson->GetKey()
			]);
			
			foreach(['email', 'phone', 'mobile_phone'] as $sContactMethod) {
				
				$bIsNew = true;
			
				// Init ContactMethod (don't save yet)
				$oNewContactMethod = MetaModel::NewObject('ContactMethod', [
					'person_id' => $oPerson->GetKey(),
					'contact_method' => $sContactMethod,
					'contact_detail' => $sCurrentContactDetail
				]);
				
				// Call this method already to make sure for instance phone numbers are formatted the same way
				// The formatting could be modified by additional extensions such as jb-contactmethod-validation
				self::BeforeSaveObject($oNewContactMethod);
				
				// Current contact detail	
				$sCurrentContactDetail = $oNewContactMethod->Get('contact_detail');
				
				// Should a new ContactMethod be created?
				if($sContactMethod == 'phone' && $sCurrentContactDetail == '+00 000 000 000') {
					// Do nothing
				}
				elseif($sCurrentContactDetail != '') {
					
					// Rewind since this is a loop
					$oSet_CurrentContactMethods->Rewind();
					
					while($oExistingContactMethod = $oSet_CurrentContactMethods->Fetch()) {
						
						if($oExistingContactMethod->Get('contact_method') == $sContactMethod) {
							switch($sContactMethod) {
								case 'phone':
								case 'mobile_phone':
									// Exact (strip characters to avoid all sorts of re-writing by additional extensions)
									if($oExistingContactMethod->Get('contact_detail') == $sCurrentContactDetail) {
										$bIsNew = false;
										break 2;
									}
									break;
								case 'email':
									// Same value, case insensitive
									if(strtolower($oExistingContactMethod->Get('contact_detail')) == strtolower($sCurrentContactDetail)) {
										$bIsNew = false;
										break 2;
									}
									break;
								default:
									break;
							}
						}
						
					}
					
					if($bIsNew == true) {
						
						$oNewContactMethod->DBInsert();	
						
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
	 * It checks if it's one of the default contact details (phone, mobile phone, email) and sets the info to blank or the last known info.
	 *  	 
	 */
	public function AfterContactMethodDeleted($oObject) {
		
		// If a ContactMethod is deleted, the related Person object should be updated to reflect these changes 
		if($oObject instanceof ContactMethod) {
			
			$oContactMethod = $oObject;
			$sContactMethod = $oContactMethod->Get('contact_method');
			$sContactDetail = $oContactMethod->Get('contact_detail');
			
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

					// Set Person's attribute value to empty if the value was the same as the one for the ContactMethod that has been deleted
					if($oPerson->Get($sContactMethod) == $sContactDetail) {
						
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
						while($oPreviousContactMethod = $oSet_ContactMethod->Fetch()){
							$oPerson->Set($sContactMethod, $oPreviousContactMethod->Get('contact_detail'));	
						}
						
						// Reset person attribute
						$oPerson->DBUpdate();
						
					}
					break;
					
				default:
					break;
		
			}
			
		}
		
		// if Person is deleted, iTop should automatically remove all ContactMethods by default
		
		return;
	}
	
	/**
	 * Hook to allow actions to occur before saving the object. 
	 * Use cases: validation, ...
	 * 
	 * @param \Person|\ContactMethod $oObject iTop object
	 * 
	 * @return void
	 */
	public static function BeforeSaveObject($oObject) {
		
		// Get list of ContactMethod extensions
		$aExtensions = [];
		foreach(get_declared_classes() as $sClassName) {
			$aImplementations = class_implements($sClassName);
			if(in_array('jb_itop_extensions\contact_method\iContactMethodExtension', $aImplementations) == true || in_array('iContactMethodExtension', $aImplementations) == true) {
				$aExtensions[] = $sClassName;
			}
		}
		
		// Sort by rank
		usort($aExtensions, function($a, $b) {
			return $a::$fRank <=> $b::$fRank;
		});
		
		// Run hook
		foreach($aExtensions as $sClassName) {
			$sClassName::BeforeSaveObject($oObject);
		}	
		
	}
	
}

/**
 * Interface iContactMethodExtension. Everything implementing this interface will run before contact info is processed. Ideal for validation mechanisms.
 */
interface iContactMethodExtension {
	
	/**
	 * Hook which allows to run a validation or perform operations before processing contact info
	 * 
	 * @param \Person|\ContactMethod $oObject iTop object
	 * @return void
	 */
	public function BeforeSaveObject($oObject);
	
}
