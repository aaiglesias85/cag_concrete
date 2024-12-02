# Getting started

## How to Build

The generated code has dependencies over external libraries like UniRest. These dependencies are defined in the ```composer.json``` file that comes with the SDK. 
To resolve these dependencies, we use the Composer package manager which requires PHP greater than 5.3.2 installed in your system. 
Visit [https://getcomposer.org/download/](https://getcomposer.org/download/) to download the installer file for Composer and run it in your system. 
Open command prompt and type ```composer --version```. This should display the current version of the Composer installed if the installation was successful.

* Using command line, navigate to the directory containing the generated files (including ```composer.json```) for the SDK. 
* Run the command ```composer install```. This should install all the required dependencies and create the ```vendor``` directory in your project directory.

![Building SDK - Step 1](https://apidocs.io/illustration/php?step=installDependencies&workspaceFolder=Genius%20Referrals-PHP)

### [For Windows Users Only] Configuring CURL Certificate Path in php.ini

CURL used to include a list of accepted CAs, but no longer bundles ANY CA certs. So by default it will reject all SSL certificates as unverifiable. You will have to get your CA's cert and point curl at it. The steps are as follows:

1. Download the certificate bundle (.pem file) from [https://curl.haxx.se/docs/caextract.html](https://curl.haxx.se/docs/caextract.html) on to your system.
2. Add curl.cainfo = "PATH_TO/cacert.pem" to your php.ini file located in your php installation. “PATH_TO” must be an absolute path containing the .pem file.

```ini
[curl]
; A default value for the CURLOPT_CAINFO option. This is required to be an
; absolute path.
;curl.cainfo =
```
## How to Intall on your Project via Composer
```
composer install geniusreferrals/geniusreferrals
```

## How to Use
The following section explains how to use the GeniusReferrals library in a new project.

### 1. Open Project in an IDE

Open an IDE for PHP like PhpStorm. The basic workflow presented here is also applicable if you prefer using a different editor or IDE.

![Open project in PHPStorm - Step 1](https://apidocs.io/illustration/php?step=openIDE&workspaceFolder=Genius%20Referrals-PHP)

Click on ```Open``` in PhpStorm to browse to your generated SDK directory and then click ```OK```.

![Open project in PHPStorm - Step 2](https://apidocs.io/illustration/php?step=openProject0&workspaceFolder=Genius%20Referrals-PHP)     

### 2. Add a new Test Project

Create a new directory by right clicking on the solution name as shown below:

![Add a new project in PHPStorm - Step 1](https://apidocs.io/illustration/php?step=createDirectory&workspaceFolder=Genius%20Referrals-PHP)

Name the directory as "test"

![Add a new project in PHPStorm - Step 2](https://apidocs.io/illustration/php?step=nameDirectory&workspaceFolder=Genius%20Referrals-PHP)
   
Add a PHP file to this project

![Add a new project in PHPStorm - Step 3](https://apidocs.io/illustration/php?step=createFile&workspaceFolder=Genius%20Referrals-PHP)

Name it "testSDK"

![Add a new project in PHPStorm - Step 4](https://apidocs.io/illustration/php?step=nameFile&workspaceFolder=Genius%20Referrals-PHP)

Depending on your project setup, you might need to include composer's autoloader in your PHP code to enable auto loading of classes.

```PHP
require_once "../vendor/autoload.php";
```

It is important that the path inside require_once correctly points to the file ```autoload.php``` inside the vendor directory created during dependency installations.

![Add a new project in PHPStorm - Step 4](https://apidocs.io/illustration/php?step=projectFiles&workspaceFolder=Genius%20Referrals-PHP)

After this you can add code to initialize the client library and acquire the instance of a Controller class. Sample code to initialize the client library and using controller methods is given in the subsequent sections.

### 3. Run the Test Project

To run your project you must set the Interpreter for your project. Interpreter is the PHP engine installed on your computer.

Open ```Settings``` from ```File``` menu.

![Run Test Project - Step 1](https://apidocs.io/illustration/php?step=openSettings&workspaceFolder=Genius%20Referrals-PHP)

Select ```PHP``` from within ```Languages & Frameworks```

![Run Test Project - Step 2](https://apidocs.io/illustration/php?step=setInterpreter0&workspaceFolder=Genius%20Referrals-PHP)

Browse for Interpreters near the ```Interpreter``` option and choose your interpreter.

![Run Test Project - Step 3](https://apidocs.io/illustration/php?step=setInterpreter1&workspaceFolder=Genius%20Referrals-PHP)

Once the interpreter is selected, click ```OK```

![Run Test Project - Step 4](https://apidocs.io/illustration/php?step=setInterpreter2&workspaceFolder=Genius%20Referrals-PHP)

To run your project, right click on your PHP file inside your Test project and click on ```Run```

![Run Test Project - Step 5](https://apidocs.io/illustration/php?step=runProject&workspaceFolder=Genius%20Referrals-PHP)

## How to Test

Unit tests in this SDK can be run using PHPUnit. 

1. First install the dependencies using composer including the `require-dev` dependencies.
2. Run `vendor\bin\phpunit --verbose` from commandline to execute tests. If you have 
   installed PHPUnit globally, run tests using `phpunit --verbose` instead.

You can change the PHPUnit test configuration in the `phpunit.xml` file.

## Initialization

### Authentication
In order to setup authentication and initialization of the API client, you need the following information.

| Parameter | Description |
|-----------|-------------|
| contentType | The content type |
| xAuthToken | Your API Token, you can get your token here https://www.geniusreferrals.com/en/settings/api-access |



API client can be initialized as following.

```php
// Configuration parameters and credentials
$contentType = "application/json"; // The content type
$xAuthToken = "3b9d11374b602fb47b987dff90f1c5940a1f377f"; // Your API Token, you can get your token here https://www.geniusreferrals.com/en/settings/api-access

$client = new GeniusReferralsLib\GeniusReferralsClient($contentType, $xAuthToken);
```

## Class Reference

### <a name="list_of_controllers"></a>List of Controllers

* [RootsController](#roots_controller)
* [AuthenticationsController](#authentications_controller)
* [AdvocatesController](#advocates_controller)
* [AccountsController](#accounts_controller)
* [ReportsController](#reports_controller)
* [ReferralsController](#referrals_controller)
* [RedemptionRequestsController](#redemption_requests_controller)
* [BonusesController](#bonuses_controller)
* [CampaignsController](#campaigns_controller)

### <a name="roots_controller"></a>![Class: ](https://apidocs.io/img/class.png ".RootsController") RootsController

#### Get singleton instance

The singleton instance of the ``` RootsController ``` class can be accessed from the API Client.

```php
$roots = $client->getRoots();
```

#### <a name="get_root"></a>![Method: ](https://apidocs.io/img/method.png ".RootsController.getRoot") getRoot

> The root of the API


```php
function getRoot()
```

#### Example Usage

```php

$result = $roots->getRoot();

```


[Back to List of Controllers](#list_of_controllers)

### <a name="authentications_controller"></a>![Class: ](https://apidocs.io/img/class.png ".AuthenticationsController") AuthenticationsController

#### Get singleton instance

The singleton instance of the ``` AuthenticationsController ``` class can be accessed from the API Client.

```php
$authentications = $client->getAuthentications();
```

#### <a name="get_authentication"></a>![Method: ](https://apidocs.io/img/method.png ".AuthenticationsController.getAuthentication") getAuthentication

> Allow clients to test authentication on Genius Referrals platform.


```php
function getAuthentication()
```

#### Example Usage

```php

$result = $authentications->getAuthentication();

```


[Back to List of Controllers](#list_of_controllers)

### <a name="advocates_controller"></a>![Class: ](https://apidocs.io/img/class.png ".AdvocatesController") AdvocatesController

#### Get singleton instance

The singleton instance of the ``` AdvocatesController ``` class can be accessed from the API Client.

```php
$advocates = $client->getAdvocates();
```

#### <a name="delete_advocate"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.deleteAdvocate") deleteAdvocate

> Delete an advocate


```php
function deleteAdvocate(
        $accountSlug,
        $advocateToken)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';

$advocates->deleteAdvocate($accountSlug, $advocateToken);

```


#### <a name="put_advocate"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.putAdvocate") putAdvocate

> Update an advocate.


```php
function putAdvocate(
        $accountSlug,
        $advocateToken,
        $advocateForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| advocateForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$advocateForm = new AdvocateForm();

$advocates->putAdvocate($accountSlug, $advocateToken, $advocateForm);

```


#### <a name="post_advocate"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.postAdvocate") postAdvocate

> Create a new advocate.


```php
function postAdvocate(
        $accountSlug,
        $advocateForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateForm = new AdvocateForm();

$result = $advocates->postAdvocate($accountSlug, $advocateForm);

```


#### <a name="get_advocate"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getAdvocate") getAdvocate

> Get an advocate by a given token.


```php
function getAdvocate(
        $accountSlug,
        $advocateToken)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';

$result = $advocates->getAdvocate($accountSlug, $advocateToken);

```


#### <a name="delete_advocates"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.deleteAdvocates") deleteAdvocates

> Delete all advocates


```php
function deleteAdvocates($accountSlug)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |



#### Example Usage

```php
$accountSlug = 'account_slug';

$advocates->deleteAdvocates($accountSlug);

```


#### <a name="get_advocates"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getAdvocates") getAdvocates

> Get the list of advocates


```php
function getAdvocates(
        $accountSlug,
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: name, lastname, email, advocate_token, bonus_exchange_method_slug, campaign_slug, can_refer, is_referral, from, to, created. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: name, lastname, email, created. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort='last_name\|first_name\|-hire_date' |



#### Example Usage

```php
$accountSlug = 'account_slug';
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $advocates->getAdvocates($accountSlug, $page, $limit, $filter, $sort);

```


#### <a name="patch_advocate"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.patchAdvocate") patchAdvocate

> Update partial elements of an advocate.


```php
function patchAdvocate(
        $accountSlug,
        $advocateToken,
        $advocatePatchForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| advocatePatchForm |  ``` Required ```  ``` Collection ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$advocatePatchForm = new AdvocatePatchForm();

$result = $advocates->patchAdvocate($accountSlug, $advocateToken, $advocatePatchForm);

```


#### <a name="get_share_links"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getShareLinks") getShareLinks

> Get the advocates share links. These are the links that advocates use to share your services online.  Share links are wrapped per campaign and widget package.


```php
function getShareLinks(
        $accountSlug,
        $advocateToken)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';

$result = $advocates->getShareLinks($accountSlug, $advocateToken);

```


#### <a name="put_payment_method"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.putPaymentMethod") putPaymentMethod

> Update a payment method.


```php
function putPaymentMethod(
        $accountSlug,
        $advocateToken,
        $advocatePaymentMethodId,
        $advocatePaymentMethodForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The advocate's token |
| advocateToken |  ``` Required ```  | The advocate's token |
| advocatePaymentMethodId |  ``` Required ```  | The payment method's identifier |
| advocatePaymentMethodForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$advocatePaymentMethodId = 147;
$advocatePaymentMethodForm = new PaymentMethodForm();

$advocates->putPaymentMethod($accountSlug, $advocateToken, $advocatePaymentMethodId, $advocatePaymentMethodForm);

```


#### <a name="get_payment_method"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getPaymentMethod") getPaymentMethod

> Get an advocate's payment method


```php
function getPaymentMethod(
        $accountSlug,
        $advocateToken,
        $advocatePaymentMethodId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| advocatePaymentMethodId |  ``` Required ```  | The payment method's identifier |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$advocatePaymentMethodId = 147;

$result = $advocates->getPaymentMethod($accountSlug, $advocateToken, $advocatePaymentMethodId);

```


#### <a name="post_payment_method"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.postPaymentMethod") postPaymentMethod

> Create a new payment method.


```php
function postPaymentMethod(
        $accountSlug,
        $advocateToken,
        $advocatePaymentMethodForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| advocatePaymentMethodForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$advocatePaymentMethodForm = new PaymentMethodForm();

$result = $advocates->postPaymentMethod($accountSlug, $advocateToken, $advocatePaymentMethodForm);

```


#### <a name="get_bonus_redemption_method"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getBonusRedemptionMethod") getBonusRedemptionMethod

> Get bonuses redemption method.


```php
function getBonusRedemptionMethod($bonusesRedemptionMethodSlug)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| bonusesRedemptionMethodSlug |  ``` Required ```  | The bonus redemption method's identifier |



#### Example Usage

```php
$bonusesRedemptionMethodSlug = 'bonuses_redemption_method_slug';

$result = $advocates->getBonusRedemptionMethod($bonusesRedemptionMethodSlug);

```


#### <a name="get_bonus_redemption_methods"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getBonusRedemptionMethods") getBonusRedemptionMethods

> Get bonuses redemption methods.


```php
function getBonusRedemptionMethods()
```

#### Example Usage

```php

$result = $advocates->getBonusRedemptionMethods();

```


#### <a name="get_currencies"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getCurrencies") getCurrencies

> Get currencies.


```php
function getCurrencies()
```

#### Example Usage

```php

$result = $advocates->getCurrencies();

```


#### <a name="get_currency"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getCurrency") getCurrency

> Get a currency.


```php
function getCurrency($code)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| code |  ``` Required ```  | The currency's code |



#### Example Usage

```php
$code = 'code';

$result = $advocates->getCurrency($code);

```


#### <a name="get_payment_methods"></a>![Method: ](https://apidocs.io/img/method.png ".AdvocatesController.getPaymentMethods") getPaymentMethods

> Get the advocate's payment methods.


```php
function getPaymentMethods(
        $accountSlug,
        $advocateToken,
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: username, is_active. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: username, created. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort=last_name\|first_name\|-hire_date |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $advocates->getPaymentMethods($accountSlug, $advocateToken, $page, $limit, $filter, $sort);

```


[Back to List of Controllers](#list_of_controllers)

### <a name="accounts_controller"></a>![Class: ](https://apidocs.io/img/class.png ".AccountsController") AccountsController

#### Get singleton instance

The singleton instance of the ``` AccountsController ``` class can be accessed from the API Client.

```php
$accounts = $client->getAccounts();
```

#### <a name="get_account"></a>![Method: ](https://apidocs.io/img/method.png ".AccountsController.getAccount") getAccount

> Get an account by a given slug.


```php
function getAccount($accountSlug)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |



#### Example Usage

```php
$accountSlug = 'account_slug';

$result = $accounts->getAccount($accountSlug);

```


#### <a name="get_accounts"></a>![Method: ](https://apidocs.io/img/method.png ".AccountsController.getAccounts") getAccounts

> Get the list of accounts.


```php
function getAccounts(
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: name. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: name, created. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort=last_name\|first_name\|-hire_date |



#### Example Usage

```php
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $accounts->getAccounts($page, $limit, $filter, $sort);

```


[Back to List of Controllers](#list_of_controllers)

### <a name="reports_controller"></a>![Class: ](https://apidocs.io/img/class.png ".ReportsController") ReportsController

#### Get singleton instance

The singleton instance of the ``` ReportsController ``` class can be accessed from the API Client.

```php
$reports = $client->getReports();
```

#### <a name="get_referrals_summary_per_origin"></a>![Method: ](https://apidocs.io/img/method.png ".ReportsController.getReferralsSummaryPerOrigin") getReferralsSummaryPerOrigin

> Get referrals summary per referral origin.


```php
function getReferralsSummaryPerOrigin($advocateToken)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| advocateToken |  ``` Required ```  | The advocate's token |



#### Example Usage

```php
$advocateToken = 'advocate_token';

$result = $reports->getReferralsSummaryPerOrigin($advocateToken);

```


#### <a name="get_bonuses_summary_per_origin"></a>![Method: ](https://apidocs.io/img/method.png ".ReportsController.getBonusesSummaryPerOrigin") getBonusesSummaryPerOrigin

> Get bonuses summary per referral origin.


```php
function getBonusesSummaryPerOrigin($advocateToken)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| advocateToken |  ``` Required ```  | The advocate's token |



#### Example Usage

```php
$advocateToken = 'advocate_token';

$result = $reports->getBonusesSummaryPerOrigin($advocateToken);

```


#### <a name="get_top_advocates"></a>![Method: ](https://apidocs.io/img/method.png ".ReportsController.getTopAdvocates") getTopAdvocates

> Get top 10 advocates.


```php
function getTopAdvocates(
        $accountSlug = null,
        $campaignSlug = null,
        $limit = 10,
        $from = null,
        $to = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Optional ```  | The account identifier |
| campaignSlug |  ``` Optional ```  | The campaign identifier |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10) |
| from |  ``` Optional ```  | The datetime were the range of the search starts |
| to |  ``` Optional ```  | The datetime were the range of the search stops |



#### Example Usage

```php
$accountSlug = 'account_slug';
$campaignSlug = 'campaign_slug';
$limit = 10;
$from = date("D M d, Y G:i");
$to = date("D M d, Y G:i");

$result = $reports->getTopAdvocates($accountSlug, $campaignSlug, $limit, $from, $to);

```


#### <a name="get_share_daily_participation"></a>![Method: ](https://apidocs.io/img/method.png ".ReportsController.getShareDailyParticipation") getShareDailyParticipation

> Get share daily participation.


```php
function getShareDailyParticipation(
        $accountSlug = null,
        $campaignSlug = null,
        $advocateToken = null,
        $from = null,
        $to = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Optional ```  | The account identifier |
| campaignSlug |  ``` Optional ```  | The campaign identifier |
| advocateToken |  ``` Optional ```  | The advocate's token |
| from |  ``` Optional ```  | The datetime were the range of the search starts |
| to |  ``` Optional ```  | The datetime were the range of the search stops |



#### Example Usage

```php
$accountSlug = 'account_slug';
$campaignSlug = 'campaign_slug';
$advocateToken = 'advocate_token';
$from = date("D M d, Y G:i");
$to = date("D M d, Y G:i");

$result = $reports->getShareDailyParticipation($accountSlug, $campaignSlug, $advocateToken, $from, $to);

```


#### <a name="get_referral_daily_participation"></a>![Method: ](https://apidocs.io/img/method.png ".ReportsController.getReferralDailyParticipation") getReferralDailyParticipation

> Get referral daily participation.


```php
function getReferralDailyParticipation(
        $accountSlug = null,
        $campaignSlug = null,
        $advocateToken = null,
        $from = null,
        $to = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Optional ```  | The account identifier |
| campaignSlug |  ``` Optional ```  | The campaign identifier |
| advocateToken |  ``` Optional ```  | The advocate's token |
| from |  ``` Optional ```  | The datetime were the range of the search starts |
| to |  ``` Optional ```  | The datetime were the range of the search stops |



#### Example Usage

```php
$accountSlug = 'account_slug';
$campaignSlug = 'campaign_slug';
$advocateToken = 'advocate_token';
$from = date("D M d, Y G:i");
$to = date("D M d, Y G:i");

$result = $reports->getReferralDailyParticipation($accountSlug, $campaignSlug, $advocateToken, $from, $to);

```


#### <a name="get_click_daily_participation"></a>![Method: ](https://apidocs.io/img/method.png ".ReportsController.getClickDailyParticipation") getClickDailyParticipation

> Get click daily participation.


```php
function getClickDailyParticipation(
        $accountSlug = null,
        $campaignSlug = null,
        $advocateToken = null,
        $from = null,
        $to = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Optional ```  | The account identifier |
| campaignSlug |  ``` Optional ```  | The campaign identifier |
| advocateToken |  ``` Optional ```  | The advocate's token |
| from |  ``` Optional ```  | The datetime were the range of the search starts |
| to |  ``` Optional ```  | The datetime were the range of the search stops |



#### Example Usage

```php
$accountSlug = 'account_slug';
$campaignSlug = 'campaign_slug';
$advocateToken = 'advocate_token';
$from = date("D M d, Y G:i");
$to = date("D M d, Y G:i");

$result = $reports->getClickDailyParticipation($accountSlug, $campaignSlug, $advocateToken, $from, $to);

```


#### <a name="get_bonuses_daily_given"></a>![Method: ](https://apidocs.io/img/method.png ".ReportsController.getBonusesDailyGiven") getBonusesDailyGiven

> Get bonuses daily given.


```php
function getBonusesDailyGiven(
        $accountSlug = null,
        $campaignSlug = null,
        $advocateToken = null,
        $from = null,
        $to = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Optional ```  | The account identifier |
| campaignSlug |  ``` Optional ```  | The campaign identifier |
| advocateToken |  ``` Optional ```  | The advocate identifier |
| from |  ``` Optional ```  | The datetime were the range of the search starts |
| to |  ``` Optional ```  | The datetime were the range of the search stops |



#### Example Usage

```php
$accountSlug = 'account_slug';
$campaignSlug = 'campaign_slug';
$advocateToken = 'advocate_token';
$from = date("D M d, Y G:i");
$to = date("D M d, Y G:i");

$result = $reports->getBonusesDailyGiven($accountSlug, $campaignSlug, $advocateToken, $from, $to);

```


[Back to List of Controllers](#list_of_controllers)

### <a name="referrals_controller"></a>![Class: ](https://apidocs.io/img/class.png ".ReferralsController") ReferralsController

#### Get singleton instance

The singleton instance of the ``` ReferralsController ``` class can be accessed from the API Client.

```php
$referrals = $client->getReferrals();
```

#### <a name="get_referral_origin"></a>![Method: ](https://apidocs.io/img/method.png ".ReferralsController.getReferralOrigin") getReferralOrigin

> Get a referral origin by a given slug.


```php
function getReferralOrigin($referralOriginSlug)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| referralOriginSlug |  ``` Required ```  | The referral origin identifier |



#### Example Usage

```php
$referralOriginSlug = 'referral_origin_slug';

$result = $referrals->getReferralOrigin($referralOriginSlug);

```


#### <a name="get_referral_origins"></a>![Method: ](https://apidocs.io/img/method.png ".ReferralsController.getReferralOrigins") getReferralOrigins

> Get referral origins. This is needed when creating (POST) a new referral, as referral_origin_slug refers to one of this origins.


```php
function getReferralOrigins()
```

#### Example Usage

```php

$result = $referrals->getReferralOrigins();

```


#### <a name="get_referral"></a>![Method: ](https://apidocs.io/img/method.png ".ReferralsController.getReferral") getReferral

> Get a referral by a given id.


```php
function getReferral(
        $accountSlug,
        $advocateToken,
        $referralId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| referralId |  ``` Required ```  | The referral id |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$referralId = 'referral_id';

$result = $referrals->getReferral($accountSlug, $advocateToken, $referralId);

```


#### <a name="delete_referral"></a>![Method: ](https://apidocs.io/img/method.png ".ReferralsController.deleteReferral") deleteReferral

> Delete a referral.


```php
function deleteReferral(
        $accountSlug,
        $advocateToken,
        $referralId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| referralId |  ``` Required ```  | The referral identifier |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$referralId = 'referral_id';

$referrals->deleteReferral($accountSlug, $advocateToken, $referralId);

```


#### <a name="post_referral"></a>![Method: ](https://apidocs.io/img/method.png ".ReferralsController.postReferral") postReferral

> Create a new referral.


```php
function postReferral(
        $accountSlug,
        $advocateToken,
        $referralForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| referralForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$referralForm = new ReferralForm();

$result = $referrals->postReferral($accountSlug, $advocateToken, $referralForm);

```


#### <a name="put_referral"></a>![Method: ](https://apidocs.io/img/method.png ".ReferralsController.putReferral") putReferral

> Update a referral.


```php
function putReferral(
        $accountSlug,
        $advocateToken,
        $referralId,
        $referralForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| referralId |  ``` Required ```  | The referral id |
| referralForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$referralId = 'referral_id';
$referralForm = new ReferralForm();

$referrals->putReferral($accountSlug, $advocateToken, $referralId, $referralForm);

```


#### <a name="get_referrals"></a>![Method: ](https://apidocs.io/img/method.png ".ReferralsController.getReferrals") getReferrals

> Get the list of referrals for a given advocate.


```php
function getReferrals(
        $accountSlug,
        $advocateToken,
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The advocate's token |
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: url, referral_origin_slug, created. Use the following delimiters to build your filters params. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: created. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort='last_name\|first_name\|-hire_date' |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $referrals->getReferrals($accountSlug, $advocateToken, $page, $limit, $filter, $sort);

```


[Back to List of Controllers](#list_of_controllers)

### <a name="redemption_requests_controller"></a>![Class: ](https://apidocs.io/img/class.png ".RedemptionRequestsController") RedemptionRequestsController

#### Get singleton instance

The singleton instance of the ``` RedemptionRequestsController ``` class can be accessed from the API Client.

```php
$redemptionRequests = $client->getRedemptionRequests();
```

#### <a name="get_redemption_request_status"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.getRedemptionRequestStatus") getRedemptionRequestStatus

> Get a redemption request status.


```php
function getRedemptionRequestStatus($redemptionRequestStatusSlug)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| redemptionRequestStatusSlug |  ``` Required ```  | The redemption request status identifier |



#### Example Usage

```php
$redemptionRequestStatusSlug = 'redemption_request_status_slug';

$result = $redemptionRequests->getRedemptionRequestStatus($redemptionRequestStatusSlug);

```


#### <a name="get_redemption_request_statuses"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.getRedemptionRequestStatuses") getRedemptionRequestStatuses

> Get redemption request statuses.


```php
function getRedemptionRequestStatuses()
```

#### Example Usage

```php

$result = $redemptionRequests->getRedemptionRequestStatuses();

```


#### <a name="get_redemption_request_action"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.getRedemptionRequestAction") getRedemptionRequestAction

> Get a redemption request action.


```php
function getRedemptionRequestAction($redemptionRequestActionSlug)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| redemptionRequestActionSlug |  ``` Required ```  | The redemption request action identifier |



#### Example Usage

```php
$redemptionRequestActionSlug = 'redemption_request_action_slug';

$result = $redemptionRequests->getRedemptionRequestAction($redemptionRequestActionSlug);

```


#### <a name="get_redemption_request_actions"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.getRedemptionRequestActions") getRedemptionRequestActions

> Get redemption request actions.


```php
function getRedemptionRequestActions()
```

#### Example Usage

```php

$result = $redemptionRequests->getRedemptionRequestActions();

```


#### <a name="patch_redemption_request"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.patchRedemptionRequest") patchRedemptionRequest

> Redeem a redemption request. After the redemption request is created it needs to be redeemed. This will deduct the amount of the advocate unclaimed balance and move the request to the completed state.


```php
function patchRedemptionRequest(
        $accountSlug,
        $redemptionRequestId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| redemptionRequestId |  ``` Required ```  | The redemption request id |



#### Example Usage

```php
$accountSlug = 'account_slug';
$redemptionRequestId = 147;

$redemptionRequests->patchRedemptionRequest($accountSlug, $redemptionRequestId);

```


#### <a name="post_redemption_request"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.postRedemptionRequest") postRedemptionRequest

> Create a redemption request.


```php
function postRedemptionRequest(
        $accountSlug,
        $redemptionRequestForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| redemptionRequestForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$redemptionRequestForm = new RedemptionRequestForm();

$result = $redemptionRequests->postRedemptionRequest($accountSlug, $redemptionRequestForm);

```


#### <a name="get_redemption_request"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.getRedemptionRequest") getRedemptionRequest

> Get a redemption request by a given id.


```php
function getRedemptionRequest(
        $accountSlug,
        $redemptionRequestId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| redemptionRequestId |  ``` Required ```  | The redemption request identifier |



#### Example Usage

```php
$accountSlug = 'account_slug';
$redemptionRequestId = 'redemption_request_id';

$result = $redemptionRequests->getRedemptionRequest($accountSlug, $redemptionRequestId);

```


#### <a name="get_redemption_requests"></a>![Method: ](https://apidocs.io/img/method.png ".RedemptionRequestsController.getRedemptionRequests") getRedemptionRequests

> Get the list of redemption requests.


```php
function getRedemptionRequests(
        $accountSlug,
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: redemption_request_id, name, lastname, email, request_status_slug, request_action_slug, from, to, created. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: name, lastname, email, created. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort='last_name\|first_name\|-hire_date' |



#### Example Usage

```php
$accountSlug = 'account_slug';
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $redemptionRequests->getRedemptionRequests($accountSlug, $page, $limit, $filter, $sort);

```


[Back to List of Controllers](#list_of_controllers)

### <a name="bonuses_controller"></a>![Class: ](https://apidocs.io/img/class.png ".BonusesController") BonusesController

#### Get singleton instance

The singleton instance of the ``` BonusesController ``` class can be accessed from the API Client.

```php
$bonuses = $client->getBonuses();
```

#### <a name="get_bonuses"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.getBonuses") getBonuses

> Get the list of bonuses for a given account.


```php
function getBonuses(
        $accountSlug,
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: name, lastname, email, campaign_slug, from, to, created. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: name, lastname, email, created. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort='last_name\|first_name\|-hire_date' |



#### Example Usage

```php
$accountSlug = 'account_slug';
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $bonuses->getBonuses($accountSlug, $page, $limit, $filter, $sort);

```


#### <a name="post_bonus"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.postBonus") postBonus

> Make an attempt to give a bonus for to the advocate's referrer. The system processes the given advocate (referral) and creates a bonus for the advocate's referrer if is needed. All restrictions set on the campaigns for this account will be check out before giving the bonus to the advocate's referrer.


```php
function postBonus(
        $accountSlug,
        $bonusesForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| bonusesForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$bonusesForm = new BonusesForm();

$result = $bonuses->postBonus($accountSlug, $bonusesForm);

```


#### <a name="get_bonus_checkup"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.getBonusCheckup") getBonusCheckup

> Check if there is a bonus to be given to the advocate. Allows the clients to check if there is a bonus to be given, it simulates the behaivor of a POST request to /accounts/{account_slug}/bonuses resource. This resource is idempotent.


```php
function getBonusCheckup(
        $accountSlug,
        $advocateToken,
        $reference,
        $paymentAmount)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| advocateToken |  ``` Required ```  | The referral's token. Usually the one that completed the purchase, or trigger an event. |
| reference |  ``` Required ```  | The reference number for this request. Usually the order_id, payment_id, or timestamp. |
| paymentAmount |  ``` Required ```  | The payment amount the referrals has made. Required for a percentage based campaign. |



#### Example Usage

```php
$accountSlug = 'account_slug';
$advocateToken = 'advocate_token';
$reference = 'reference';
$paymentAmount = 105.64688331012;

$result = $bonuses->getBonusCheckup($accountSlug, $advocateToken, $reference, $paymentAmount);

```


#### <a name="post_force_bonus"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.postForceBonus") postForceBonus

> Force the system to give a bonus to an advocate. The system will not take into account the restriccions specified on the campaigns.


```php
function postForceBonus(
        $accountSlug,
        $bonusForm)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| bonusForm |  ``` Required ```  | The body of the request |



#### Example Usage

```php
$accountSlug = 'account_slug';
$bonusForm = new ForceBonusesForm();

$result = $bonuses->postForceBonus($accountSlug, $bonusForm);

```


#### <a name="get_bonus_trace"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.getBonusTrace") getBonusTrace

> Get a bonus request trace.


```php
function getBonusTrace(
        $accountSlug,
        $traceId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| traceId |  ``` Required ```  | The trace id |



#### Example Usage

```php
$accountSlug = 'account_slug';
$traceId = 105;

$result = $bonuses->getBonusTrace($accountSlug, $traceId);

```


#### <a name="delete_bonus"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.deleteBonus") deleteBonus

> Delete a bonus


```php
function deleteBonus(
        $accountSlug,
        $bonusId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| bonusId |  ``` Required ```  | The bonus id |



#### Example Usage

```php
$accountSlug = 'account_slug';
$bonusId = 105;

$bonuses->deleteBonus($accountSlug, $bonusId);

```


#### <a name="get_bonus"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.getBonus") getBonus

> Get a bonus by a given id.


```php
function getBonus(
        $accountSlug,
        $bonusId)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| bonusId |  ``` Required ```  | The bonus id |



#### Example Usage

```php
$accountSlug = 'account_slug';
$bonusId = 105;

$result = $bonuses->getBonus($accountSlug, $bonusId);

```


#### <a name="get_bonus_traces"></a>![Method: ](https://apidocs.io/img/method.png ".BonusesController.getBonusTraces") getBonusTraces

> Get the list of bonuses traces (audit trail). Every time the system tries to give a bonus the an advocate a new trace is created.


```php
function getBonusTraces(
        $accountSlug,
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: reference, result, bonus_amount, advocate_token, advocate_referrer_token, campaign_slug, from, to, created. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: created. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort='last_name\|first_name\|-hire_date' |



#### Example Usage

```php
$accountSlug = 'account_slug';
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $bonuses->getBonusTraces($accountSlug, $page, $limit, $filter, $sort);

```


[Back to List of Controllers](#list_of_controllers)

### <a name="campaigns_controller"></a>![Class: ](https://apidocs.io/img/class.png ".CampaignsController") CampaignsController

#### Get singleton instance

The singleton instance of the ``` CampaignsController ``` class can be accessed from the API Client.

```php
$campaigns = $client->getCampaigns();
```

#### <a name="get_campaign"></a>![Method: ](https://apidocs.io/img/method.png ".CampaignsController.getCampaign") getCampaign

> Get a campaign by a given slug.


```php
function getCampaign(
        $accountSlug,
        $campaignSlug)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| campaignSlug |  ``` Required ```  | The campaign identifier |



#### Example Usage

```php
$accountSlug = 'account_slug';
$campaignSlug = 'campaign_slug';

$result = $campaigns->getCampaign($accountSlug, $campaignSlug);

```


#### <a name="get_campaigns"></a>![Method: ](https://apidocs.io/img/method.png ".CampaignsController.getCampaigns") getCampaigns

> Get the list of campaings for a given account.


```php
function getCampaigns(
        $accountSlug,
        $page = 1,
        $limit = 10,
        $filter = null,
        $sort = null)
```

#### Parameters

| Parameter | Tags | Description |
|-----------|------|-------------|
| accountSlug |  ``` Required ```  | The account identifier |
| page |  ``` Optional ```  ``` DefaultValue ```  | Page number, e.g. 1 would start at the first result, and 10 would start at the tenth result. |
| limit |  ``` Optional ```  ``` DefaultValue ```  | Maximum number of results to return in the response. Default (10), threshold (100) |
| filter |  ``` Optional ```  | Allowed fields: name, description, start_date, end_date, is_active (true\|false), created. Use the following delimiters to build your filters params. The vertical bar ('\|') to separate individual filter phrases and a double colon ('::') to separate the names and values. The delimiter of the double colon (':') separates the property name from the comparison value, enabling the comparison value to contain spaces. Example: www.example.com/users?filter='name::todd\|city::denver\|title::grand poobah' |
| sort |  ``` Optional ```  | Allowed fields: campaign_slug, created, start_date, end_date, is_active. Use sort query-string parameter that contains a delimited set of property names. For each property name, sort in ascending order, and for each property prefixed with a dash ('-') sort in descending order. Separate each property name with a vertical bar ('\|'), which is consistent with the separation of the name\|value pairs in filtering, above. For example, if we want to retrieve users in order of their last name (ascending), first name (ascending) and hire date (descending), the request might look like this www.example.com/users?sort='last_name\|first_name\|-hire_date' |



#### Example Usage

```php
$accountSlug = 'account_slug';
$page = 1;
$limit = 10;
$filter = 'filter';
$sort = 'sort';

$result = $campaigns->getCampaigns($accountSlug, $page, $limit, $filter, $sort);

```


[Back to List of Controllers](#list_of_controllers)



