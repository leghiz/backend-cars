# OpenAPI\Server\Api\AdminApiInterface

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**adminUsersGet**](AdminApiInterface.md#adminUsersGet) | **GET** /admin/users | 
[**adminUsersIdDelete**](AdminApiInterface.md#adminUsersIdDelete) | **DELETE** /admin/users/{id} | 
[**adminUsersIdGet**](AdminApiInterface.md#adminUsersIdGet) | **GET** /admin/users/{id} | 


## Service Declaration
```yaml
# config/services.yaml
services:
    # ...
    Acme\MyBundle\Api\AdminApi:
        tags:
            - { name: "open_api_server.api", api: "admin" }
    # ...
```

## **adminUsersGet**
> OpenAPI\Server\Model\UserListItem adminUsersGet($page, $limit)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AdminApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AdminApiInterface;

class AdminApi implements AdminApiInterface
{

    // ...

    /**
     * Implementation of AdminApiInterface#adminUsersGet
     */
    public function adminUsersGet(int $page, int $limit, int &$responseCode, array &$responseHeaders): array|object|null
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **page** | **int**|  | [optional] [default to 1]
 **limit** | **int**|  | [optional] [default to 10]

### Return type

[**OpenAPI\Server\Model\UserListItem**](../Model/UserListItem.md)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **adminUsersIdDelete**
> adminUsersIdDelete($id)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AdminApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AdminApiInterface;

class AdminApi implements AdminApiInterface
{

    // ...

    /**
     * Implementation of AdminApiInterface#adminUsersIdDelete
     */
    public function adminUsersIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
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

## **adminUsersIdGet**
> OpenAPI\Server\Model\ProfileResponse adminUsersIdGet($id)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AdminApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AdminApiInterface;

class AdminApi implements AdminApiInterface
{

    // ...

    /**
     * Implementation of AdminApiInterface#adminUsersIdGet
     */
    public function adminUsersIdGet(int $id, int &$responseCode, array &$responseHeaders): array|object|null
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

[**OpenAPI\Server\Model\ProfileResponse**](../Model/ProfileResponse.md)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

