## Payment Cycle
For a payment transaction we have to request a payment via web service. If our request is successful the IPG will return a token which we should use while redirecting customer to payment page. Customer will be redirected back to our desired URL(callback URL) from payment page via a GET request carrying data which may be used to check and verify customer's transaction using web service.
## Request payment
For a payment transaction we should send a payment request to IPG and acquire a token. This may be accomplished by calling `getToken` method.
### Instantiating an IPG object
for instantiating an IPG object we should call `Dizatech\RayanpayIpg\RayanpayIpg` constructor passing it an array of required arguments containing:
* merchantId: your payment gateway merchant id
#### Code sample:
```php
$args = [
    'merchantId'    => '4e1598fc-09b8-29e6-2edc-bf5494616b4d'
]; //Replace arguments with your gateway actual values
$ipg = new RayanpayIpg($args);
```
### `getToken` method
#### Arguments:
* amount: amount in Rials
* redirect_address: URL to which customer may be redirected after payment
#### Returns:
An object with the following properties:
* status: `success` or `error`
* token: in case of a successful request contains the generated token which may be used while redirecting customer to payment page
* message: contains error message when `status` is `error`
## Redirecting customer to payment page
If `status` property of the result of calling `getToken` is `success` we can redirect customer to payment page URL which is currently `https://pms.rayanpay.com/pg/startpay/`. We have to redirect user to payment page via a GET request.

**It is neccessary to save the acquired token token for further use**
#### Code sample:
```php
$args = [
    'merchantId'    => '4e1598fc-09b8-29e6-2edc-bf5494616b4d'
]; //Replace arguments with your gateway actual values
$ipg = new RayanpayIpg($args);
$amount = 1000; //Replace with actual order amount in Rials
$redirect_address = 'http://my.com/verify'; //Replace with your desired callback page URL
$result = $ipg->getToken($amount, $redirect_address);
if( $result->status == 'success' ){
    header('Location: https://pms.rayanpay.com/pg/startpay/' . $result->token);
    die();
}
else{
    echo "Error: {$result->message}";
}
```
## Payment verification and settle
After payment the customer will be redirected back to the callback URL provided in payment request phase via a GET request carrying all necessary data including:
* Authority: Payment token by which the user has been redirected to payment page
* Status: Payment status which should be ‍`OK` for successful payments

If `Status` equals `OK` we can call the `verifyRequest` method to verify payment.
### `verifyRequest` method
#### Arguments:
* amount: Original order amoun tused in payment request
* token: Authority parameter returned by IPG
#### Returns:
An object with the following properties:
* status: `success` or `error`
* message: message describing the status
* ref_id: reference id in case of successful transaction
#### Code sample:
```php
$args = [
    'merchantId'    => '4e1598fc-09b8-29e6-2edc-bf5494616b4d'
]; //Replace arguments with your gateway actual values
$ipg = new RayanpayIpg($args);
$amount = 1000; //Replace with actual order amount in Rials
$result = $ipg->verifyRequest($amount, $_GET['Authority']);
```
Getting `success` status in response means that the transction is successful and settled.