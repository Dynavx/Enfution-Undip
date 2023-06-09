<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use Illuminate\Http\Request;
use App\Models\SemnasParticipant;
use App\Models\SemnasTransaction;
use App\Models\SemnasReferralCode;
use App\Policies\SemnasParticipantPolicy;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use function Symfony\Component\VarDumper\Dumper\esc;

class SemnasParticipantController extends Controller
{

    static private $view;
    static private $event;

    public function __construct()
    {
        SemnasParticipantController::segmentURL();
    }

    public function index()
    {
        $currentDateTime = Carbon::now();
        $time_regist = "";

        if ($currentDateTime->between(
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['EB']['open']),
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['EB']['closed'])
        )) {
            $time_regist = "EB";
        } elseif ($currentDateTime->between(
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['PS1']['open']),
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['PS1']['closed'])
        )) {
            $time_regist = "PS1";
        } elseif ($currentDateTime->between(
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['PS2']['open']),
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['PS2']['closed'])
        )) {
            $time_regist = "PS2";
        } elseif ($currentDateTime->between(
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['NORMAL']['open']),
            Carbon::parse(SemnasTransactionController::$timeRegist["summit"]['NORMAL']['closed'])
        )) {
            $time_regist = "NORMAL";
        }


        $data = [
            "ticketPrice" => SemnasTransactionController::$ticketPrice["summit"][$time_regist] ?? 0,
            "timeRegist" => $time_regist,
        ];
        return Inertia::render('NationalSeminar', $data);
    }


    static private function segmentURL()
    {
        $urlForm = request()->segment(1);
        switch ($urlForm) {
            case 'registration-national-seminar':
                SemnasParticipantController::$view = 'FormSemnas';
                SemnasParticipantController::$event = 'summit';
                break;
            case 'registration-EarlyTalk1':
                SemnasParticipantController::$view = 'FormET1';
                SemnasParticipantController::$event = 'talk-1';
                break;
            case 'registration-EarlyTalk2':
                SemnasParticipantController::$view = 'FormET2';
                SemnasParticipantController::$event = 'talk-2';
                break;
            default:
                return false;
                break;
        }
    }

    public function create()
    {
        $data = [];
        $currentDateTime = Carbon::now();
        if (SemnasParticipantController::$event == "summit") {
            if ($currentDateTime->between(
                Carbon::parse(SemnasTransactionController::$timeRegist[SemnasParticipantController::$event]['EB']['open']),
                Carbon::parse(SemnasTransactionController::$timeRegist[SemnasParticipantController::$event]['EB']['closed'])
            )) {
                $data['time_regist'] = "Early Bird";
            } elseif ($currentDateTime->between(
                Carbon::parse(SemnasTransactionController::$timeRegist[SemnasParticipantController::$event]['PS1']['open']),
                Carbon::parse(SemnasTransactionController::$timeRegist[SemnasParticipantController::$event]['PS1']['closed'])
            )) {
                $data['time_regist'] = "Presale 1";
            } elseif ($currentDateTime->between(
                Carbon::parse(SemnasTransactionController::$timeRegist[SemnasParticipantController::$event]['PS2']['open']),
                Carbon::parse(SemnasTransactionController::$timeRegist[SemnasParticipantController::$event]['PS2']['closed'])
            )) {
                $data['time_regist'] = "Presale 2";
            } else {
                $data['time_regist'] = "Normal";
            }
        } else {
            $data['time_regist'] = "Normal";
        }

        if (session()->has('not_success')) {
            $data['info'] = session('not_success');
        }

        if (session()->has('email_not_valid')) {
            $data['email_not_valid'] = session('email_not_valid');
        }
        return Inertia::render("Semnas/" . SemnasParticipantController::$view, $data);
    }

    public function store(Request $request)
    {
        $rules = [
            'full_name' => 'required|string|max:255',
            'faculty_departements_batch' => 'nullable|string|max:100',
            'gender' => 'required',
            'place_dob' => 'required|string|max:100',
            'status' => 'required',
            'university' => 'nullable|string|max:150',
            'phone_number' => 'required|string|max:16',
            'line_id' => 'required|string|max:20',
            'email' => 'required|max:200|email:rfc,dns',
            'ktm' => 'nullable|file|max:2048|mimes:jpg,png',
        ];

        if (request('status') != "Non-Student" && request('status')) {
            $rules['faculty_departements_batch'] = 'required|string|max:100';
            $rules['university'] = 'required|string|max:150';
        }

        $validateData = $request->validate($rules);

        if (!$validateData) {
            return false;
        }

        if (request('email')) {
            $getDomain = explode('@', request('email'));
            $lastDomain = end($getDomain);
            if ($lastDomain != "gmail.com") {
                session()->flash("email_not_valid", "Please enter a valid Gmail address.");
                switch (session('event')) {
                    case 'summit':
                        return redirect()->route('national-seminar.form-summit');
                        break;
                    case 'talk-1':
                        return redirect()->route('national-seminar.form-et1');
                        break;
                    case 'talk-2':
                        return redirect()->route('national-seminar.form-et2');
                        break;
                    default:
                        return false;
                        break;
                }
            }
        }

        $filterData = [
            'full_name' => esc(request('full_name')),
            'gender' => esc(request('gender')),
            'place_dob' => esc(request('place_dob')),
            'status' => esc(request('status')),
            'phone_number' => esc(request('phone_number')),
            'line_id' => esc(request('line_id')),
            'email' => esc(request('email')),
            'event' => SemnasParticipantController::$event,
        ];

        if (request('faculty_departements_batch')) {
            $filterData['faculty_departements_batch'] = esc(request('faculty_departements_batch'));
        }

        if (request('university')) {
            $filterData['university'] = esc(request('university'));
        }

        if ($validateData['ktm'] != null) {
            $path = 'uploads/semnas_ktm';
            $extension = $validateData['ktm']->getClientOriginalExtension();
            $filename = uniqid() . '.' . $extension;
            $validateData['ktm']->move($path, $filename);
            $filterData['ktm'] = $filename;
        }

        // Get id coupun if any
        if (request('coupon')) {
            $couponExists = SemnasReferralCode::where('code', request('coupon'))->first();
            $couponQty = (int) $couponExists->qty;
            if ($couponExists && $couponQty > 0) {
                $filterData['id_referral_code'] = $couponExists->id;
                $couponQty--;
                $couponExists->update(['qty' => $couponQty]);
            }
        }

        SemnasParticipant::create($filterData);

        $tempTrx = [
            "id_peserta" => SemnasParticipant::all()->sortByDesc('created_at')->where('full_name', $filterData['full_name'])->first()->id,
        ];
        SemnasTransaction::create($tempTrx);
        Session::put([
            'id_peserta' => $tempTrx['id_peserta'],
            'event' => SemnasParticipantController::$event,
        ]);
        return redirect()->route('national-seminar.payment-confirmation');
    }
}
