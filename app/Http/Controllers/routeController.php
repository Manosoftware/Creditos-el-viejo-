<?php

namespace App\Http\Controllers;

use App\db_credit;
use App\db_not_pay;
use App\db_summary;
use App\db_supervisor_has_agent;
use App\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class routeController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->id = Auth::user()->id;
            if (!db_supervisor_has_agent::where('id_user_agent', Auth::id())->exists()) {
                die('No existe relacion Usuario y Agente');
            }
            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $data = db_credit::where('credit.id_agent', Auth::id())
            ->join('users','credit.id_user','=','users.id')
            ->where('credit.status', 'inprogress')
            ->select('credit.*')
            ->orderBy('credit.order_list', 'asc')
            ->get();
        $data_filter = array();
        $dt = Carbon::now();

        foreach ($data as $k => $d) {


            $tmp_amount = db_summary::where('id_credit', $d->id)
            ->where('id_agent', Auth::id())
            ->sum('amount');
            $amount_total = ($d->amount_neto) + ($d->amount_neto * $d->utility);
            $tmp_quote = round(floatval(($amount_total / $d->payment_number)), 2);
            $tmp_rest = round(floatval($amount_total - $tmp_amount), 2);

            $d->positive = $tmp_amount;
            $d->payment_quote = $tmp_quote;
            $d->rest = round(floatval($amount_total - $tmp_amount), 2);            
            $d->payment_done = db_summary::where('id_credit', $d->id)->count();
            $d->user = User::find($d->id_user);
            $d->amount_total = $amount_total;
            $d->days_rest = $dt->diffInDays(Carbon::parse($d->created_at));
            $d->saldo = $d->amount_total - (db_summary::where('id_credit', $d->id)->sum('amount'));
            $d->quote = round((floatval($d->amount_neto * $d->utility) + floatval($d->amount_neto)) / floatval($d->payment_number),2);
            $d->setAttribute('last_pay', db_summary::where('id_credit', $d->id)->orderBy('id', 'desc')->first());

            if (!db_summary::where('id_credit', $d->id)->whereDate('created_at', '=', Carbon::now()->toDateString())->exists()) {
                if (!db_not_pay::whereDate('created_at', '=', Carbon::now()->toDateString())->where('id_credit', $d->id)->exists()) {
                    $data_filter[] = $d;
                }

            }


        }

        $data_all = array(
            'clients' => $data_filter
        );

        return view('route.index', $data_all);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, $id)
    {
        $id_credit = $request->id_credit;
        $direction = $request->direction;

        if (!isset($direction)) {
            return 'Direction';
        };
        if ($direction == 'up') {
            $direction = '<';
            $order = 'DESC';
        };
        if ($direction == 'down') {
            $direction = '>';
            $order = 'ASC';
        };


        $data = db_credit::where('id_agent', Auth::id())
            ->orderBy('order_list', $order)
            ->where('order_list', $direction, $id)
            ->where('status', 'inprogress')
            ->first();

        $no_pay = db_not_pay::whereDate('created_at', Carbon::now()->toDateString())
            ->where('id_credit', $data->id)
            ->exists();

        db_credit::where('id', $id_credit)->update([
            'order_list' => ($data->order_list)
        ]);

        db_credit::where('id', $data->id)->update([
            'order_list' => ($id)
        ]);

        if ($no_pay) {
            return redirect('/route?hide=true');
        } else {
            return redirect('/route');
        }


    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {


    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
