# OpenAPI\Server\Api\ReviewsApiInterface

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**reviewsGet**](ReviewsApiInterface.md#reviewsGet) | **GET** /reviews | 
[**reviewsPost**](ReviewsApiInterface.md#reviewsPost) | **POST** /reviews | 


## Service Declaration
```yaml
# config/services.yaml
services:
    # ...
    Acme\MyBundle\Api\ReviewsApi:
        tags:
            - { name: "open_api_server.api", api: "reviews" }
    # ...
```

## **reviewsGet**
> OpenAPI\Server\Model\Review reviewsGet($page, $limit, $dateOrder, $ratingOrder)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/ReviewsApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\ReviewsApiInterface;

class ReviewsApi implements ReviewsApiInterface
{

    // ...

    /**
     * Implementation of ReviewsApiInterface#reviewsGet
     */
    public function reviewsGet(int $page, int $limit, string $dateOrder, string $ratingOrder, int &$responseCode, array &$responseHeaders): array|object|null
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
 **dateOrder** | **string**|  | [optional] [default to &#39;desc&#39;]
 **ratingOrder** | **string**|  | [optional] [default to &#39;desc&#39;]

### Return type

[**OpenAPI\Server\Model\Review**](../Model/Review.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **reviewsPost**
> reviewsPost($reviewsPostRequest)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/ReviewsApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\ReviewsApiInterface;

class ReviewsApi implements ReviewsApiInterface
{

    // ...

    /**
     * Implementation of ReviewsApiInterface#reviewsPost
     */
    public function reviewsPost(ReviewsPostRequest $reviewsPostRequest, int &$responseCode, array &$responseHeaders): void
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **reviewsPostRequest** | [**OpenAPI\Server\Model\ReviewsPostRequest**](../Model/ReviewsPostRequest.md)|  |

### Return type

void (empty response body)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: application/json
 - **Accept**: Not defined

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

