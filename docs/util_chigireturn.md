ChigiReturn:Return Values Equalization
====================================

## The ChigiReturn Class

ChigiReturn is a utility for unififormity of return values. Originally, it is designed to the Return Values Specification of the Chigix own. However, it could be adapt to other common type of datas now as a equalizer.

Physically, this machanism is providered by the ChigiReturn Class.

## Build A General Return Object

* `new ChigiReturn();`

	Build an empty return object. Developers could load data into it via `$obj->get()` method;

* `new ChigiReturn(201);`

	Build an ChigiReturn Object just with a ChigiCode. Usually, developers use the only ChigiCode Return object as an improved boolean.

* `new ChigiReturn(RETA);`

	The formal usage to instantiate the Return Object with a RETA Array. Since 1.8.0, an array could be recognized as a RETA by the ChigiCode in the 'status' key only, rather than keep the completed format of the development specification. So just feel free to return values with only the ChigiCode necessary.

* `new ChigiReturn(ChigiReturn $object);`

	It would be convenient to new this instance without confirm in advance whether the data is also an instance from ChigiReturn. Since 1.8.0, the constructor of the ChigiReturn Class would clone the ChigiReturn object existed automatically. 

	In conclusion, just instantiate it as a abstract data no mater its type or class belongs to.

* `new ChigiReturn(OTHERS);`

	All other datas beyond the identification of the ChigiReturn Class would be setted as the Data totally. And developers could only get it using `$obj->__` .

## Datas Getter

The ChigiReturn Object is allowed to get data via some magic proterty:

* `$returnObj->__`

	Get the whole data in the $returnObj. Developers could get the original data by this.

* `$returnObj->key`

	If the original data in the returnObj is an array, this expression will return the value of the 'key'.		
	It is equal to the expression: `$returnObj->__['key']` .		
	**NOTE**:If the original data is not an array or an array without the target 'key', it will return null equally.

## Datas Formatter

The ChigiReturn Object could generate the two format of JSON and RETA upon the data itself.

* Usage:

		// ↓ Return the JSON for the whole abstract object
		$returnObj->toJsonAll();

		// ↓ Return the JSON only for the data part of the target object
		$returnObj->toJsonData();

		// ↓ Return the RETA format of the abstract object
		$returnObj->toReta();

## Datas Visualization

The ChigiReturn Object support Datas Visualization since 1.8.0.

It's very easy to use through the method `ChigiReturn::View()` . Now, it only support 1-dimension(linear) data and 2-dimension(table) data. The view method could detect the data format smartly so as to decide which view to be generated automatically.

* Description:

		$obj->view('StrapTable', 'indexUserInfo', [false], ['Index']);

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$type                   |Give a formatting script for renderer, which file was in CHIGI_PATH/DataExt/
	$name                   |Give the name of the target view to be generated, which would be the file name for the new template.
	$isLock                 |Optional, if false, the template content would be refreshed everytime; if true it would not.
	$pageName               |Optional, the target action name to put in the new template, it was the current action by default.

* Example:

		// ↓In the Action
		$userInfo = new ChigiReturn($serviceSugar->currentUser());
		$this->assign('ProfileView_indexUserInfo', array(
            'indexUserInfo'=>$userInfo->view('StrapTable', 'indexUserInfo', false)
        ));

        // In the including template file such as the main template
        <include file="ProfileView:indexUserInfo"/>
        // Then the data would be included into the page as a view module.