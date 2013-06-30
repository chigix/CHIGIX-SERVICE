The ChigiRole Util for Access Control System
============================

# The Util of the Role based Access Control

## Introduction

## Intended Audience

## Roles from a Developer's View

### The ChigiRole Object

## Access Control List

### Get the ACL

	$this->getACL('PAGE');
	$this->getACL('VIEW');
	$this->getACL('FILTER');
	$this->getACL('ALL');

	$this->getAccessStatus('PAGE', 'Login');

### Check Page Access

	$this->getPageAccessStatus('Login');
	$this->checkPageAccess('Login','Register/index', 'Error Alert contents');

### Check View Access

	$this->getViewAccessStatus('Nav','Login');
	$this->getViewAccessStatus('Nav','UnLogin');

	// Get the access control list upon the view level.
	$this->getViewAccessList();

### Check Data Access



## The Role's Network Manager

## Guidlines For Role Development in the Service Layer