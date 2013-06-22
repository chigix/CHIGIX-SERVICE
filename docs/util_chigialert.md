The ChigiAlert Class
=============================

## ChigiAlert

The ChigiAlert class could help you send a global alert between pages and servers 

## Usage in Server-side

### Build an alert object

	$alert = new ChigiAlert();
	$alert = new ChigiAlert($ChigiReturnObj);
	$alert = new ChigiAlert(array('status')=>200));
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

### Suggestion Alert Type

* alert-error
* alert-success
* alert-info
* alert-block

## Usage on Front-end

Because of the suggestion above is BootStrap Supporting, so it would be easily to throw an alert via the bootstrap.

Here is the example upon the bootstrap:

	<if condition="ching('chijiAlertOn',null)">
		<div class="alert {:ching('chijiAlert.option',null)}">
			<button type="button" class="close" data-dismiss="alert">&times;</button>
			<strong>{:ching('chijiAlert.message',null)}</strong>
			</div>
	</if>

And then, you can make this template file included into the main layout html.