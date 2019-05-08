<?php
namespace Bullhorn\FastRest\Api\Services;
use Bullhorn\FastRestServices\SplEnum as ParentSplEnum;
//Fix for default SplEnum not being available in windows
abstract class SplEnum extends ParentSplEnum {
}