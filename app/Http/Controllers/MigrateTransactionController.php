<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MigrateTransactionController extends Controller
{
    public static $key = '!QAZXSW@#EDCVFR$';
    public static $iv = '5666685225155700';
    public static $username = 'diamondcustomer';
    public static $password = 'ssw0rd20';

    public function migrate_transaction()
    {
        $success_count = 0;  $failure_count = 0;

	$payload = array();
    $pendingTransactions = DB::table('QUALIFIED_TRANSACTIONS')
                                ->where('status', 0)
                                ->limit(100)
                                ->get();
   
      if($pendingTransactions->count() > 0){
          foreach($pendingTransactions->unique('transaction_reference') as $pendingTransaction){
              $arrayToPush = array(
                'Company_username'=>self::$username,
                'Company_password'=>self::$password,
                // 'Membership_ID'=>$pendingTransaction->cif_id,
                'Membership_ID'=>$pendingTransaction->member_reference,
                'Acid' => $pendingTransaction->account_number,
                // 'Membership_ID'=>$membership_id_resolved ?? '8711130',
                'Transaction_Date'=>$pendingTransaction->transaction_date,
                'Transaction_Type_code'=>$pendingTransaction->transaction_type,
                'Transaction_channel_code'=>$pendingTransaction->channel,
                'Transaction_amount'=>$pendingTransaction->amount,
                'Branch_code'=>$pendingTransaction->branch_code,
                'Transaction_ID'=>$pendingTransaction->transaction_reference,
                'Product_Code' =>$pendingTransaction->product_code,
                'Product_Quantity' =>$pendingTransaction->quantity,
                'API_flag' => 'stran',
                'id'=>$pendingTransaction->id
                );

                DB::table('QUALIFIED_TRANSACTIONS')
                        ->where('id', '=', $pendingTransaction->id)
                        ->update(['status' => 1]);
				array_push($payload, $arrayToPush);
		  }

    try {

$resp =$this->pushToPERX("https://fbnperxlive-amfgcwc2d9g0e9av.francecentral-01.azurewebsites.net/staging/stage_data.php", $payload, "");
        print_r($resp);
        //dd($resp);
        // return response()->json($resp);
    } catch (\Exception $ex) {
        throw new \Exception("Something went wrong " . $ex->getMessage());
    }

        }else{

            return response()->json([
                "message" => "There are no staging data currently",
                "status" => false
            ]);
        }
    }
}