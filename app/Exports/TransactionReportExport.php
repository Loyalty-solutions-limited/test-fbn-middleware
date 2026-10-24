<?php

namespace App\Exports;

use App\Models\Transaction;
use Maatwebsite\Excel\Concerns\FromCollection;

class TransactionReportExport implements FromCollection
{
    /**
    * @return \Illuminate\Support\Collection
    */
    
    public function __construct($log_id){
        $this->log_id = $log_id;
    }
    
    public function collection(): \Illuminate\Support\Enumerable
    {
        return Transaction::where('transaction_log_id', $this->log_id)->get();
    }
}