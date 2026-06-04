# OpenAPI\Server\Api\RequestsApiInterface

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**requestsIdDelete**](RequestsApiInterface.md#requestsIdDelete) | **DELETE** /requests/{id} | 
[**requestsIdPatch**](RequestsApiInterface.md#requestsIdPatch) | **PATCH** /requests/{id} | 
[**requestsPost**](RequestsApiInterface.md#requestsPost) | **POST** /requests | 


## Service Declaration
```yaml
# config/services.yaml
services:
    # ...
    Acme\MyBundle\Api\RequestsApi:
        tags:
            - { name: "open_api_server.api", api: "requests" }
    # ...
```

## **requestsIdDelete**
> requestsIdDelete($id)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/RequestsApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\RequestsApiInterface;

class RequestsApi implements RequestsApiInterface
{

    // ...

    /**
     * Implementation of RequestsApiInterface#requestsIdDelete
     */
    public function requestsIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**|  |

### Return type

void (empty response body)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **requestsIdPatch**
> requestsIdPatch($id, $requestsIdPatchRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/RequestsApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\RequestsApiInterface;

class RequestsApi implements RequestsApiInterface
{

    // ...

    /**
     * Implementation of RequestsApiInterface#requestsIdPatch
     */
    public function requestsIdPatch(int $id, ?RequestsIdPatchRequest $requestsIdPatchRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **id** | **int**|  |
 **requestsIdPatchRequest** | [**OpenAPI\Server\Model\RequestsIdPatchRequest**](../Model/RequestsIdPatchRequest.md)|  | [optional]

### Return type

void (empty response body)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **requestsPost**
> requestsPost($requestsPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/RequestsApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\RequestsApiInterface;

class RequestsApi implements RequestsApiInterface
{

    // ...

    /**
     * Implementation of RequestsApiInterface#requestsPost
     */
    public function requestsPost(RequestsPostRequest $requestsPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **requestsPostRequest** | [**OpenAPI\Server\Model\RequestsPostRequest**](../Model/RequestsPostRequest.md)|  |

### Return type

void (empty response body)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

