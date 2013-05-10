The ChigiService Class
===============================

## ChigiService::$apiAction;

* 描述：指向当前Service服务类所对应的api接口对象
* 使用：

		//安装配置
		public $apiAction = 'Sugar.Action.SugarApi';  //以SugarService为例

		//使用示例
		$this->apiAction->onlineip = getClientIp(); //属性访问
		$this->apiAction->requestCurrentUser($property); //操作访问调用

## ChigiService::addAddrParams;

* Description：

		$this->addAddrParams("Key","Value");

	Add a pair of key-value as a Query Param to the current redirect link in this service.

* Parameters：

	Param                   |Desc
	------------------------|-----------------------------
	$key                    |The param's variable name
	$value                  |The param's value

* Return Values:

	`$this` Handle.

* Example:

		//↓You can use it in chain-writing style
		$this->addAddrParams('iframe','http://www.chigix.com')->addAddrParams('var','Test');

## ChigiService::successDirectHeader;

* Description:

		$this->successDirectHeader();

	Make a redirection to the address marked as the success target in this service.

* Parameters: NONE
* Return Values: NONE

## ChigiService::errorDirectHeader;

* Description:

		$this->errorDirectHeader();

	Make a redirection to the address marked as the error target in this service.

* Parameters: NONE
* Return Values: NONE

## ChigiService::setDirect( string $successAdd = null, string $errorAdd = null);

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

## ChigiService::setErr($addr = null)

* Description:

		$this->setErr(string $addr);
	
	Set the ChigiService::errorDirectHeader by giving the `$addr`.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$addr                   |Set it as the error redirecting address

* Return Values:

	`$this` handle.

## ChigiService::setSuc($addr = null)

* Description:

		$this->setSuc(string $addr);
	
	Set the ChigiService::successDirectHeader by giving the `$addr`.

* Parameters:

	Param                   |Desc
	------------------------|-----------------------------
	$addr                   |Set it as the successful redirecting address

* Return Values:

	`$this` handle.


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
[CONTENTS](./index.md#contents)
