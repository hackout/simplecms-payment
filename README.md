# 支付模块

一个基于国家地理的地理信息表

## 环境配置要求

1. PHP 8.0+
2. SimpleCMS/Framework

## 安装

```bash
composer require simplecms/payment
```

## 使用方法

```php
use SimpleCMS\Region\Facades\Region; 
//获取所有城市
return Region::getAll();
//通过代码查询城市
return Region::findRegion(string $code = '行政标识');
//查询所有下级
return Region::getAllChildren(string $code = '行政标识');
//带深度查询
return Region::getChildren(string $code, int $deep = 0);
//坐标计算距离
distance($lat1,$lng1,$lat2,$lng2);
//SimpleCMS service
$service->distance($lat1,$lng1,$maxDistance,$latKey,$lngKey);
```

### Casts

```php
use SimpleCMS\Region\Casts\Point; 
use SimpleCMS\Region\Casts\LineString; 
use SimpleCMS\Region\Casts\Polygon; 

public $casts = [
    'geo' => Point::class,
    'geo' => LineString::class,
    'geo' => Polygon::class
];
```

## SimpleCMS

请先加载simplecms/framework

### 服务调用方法

```php
use SimpleService;

//获取距离
$service->selectDistance(float $lat = 23.23211, float $lng = 111.23123,string $column = 'location');
//通过记录查询
$service->queryDistance(float $lat = 23.23211, float $lng = 111.23123, float $maxDistance = 50,string $column = 'location')
```

## 数据结构

数据结构参考遵循以下格式:

```bash
{
    "name": "名称",
    "short": "缩写/简称/短名",
    "code": "唯一地理标识",
    "area": "电话区号",
    "zip": "邮政编码",
    "lng": 100.00000, #经度
    "lat": 32.00000, #纬度
    "children": [
        ....#跟上面结构一样
    ]
}
```

## Facades

```php
use SimpleCMS\Payment\Facades\Payment; #支付 
```

## 其他说明

更多操作参考IDE提示
