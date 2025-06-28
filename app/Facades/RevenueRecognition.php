<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array recognizeRevenueForTransaction(\App\Models\Transaction $transaction, ?\Carbon\Carbon $recognitionDate = null)
 * @method static array recognizeGiftCardRedemption(\App\Models\TransactionLineItem $redemptionLineItem, \App\Models\Transaction $redemptionTransaction)
 * @method static \Illuminate\Support\Collection getRecognizedRevenue(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate, array $filters = [])
 * @method static \Illuminate\Support\Collection getDeferredRevenue(\Carbon\Carbon $asOfDate, array $filters = [])
 * @method static array processRevenueRecognition(?\Carbon\Carbon $asOfDate = null)
 * 
 * @see \App\Services\RevenueRecognitionService
 */
class RevenueRecognition extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'revenue-recognition';
    }
}
