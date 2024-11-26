<?php

namespace App\Helper;

use Termwind\Components\Span;

class OrderStatus
{
    const PENDING = 1; //heleki bu sifaris sorgusu gozlemededi - yeni admin hele tesdiqlemiyib
    const CONFIRMED = 2; //admin derefden tesdiq edildi
    const SHIPPED = 3; //catdirilma merhelesi - curyere verilib
    const DELIVERED = 4; //musteriye catdirilib
    const CANCELED = 5; //sifaris legv edildi user terefden (confirmed edilmemis muddetde cansel ede bilerik, yeni status ne qeder ki 1-dirse cansel ede bilerik)
    const RETURNED = 6; //musteri sifarisi qaytardi
    const FAILED = 7; //bu sorgu redd edildi

    public function order_status_converter(int $status)
    {
        switch ($status) {
            case 1:
                return "<span class='badge bg-warning'>PENDING</span>";
                break;
            case 2:
                return '<span class="badge bg-success">CONFIRMED</span>';
                break;
            case 3:
                return '<span class="badge bg-primary">SHIPPED</span>';
                break;
            case 4:
                return '<span class="badge bg-success">DELIVERED</span>';
                break;
            case 5:
                return '<span class="badge bg-danger">CANCELED</span>';
                break;
            case 6:
                return '<span class="badge bg-secondary">RETURNED</span>';
                break;
            case 7:
                return '<span class="badge bg-danger">FAILED</span>';
                break;
            default:
                return "<span class='badge bg-secondary'>UNKNOWN</span>";
                break;
        }
    }
}
