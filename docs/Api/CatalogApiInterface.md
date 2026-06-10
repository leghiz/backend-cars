# OpenAPI\Server\Api\CatalogApiInterface

All URIs are relative to *http://localhost*

Method | HTTP request | Description
------------- | ------------- | -------------
[**catalogFiltersGet**](CatalogApiInterface.md#catalogFiltersGet) | **GET** /catalog/filters | 
[**catalogGet**](CatalogApiInterface.md#catalogGet) | **GET** /catalog | 
[**catalogIdDelete**](CatalogApiInterface.md#catalogIdDelete) | **DELETE** /catalog/{id} | 
[**catalogIdGet**](CatalogApiInterface.md#catalogIdGet) | **GET** /catalog/{id} | 
[**catalogIdPost**](CatalogApiInterface.md#catalogIdPost) | **POST** /catalog/{id} | 
[**catalogPost**](CatalogApiInterface.md#catalogPost) | **POST** /catalog | 


## Service Declaration
```yaml
# config/services.yaml
services:
    # ...
    Acme\MyBundle\Api\CatalogApi:
        tags:
            - { name: "open_api_server.api", api: "catalog" }
    # ...
```

## **catalogFiltersGet**
> OpenAPI\Server\Model\FilterOptions catalogFiltersGet()



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/CatalogApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\CatalogApiInterface;

class CatalogApi implements CatalogApiInterface
{

    // ...

    /**
     * Implementation of CatalogApiInterface#catalogFiltersGet
     */
    public function catalogFiltersGet(int &$responseCode, array &$responseHeaders): array|object|null
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters
This endpoint does not need any parameter.

### Return type

[**OpenAPI\Server\Model\FilterOptions**](../Model/FilterOptions.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **catalogGet**
> OpenAPI\Server\Model\LotListItem catalogGet($page, $limit, $search, $manufacturerId, $modelId, $colorId, $transmission, $drive, $year, $priceFrom, $priceTo, $mileageFrom, $mileageTo, $engineVolumeId, $isSold)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/CatalogApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\CatalogApiInterface;

class CatalogApi implements CatalogApiInterface
{

    // ...

    /**
     * Implementation of CatalogApiInterface#catalogGet
     */
    public function catalogGet(int $page, int $limit, ?string $search, ?int $manufacturerId, ?int $modelId, ?int $colorId, ?string $transmission, ?string $drive, ?int $year, ?float $priceFrom, ?float $priceTo, ?int $mileageFrom, ?int $mileageTo, ?int $engineVolumeId, ?bool $isSold, int &$responseCode, array &$responseHeaders): array|object|null
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
 **search** | **string**|  | [optional]
 **manufacturerId** | **int**|  | [optional]
 **modelId** | **int**|  | [optional]
 **colorId** | **int**|  | [optional]
 **transmission** | **string**|  | [optional]
 **drive** | **string**|  | [optional]
 **year** | **int**|  | [optional]
 **priceFrom** | **float**|  | [optional]
 **priceTo** | **float**|  | [optional]
 **mileageFrom** | **int**|  | [optional]
 **mileageTo** | **int**|  | [optional]
 **engineVolumeId** | **int**|  | [optional]
 **isSold** | **bool**|  | [optional]

### Return type

[**OpenAPI\Server\Model\LotListItem**](../Model/LotListItem.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **catalogIdDelete**
> catalogIdDelete($id)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/CatalogApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\CatalogApiInterface;

class CatalogApi implements CatalogApiInterface
{

    // ...

    /**
     * Implementation of CatalogApiInterface#catalogIdDelete
     */
    public function catalogIdDelete(int $id, int &$responseCode, array &$responseHeaders): void
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

## **catalogIdGet**
> OpenAPI\Server\Model\LotDetail catalogIdGet($id)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/CatalogApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\CatalogApiInterface;

class CatalogApi implements CatalogApiInterface
{

    // ...

    /**
     * Implementation of CatalogApiInterface#catalogIdGet
     */
    public function catalogIdGet(int $id, int &$responseCode, array &$responseHeaders): array|object|null
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

[**OpenAPI\Server\Model\LotDetail**](../Model/LotDetail.md)

### Authorization

No authorization required

### HTTP request headers

 - **Content-Type**: Not defined
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **catalogIdPost**
> OpenAPI\Server\Model\LotDetail catalogIdPost($id, $manufacturer, $model, $year, $price, $mileage, $engineVolume, $color, $transmission, $drive, $bodyNumber, $isSold, $soldData, $deletedImages, $newImages)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/CatalogApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\CatalogApiInterface;

class CatalogApi implements CatalogApiInterface
{

    // ...

    /**
     * Implementation of CatalogApiInterface#catalogIdPost
     */
    public function catalogIdPost(int $id, ?string $manufacturer, ?string $model, ?int $year, ?float $price, ?int $mileage, ?float $engineVolume, ?string $color, ?string $transmission, ?string $drive, ?string $bodyNumber, bool $isSold, ?\DateTime $soldData, ?array $deletedImages, ?array $newImages, int &$responseCode, array &$responseHeaders): array|object|null
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
 **manufacturer** | **string**|  | [optional]
 **model** | **string**|  | [optional]
 **year** | **int**|  | [optional]
 **price** | **float**|  | [optional]
 **mileage** | **int**|  | [optional]
 **engineVolume** | **float**|  | [optional]
 **color** | **string**|  | [optional]
 **transmission** | **string**|  | [optional]
 **drive** | **string**|  | [optional]
 **bodyNumber** | **string**|  | [optional]
 **isSold** | **bool**|  | [optional] [default to false]
 **soldData** | **\DateTime**|  | [optional]
 **deletedImages** | [**string**](../Model/string.md)|  | [optional]
 **newImages** | **UploadedFile**|  | [optional]

### Return type

[**OpenAPI\Server\Model\LotDetail**](../Model/LotDetail.md)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: multipart/form-data
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

## **catalogPost**
> OpenAPI\Server\Model\LotDetail catalogPost($manufacturer, $model, $year, $price, $mileage, $engineVolume, $color, $transmission, $drive, $bodyNumber, $isSold, $soldData, $images)



### Example Implementation
```php
<?php
// src/Acme/MyBundle/Api/CatalogApiInterface.php

namespace Acme\MyBundle\Api;

use OpenAPI\Server\Api\CatalogApiInterface;

class CatalogApi implements CatalogApiInterface
{

    // ...

    /**
     * Implementation of CatalogApiInterface#catalogPost
     */
    public function catalogPost(?string $manufacturer, ?string $model, ?int $year, ?float $price, ?int $mileage, ?float $engineVolume, ?string $color, ?string $transmission, ?string $drive, ?string $bodyNumber, bool $isSold, ?\DateTime $soldData, ?array $images, int &$responseCode, array &$responseHeaders): array|object|null
    {
        // Implement the operation ...
    }

    // ...
}
```

### Parameters

Name | Type | Description  | Notes
------------- | ------------- | ------------- | -------------
 **manufacturer** | **string**|  | [optional]
 **model** | **string**|  | [optional]
 **year** | **int**|  | [optional]
 **price** | **float**|  | [optional]
 **mileage** | **int**|  | [optional]
 **engineVolume** | **float**|  | [optional]
 **color** | **string**|  | [optional]
 **transmission** | **string**|  | [optional]
 **drive** | **string**|  | [optional]
 **bodyNumber** | **string**|  | [optional]
 **isSold** | **bool**|  | [optional] [default to false]
 **soldData** | **\DateTime**|  | [optional]
 **images** | **UploadedFile**|  | [optional]

### Return type

[**OpenAPI\Server\Model\LotDetail**](../Model/LotDetail.md)

### Authorization

[bearerAuth](../../README.md#bearerAuth)

### HTTP request headers

 - **Content-Type**: multipart/form-data
 - **Accept**: application/json

[[Back to top]](#) [[Back to API list]](../../README.md#documentation-for-api-endpoints) [[Back to Model list]](../../README.md#documentation-for-models) [[Back to README]](../../README.md)

