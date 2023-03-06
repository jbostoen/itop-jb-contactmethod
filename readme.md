# jb-contactmethod

Copyright (c) 2019-2023 Jeffrey Bostoen

[![License](https://img.shields.io/github/license/jbostoen/iTop-custom-extensions)](https://github.com/jbostoen/iTop-custom-extensions/blob/master/license.md)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.me/jbostoen)
🍻 ☕

Need assistance with iTop or one of its extensions?  
Need custom development?  
Please get in touch to discuss the terms: **info@jeffreybostoen.be** / https://jeffreybostoen.be


## What?

⚠️ The current version is broken because listing the changed attributes is incorrect.

Adds a contact methods tab to a Contact. 
Can easily be extended to support all sorts of data. Dropdown to pick method (e.g. phone, email, social media) and a basic text box (255 characters).

Actions:
* If a person's contact info (email, phone, mobile phone) is updated, a contact method is created
* If a contact method is deleted, the info gets removed from the person's details.
  * If possible, it will automatically set the latest known contact information again on the person object for this contact method.
  
* If a contact method is created, it does not get written back to the person's details.
  * This is because of legacy code which still creates a contact method of info that is being removed from the person's details.
  * This is done because removal is sometimes adding an alternative email address, while the previous info is still valid too.



## Cookbook

XML:
* add AttributeLinkedSet to Person
* create new class ContactMethod

PHP:
* make sure ContactMethod follows certain rules. Warning if necessary, strip unnecessary parts where needed
