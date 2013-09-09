The ChigiService Class
===============================

# INDEX

- [ChigiService::$apiAction](#chigiserviceapiaction)
- [ChigiService::addAddrParams](#chigiserviceaddaddrparams)
- [ChigiService::bind](#chigiservicebind)
- [ChigiService::errorDirectHeader](#chigiserviceerrordirectheader)
- [ChigiService::request](#chigiservicerequest)
- [ChigiService::setDirect](#chigiservicesetdirect)
- [ChigiService::setErr](#chigiserviceseterr)
- [ChigiService::setSuc](#chigiservicesetsuc)
- [ChigiService::successDirectHeader](#chigiservicesuccessdirectheader)
- [ChigiService::under](#chigiserviceunder)

## Need to be Overrided

### ChigiService::getCurrentRole

* 描述：获取当前服务中的全局注册角色
* 使用：

		// 一般用在控制器中，以获取当前服务所对应的角色的一些信息
		$UserService->getCurrentRole();
		

[INDEX](#index)		
[CONTENTS](../README.md#contents)

### ChigiService::getParents

* Description:
		
		$this->getParents($me_role, $max_level = 5);

	Get the array of the parent roles for the `$me_role` as target.

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	`$me_role`              |The target role to be upon for getting parent roles.
	`$max_level`            |Option. The max value limit for the recursive parent levels. The default value is 5.

* Return Values:

	The array of the parent roles.

* 使用：

		// 一般用在控制器中，以获取当前服务所对应的角色
		$role = $UserService->getCurrentRole();
		$parents = $UserService->getParents($role, 5);

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::$apiAction

* 描述：指向当前Service服务类所对应的api接口对象
* 使用：

		//安装配置
		public $apiAction = 'Sugar.Action.SugarApi';  //以SugarService为例

		//使用示例
		$this->apiAction->onlineip = getClientIp(); //属性访问
		$this->apiAction->requestCurrentUser($property); //操作访问调用

* 说明：

	此属性为可选，但是作为连接对应API层的必需接口声明。

	若声明此属性，则千木服务类在实例化之初自动与API层建立起初始连接状态。

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::addAddrParams

* Description：

		$this->addAddrParams("Key","Value");

	Add a pair of key-value as a Query Param to the current redirect link in this service.

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	$key                    |The param's variable name
	$value                  |The value of the corresponding param

* Return Values:

	`$this` Handle.

* Example:

		//↓You can use it in chain-writing style
		$this->addAddrParams('iframe','http://www.chigix.com')->addAddrParams('var','Test');

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::bind

* Description：

		$this->bind("Key" [,"Value"]);

	Add a pair of key-value as a Query Param to the current redirect link in this service.		
	Or get a value binded to the key associated.

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	$key                    |The param's variable name
	$value                  |Optional, the value of the corresponding param

* Return Values:

	The last value of the key given.

* Example:

		$this->bind('uid',2);
		$this->bind('uid');

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::errorDirectHeader

* Description:

		$this->errorDirectHeader([string $alertMsg]);

	Make a redirection to the address marked as the error target in this service.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$alertMsg               |The string of message would build an alert to push into the ching

* Return Values: NONE
* Example:

		$service->errorDirectHeader("对不起，操作失败");

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::getCurrentRole

* Description:

		$this->getCurrentRole();

	Return the current global ChigiRole Object registerred in this service.

* Return Values: a ChigiRole Object
* Example:

		// We could have a powerful logic supported with the Role Based Access Control List.
		$service->getCurrentRole()->checkPageAccess('Login');

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::request

* Description:

		$this->request($dataArr,'methodName');

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	$dataArr                |The data to be sent off in the request.
	$methodName             |The accessible destiny provided by the API.

* Return Values:

	Going well, it will return a general ChigiReturn object.		
	Otherwise, it will throw exception , for example, without methodName given.		
	Besides, it could be used as a initializer for the API connection when calling without parameters.

* Example:

		//Return the meta data as the result of the request for 'currentUser'
		return $this->request(null, 'currentUser')->__;

* NOTE:

	For the further developer on the services, the method with the same name prepending 'request' defined in the API is right for this request matching.

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::setDirect

* Description:

		$this->setDirect( string $successAdd = null, string $errorAdd = null);

	Give a pair of address marked as success and error to current instance of this service.		
	You can also write it in chain-writing style.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$successAdd             |Set it as the successful redirecting address
	$errorAdd               |Set it as the error redirecting address

* Return Values: 

	`$this` handle.

* Example:

		//The formatting of the address is just the couple of `Module_name/Action_name`.
		$this->setDirect('Index/index','Login/index');

* NOTE:

	This is initialized in the beginning of the instance using the Configure Pair,`C("CHIGI_ERRORDIRECT")` and `C("CHIGI_SUCCESSDIRECT")` .

	If there is another address pair in the Current accessable CHING-SESSION as `ching("CHIGI_ERRORDIRECT")` and `ching("CHIGI_SUCCESSDIRECT")` , the service will also automatic fetch it to the `ChigiService::successDirectHeader` and `ChigiService::errorDirectHeader` above.

	In short, the timing for above methioned intialize is :

		Parameter → Ching → Config-setting

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::setErr

* Description:

		$this->setErr(string $addr);
	
	Set the ChigiService::errorDirectHeader by giving the `$addr`.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$addr                   |Set it as the error redirecting address

* Return Values:

	`$this` handle.

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::setErrAlert

* Description:

		$this->setErrAlert(string $alertMsg);

	Set an alert message being pushed to the alert ching automatically when redirect.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$alertMsg               |The string of message would build an alert to push into the ching

* Return Values:

	`$this` handle.

* **NOTE**:This method was accessible in protected, so it could be invoked only in the service layer.

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::setSuc

* Description:

		$this->setSuc(string $addr);
	
	Set the ChigiService::successDirectHeader by giving the `$addr`.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$addr                   |Set it as the successful redirecting address

* Return Values:

	`$this` handle.

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::setSucAlert

* Description:

		$this->setSucAlert(string $alertMsg);

	Set an alert message being pushed to the alert ching automatically when redirect.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$alertMsg               |The string of message would build an alert to push into the ching

* Return Values:

	`$this` handle.

* **NOTE**:This method was accessible in protected, so it could be invoked only in the service layer.

## ChigiService::successDirectHeader

* Description:

		$this->successDirectHeader([string $alertMsg]);

	Make a redirection to the address marked as the success target in this service.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$alertMsg               |The string of message would build an alert to push into the ching

* Return Values: NONE
* Example:

		$service->errorDirectHeader("登录成功");

[INDEX](#index)		
[CONTENTS](../README.md#contents)

## ChigiService::under

* Description:

		$this->under(string $method);

	The method for the Business Evironment Checking.It will automatic invoke the method named with `"under$method"`, defined in the corresponding service class to achieve the Checking .

	In addition, it is case-sensitive.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$method                 |The undercheck name, with the first character uppercase,  provided in the detailed service document.

* Return Values: UnderCheck
* Example:

		//It is easily to use in chain-writing style.
		$service->under('Login')->setDirect('/login/')->pushAlert("对不起，请先登录")->check();

		//remove a query param in the current address
		$service->rmAddrParam('iframe');

		//add a query param to the current addreess
		$service->addAddrParams('var','testValue');

* NOTE：The first character of the `$method` must be in capital letters.
* REFER: [Under: Method for Environment Check](./developing_specification.md#under-method-for-environment-check)

[INDEX](#index)		
[CONTENTS](../README.md#contents)
