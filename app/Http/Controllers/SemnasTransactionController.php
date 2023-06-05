<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\SemnasParticipant;
use App\Models\SemnasTransaction;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\VarDumper\Dumper\esc;
use App\Http\Requests\Storesemnas_transactionRequest;
use App\Http\Requests\Updatesemnas_transactionRequest;
use App\Models\SemnasReferralCode;
use Illuminate\Http\Request;

class SemnasTransactionController extends Controller
{

    static private $event_period;

    static private $ticketPrice = [
        "summit" => [
            "NORMAL" => 300000,
            "EB" => 250000,
            "PS1" => 270000,
            "PS2" => 280000
        ],
        "talk-1" => [
            "NORMAL" => 250000,
        ],
        "talk-2" => [
            "NORMAL" => 180000,
        ],
    ];

    static public $timeRegist = [
        "summit" => [
            "EB" => [
                "open" => '2023-06-05 10:00:00',
                "closed" => '2023-06-05 11:05:59',
            ],
            "PS1" => [
                "open" => '2023-06-05 11:06:00',
                "closed" => '2023-06-05 11:10:59',
            ],
            "PS2" => [
                "open" => '2023-06-05 11:11:00',
                "closed" => '2023-06-05 11:15:59',
            ],
            "NORMAL" => [
                "open" => '2023-06-05 11:16:00',
                "closed" => '2023-06-05 13:30:00',
            ],
        ],
        "talk-1" => [
            "NORMAL" => [
                "open" => '2023-06-05 09:23:00',
                "closed" => '2023-06-05 09:30:00',
            ],
        ],
        "talk-2" => [
            "NORMAL" => [
                "open" => '2023-06-05 09:23:00',
                "closed" => '2023-06-05 09:30:00',
            ],
        ],
    ];
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    static private function getTicketPrice()
    {
        $currentDateTime = Carbon::now();
        if (session('event') == 'summit') {
            if ($currentDateTime->between(
                Carbon::parse(SemnasTransactionController::$timeRegist[session('event')]['EB']['open']),
                Carbon::parse(SemnasTransactionController::$timeRegist[session('event')]['EB']['closed'])
            )) {
                SemnasTransactionController::$event_period = "EB";
                return SemnasTransactionController::$ticketPrice[session('event')]["EB"];
            } elseif ($currentDateTime->between(
                Carbon::parse(SemnasTransactionController::$timeRegist[session('event')]['PS1']['open']),
                Carbon::parse(SemnasTransactionController::$timeRegist[session('event')]['PS1']['closed'])
            )) {
                SemnasTransactionController::$event_period = "PS1";
                return SemnasTransactionController::$ticketPrice[session('event')]["PS1"];
            } elseif ($currentDateTime->between(
                Carbon::parse(SemnasTransactionController::$timeRegist[session('event')]['PS2']['open']),
                Carbon::parse(SemnasTransactionController::$timeRegist[session('event')]['PS2']['closed'])
            )) {
                SemnasTransactionController::$event_period = "PS2";
                return SemnasTransactionController::$ticketPrice[session('event')]["PS2"];
            }
        }

        SemnasTransactionController::$event_period = "NORMAL";
        return SemnasTransactionController::$ticketPrice[session('event')]["NORMAL"];
    }

    static private function getDiscount($idCoupon)
    {
        $discountPercent = SemnasReferralCode::where('id', $idCoupon)->first()->diskon_persen / 100;
        return $discountPercent;
    }

    /**
     * Store a newly created resource in storage.
     */
    public function save(Request $request)
    {
        //

        $idPeserta = session('id_peserta');
        $existPeserta = SemnasParticipant::where('id', $idPeserta)->first();
        $event = session('event');

        if (!$existPeserta) {
            session()->forget(['id_peserta', 'event']);
            switch ($event) {
                case 'summit':
                    return redirect()->route('national-seminar.form-summit')->with("not_success", true);
                    break;
                case 'talk-1':
                    return redirect()->route('national-seminar.form-et1')->with("not_success", true);
                    break;
                case 'talk-2':
                    return redirect()->route('national-seminar.form-et2')->with("not_success", true);
                    break;
                default:
                    return false;
                    break;
            }
        }

        $rules = [
            'account_name' => 'required|string|max:100',
            'account_number' => 'required|string|max:100',
            'bank_name' => 'required',
            'payment_slip' => 'required|file|max:2048|mimes:jpg,png',
        ];

        $validateData = $request->validate($rules);

        if (!$validateData) {
            return false;
        }

        $idCoupon = $existPeserta->id_referral_code;
        $totalPrice = SemnasTransactionController::getTicketPrice();
        if ($idCoupon) {
            $totalPrice = SemnasTransactionController::getTicketPrice() * (1 - SemnasTransactionController::getDiscount($idCoupon));
        }

        $filterData = [
            'account_name' => esc(request('account_name')),
            'account_number' => esc(request('account_number')),
            'amount' => esc($totalPrice),
            'bank_name' => esc(request('bank_name')),
            'status_periode' => esc(SemnasTransactionController::$event_period),
            'status_bayar' => "PAID",
        ];


        if ($validateData['payment_slip'] != null) {
            Storage::disk('semnas_payment_slip')->put('', $validateData['payment_slip']);
            $filterData['bukti_bayar'] = Storage::disk('semnas_payment_slip')->put('', $validateData['payment_slip']);
        }

        $createdTrx = SemnasTransaction::where('id_peserta', $idPeserta)->update($filterData);
        if ($createdTrx) {
            session()->forget(['id_peserta', 'event']);
            return Inertia::render('Semnas/PaymentConfirmation', ['modal' => true]);
        }
    }


    public function transaction()
    {
        // SemnasTransactionController::getDiscount(1);
        // dd(session()->get('id_peserta'));
        $data['name'] =  SemnasParticipant::where('id', session('id_peserta'))->first()->full_name;
        // $data['name'] =  "Ucup";

        $idCoupon = SemnasParticipant::where('id', session('id_peserta'))->first()->id_referral_code;
        // dd(session('id_peserta'));
        // $idCoupon = 3;
        // $data['total'] = session('ticketPrice');
        $data['total'] = SemnasTransactionController::getTicketPrice();
        // $data['total'] = 10000;
        if ($idCoupon) {
            $data['total'] = SemnasTransactionController::getTicketPrice() * (1 - SemnasTransactionController::getDiscount($idCoupon));
            // $data['total'] = session('ticketPrice') - 10000;
        }
        return Inertia::render('Semnas/PaymentConfirmation', $data);
    }

    /**
     * Display the specified resource.
     */
    public function show(SemnasTransaction $semnas_transaction)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(SemnasTransaction $semnas_transaction)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Updatesemnas_transactionRequest $request, SemnasTransaction $semnas_transaction)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(SemnasTransaction $semnas_transaction)
    {
        //
    }
}