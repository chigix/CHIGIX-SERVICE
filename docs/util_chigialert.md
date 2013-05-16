The ChigiAlert Class
=============================

## ChigiAlert

The ChigiAlert class could help you send a global alert between pages and servers 

## Usage in Server-side

### Build an alert object

	$alert = new ChigiAlert();
	$alert = new ChigiAlert($ChigiReturnObj);
	$alert = new ChigiAlert(array('status)=>200));
	$alert = new ChigiAlert("此处为ALERT内容");
	$alert = new ChigiAlert("此处为ALERT内容",'alert-info');

### Fast Push Via RETA

You could push the data manually with the RETA Specification, such as a ChigiReturn instance, RETA Array and an integer of ChigiCode.

	$alert->push($retaObj)->alert();

### Common PUSH

If the target data to be pushed without compliance, you could also push completely by your self.

	$alert->pushSet($message, 'alert-info')->alert();

### Alert Submit

The data to be sent set, the `alert()` method appended at the last could submit this current alert.

	$alert->alert();

## Usage on Front-end

