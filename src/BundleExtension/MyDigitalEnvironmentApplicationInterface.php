<?php

namespace MyDigitalEnvironment\MyDigitalEnvironmentBundle\BundleExtension;

interface MyDigitalEnvironmentApplicationInterface
{
    public static function getApplicationRouteId(): string;
    public static function getApplicationDescription(): string;
}