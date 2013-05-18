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

* $returnObj->__

	Get the whole data in the $returnObj. Developers could get the original data by this.

* $returnObj->key

	If the original data in the returnObj is an array, this expression will return the value of the 'key'.		
	It is equal to the expression: `$returnObj->__['key']` .		
	**NOTE**:If the original data is not an array or an array without the target 'key', it will return null equally.
