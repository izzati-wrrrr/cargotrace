<?php

namespace App\Enums;

/** Jenis event operasional. Event perubahan status memakai nilai ShipmentStatus. */
enum TrackingEventType: string
{
    case ArrivedAtPort = 'ARRIVED_AT_PORT';
    case DepartedPort = 'DEPARTED_PORT';
    case CustomsCleared = 'CUSTOMS_CLEARED';
    case ArrivedAtHub = 'ARRIVED_AT_HUB';
    case DepartedHub = 'DEPARTED_HUB';
    case OutForDelivery = 'OUT_FOR_DELIVERY';
    case LocationUpdate = 'LOCATION_UPDATE';
}