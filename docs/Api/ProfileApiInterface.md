# OpenAPI\Server\Api\ProfileApiInterface

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**profileGet**](ProfileApiInterface.md#profileGet) | **GET** /profile | 
[**profilePost**](ProfileApiInterface.md#profilePost) | **POST** /profile | 


## Service Declaration
```yaml
# config/services.yaml
services:
    # ...
    Acme\MyBundle\Api\ProfileApi:
        tags:
            - { name: "open_api_server.api", api: "profile" }
    # ...
```

## **profileGet**
> OpenAPI\Server\Model\ProfileResponse profileGet()



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/ProfileApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\ProfileApiInterface;

class ProfileApi implements ProfileApiInterface
{

    // ...

    /**
     * Implementation of ProfileApiInterface#profileGet
     */
    public function profileGet(int &$responseCode, array &$responseHeaders): array|object|null
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters
This endpoint does not need any parameter.

### Return type

[**OpenAPI\Server\Model\ProfileResponse**](../Model/ProfileResponse.md)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **profilePost**
> OpenAPI\Server\Model\Profile profilePost($firstName, $lastName, $email, $phoneNumber, $avatar)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/ProfileApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\ProfileApiInterface;

class ProfileApi implements ProfileApiInterface
{

    // ...

    /**
     * Implementation of ProfileApiInterface#profilePost
     */
    public function profilePost(?string $firstName, ?string $lastName, ?string $email, ?string $phoneNumber, ?UploadedFile $avatar, int &$responseCode, array &$responseHeaders): array|object|null
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **firstName** | **string**|  | [optional]
 **lastName** | **string**|  | [optional]
 **email** | **string**|  | [optional]
 **phoneNumber** | **string**|  | [optional]
 **avatar** | **UploadedFile****UploadedFile**|  | [optional]

### Return type

[**OpenAPI\Server\Model\Profile**](../Model/Profile.md)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: multipart/form-data
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

