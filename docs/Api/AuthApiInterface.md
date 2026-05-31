# OpenAPI\Server\Api\AuthApiInterface

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**authChangePasswordPost**](AuthApiInterface.md#authChangePasswordPost) | **POST** /auth/change-password | 
[**authForgotPasswordRequestPost**](AuthApiInterface.md#authForgotPasswordRequestPost) | **POST** /auth/forgot-password/request | 
[**authForgotPasswordResetPost**](AuthApiInterface.md#authForgotPasswordResetPost) | **POST** /auth/forgot-password/reset | 
[**authForgotPasswordVerifyPost**](AuthApiInterface.md#authForgotPasswordVerifyPost) | **POST** /auth/forgot-password/verify | 
[**authLoginPost**](AuthApiInterface.md#authLoginPost) | **POST** /auth/login | 
[**authRegisterPost**](AuthApiInterface.md#authRegisterPost) | **POST** /auth/register | 
[**authVerifyPost**](AuthApiInterface.md#authVerifyPost) | **POST** /auth/verify | 


## Service Declaration
```yaml
# config/services.yaml
services:
    # ...
    Acme\MyBundle\Api\AuthApi:
        tags:
            - { name: "open_api_server.api", api: "auth" }
    # ...
```

## **authChangePasswordPost**
> authChangePasswordPost($authChangePasswordPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AuthApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AuthApiInterface;

class AuthApi implements AuthApiInterface
{

    // ...

    /**
     * Implementation of AuthApiInterface#authChangePasswordPost
     */
    public function authChangePasswordPost(AuthChangePasswordPostRequest $authChangePasswordPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **authChangePasswordPostRequest** | [**OpenAPI\Server\Model\AuthChangePasswordPostRequest**](../Model/AuthChangePasswordPostRequest.md)|  |

### Return type

void (empty response body)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **authForgotPasswordRequestPost**
> authForgotPasswordRequestPost($authForgotPasswordRequestPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AuthApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AuthApiInterface;

class AuthApi implements AuthApiInterface
{

    // ...

    /**
     * Implementation of AuthApiInterface#authForgotPasswordRequestPost
     */
    public function authForgotPasswordRequestPost(AuthForgotPasswordRequestPostRequest $authForgotPasswordRequestPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **authForgotPasswordRequestPostRequest** | [**OpenAPI\Server\Model\AuthForgotPasswordRequestPostRequest**](../Model/AuthForgotPasswordRequestPostRequest.md)|  |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **authForgotPasswordResetPost**
> authForgotPasswordResetPost($authForgotPasswordResetPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AuthApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AuthApiInterface;

class AuthApi implements AuthApiInterface
{

    // ...

    /**
     * Implementation of AuthApiInterface#authForgotPasswordResetPost
     */
    public function authForgotPasswordResetPost(AuthForgotPasswordResetPostRequest $authForgotPasswordResetPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **authForgotPasswordResetPostRequest** | [**OpenAPI\Server\Model\AuthForgotPasswordResetPostRequest**](../Model/AuthForgotPasswordResetPostRequest.md)|  |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **authForgotPasswordVerifyPost**
> OpenAPI\Server\Model\AuthForgotPasswordVerifyPost200Response authForgotPasswordVerifyPost($authForgotPasswordVerifyPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AuthApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AuthApiInterface;

class AuthApi implements AuthApiInterface
{

    // ...

    /**
     * Implementation of AuthApiInterface#authForgotPasswordVerifyPost
     */
    public function authForgotPasswordVerifyPost(AuthForgotPasswordVerifyPostRequest $authForgotPasswordVerifyPostRequest, int &$responseCode, array &$responseHeaders): array|object|null
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **authForgotPasswordVerifyPostRequest** | [**OpenAPI\Server\Model\AuthForgotPasswordVerifyPostRequest**](../Model/AuthForgotPasswordVerifyPostRequest.md)|  |

### Return type

[**OpenAPI\Server\Model\AuthForgotPasswordVerifyPost200Response**](../Model/AuthForgotPasswordVerifyPost200Response.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **authLoginPost**
> OpenAPI\Server\Model\AuthLoginPost200Response authLoginPost($authLoginPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AuthApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AuthApiInterface;

class AuthApi implements AuthApiInterface
{

    // ...

    /**
     * Implementation of AuthApiInterface#authLoginPost
     */
    public function authLoginPost(AuthLoginPostRequest $authLoginPostRequest, int &$responseCode, array &$responseHeaders): array|object|null
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **authLoginPostRequest** | [**OpenAPI\Server\Model\AuthLoginPostRequest**](../Model/AuthLoginPostRequest.md)|  |

### Return type

[**OpenAPI\Server\Model\AuthLoginPost200Response**](../Model/AuthLoginPost200Response.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **authRegisterPost**
> authRegisterPost($authRegisterPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AuthApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AuthApiInterface;

class AuthApi implements AuthApiInterface
{

    // ...

    /**
     * Implementation of AuthApiInterface#authRegisterPost
     */
    public function authRegisterPost(AuthRegisterPostRequest $authRegisterPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **authRegisterPostRequest** | [**OpenAPI\Server\Model\AuthRegisterPostRequest**](../Model/AuthRegisterPostRequest.md)|  |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **authVerifyPost**
> authVerifyPost($authVerifyPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/AuthApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\AuthApiInterface;

class AuthApi implements AuthApiInterface
{

    // ...

    /**
     * Implementation of AuthApiInterface#authVerifyPost
     */
    public function authVerifyPost(AuthVerifyPostRequest $authVerifyPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **authVerifyPostRequest** | [**OpenAPI\Server\Model\AuthVerifyPostRequest**](../Model/AuthVerifyPostRequest.md)|  |

### Return type

void (empty response body)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

