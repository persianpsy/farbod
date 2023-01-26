<?php

namespace App\Exports;

use App\Models\Payment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\Exportable;
use Hekmatinasser\Verta\Verta;
use App\Models\Reservation;

class PaymentExport implements FromCollection, WithMapping, WithHeadings
{
    
       use Exportable;    

    public function collection()
    {
        $users =  Payment::with('user')->orderBy('created_at', 'DESC')->get();
        
        return $users;
    }
      public function query()
    { 
        $shop =  Payment::with('user');
        ;
      
        
        return  $shop;
    }
    
        public function headings(): array
    {
        return[
            'ID',
            'Gateway',
            'Ref',
            'Price',
            'Status',
            'Cellphone',
            'Time',
            'Therapist',
            'commission'
          
        ];
    }
    
    public function map($data): array
    {
        $info = [];
        $commision = []; 
     $reservation = Reservation::with('appointment.staff.user')->where('payment_id',$data->id)->first();
     if($reservation){
         $info = $reservation->staff->user->en_full_name ;
         $commision = $reservation->staff->commission; 
     }
    
        return [
            $data->id,
            $data->gateway,
            $data->ref_id,
            $data->price,
            $data->status,
            $data->user->cellphone,
            \verta( $data->created_at),
            $info,
            $commision
        ];
    } 

}
