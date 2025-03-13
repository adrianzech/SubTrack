<?php

declare(strict_types=1);

namespace App\Entity;

enum BillingCycleEnum: string
{
    case DAILY = 'Daily';
    case WEEKLY = 'Weekly';
    case MONTHLY = 'Monthly';
    case YEARLY = 'Yearly';
}
