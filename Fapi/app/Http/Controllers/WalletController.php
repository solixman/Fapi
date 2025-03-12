<?php

namespace App\Http\Controllers;


use App\Models\Transaction;
use App\Models\User as ModelsUser;
use Exception;
use Illuminate\Foundation\Auth\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class WalletController extends Controller
{
    

    

    public function transact(Request $request)
    {
        try {
            $sender=ModelsUser::find($request['id']);
        $S_wallet = $sender->wallet;
        $S_wallet->balance=400000;
        // $S_wallet->save();
        //         return [$request['']];
        //  die;
        try {
            $receiver = ModelsUser::where('email', $request['email'] )->first();
            $R_wallet = $receiver->wallet;
        } catch (Exception $e) {
            return response()->json('error', $e);
        }

        // verifiying name  
        // return $receiver->name;
        if ($receiver->name != $request['name']) {
            return response()->json('the given name is wrong');
        } else {
            if ($request['amount'] < $S_wallet->balance) {
               
                try {
                    DB::beginTransaction();
                    $S_wallet->balance = $S_wallet->balance - $request['amount'];
                    $R_wallet->balance = $R_wallet->balance + $request['amount'];
                
                    $S_wallet->save();
                    $R_wallet->save();
                    $Transaction = new Transaction();
                    $Transaction->recieverEmail=$receiver->email;
                    $Transaction->senderEmail=$sender->email;
                    $Transaction->date=now();
                    $Transaction->status = "done";
                    $Transaction->amount=$request['amount'];
                    $Transaction->motif = $request['motif'];
                    // return $Transaction;
                    try {
                        $Transaction->save();
                    } catch(Exception $e) {
                        return ["rrr" => $e->getMessage()];
                    }
                    DB::commit();
                } catch (Exception $e) {
                    DB::rollBack();
                    return response()->json('error', $e);
                }
            }else{
                return response()->json('sorry you dont have this amount');
            }
           
            return [$Transaction];
            
        }
        } catch(Exception $e) {
            return ["error" => $e->getMessage()];
        }
    }

}
