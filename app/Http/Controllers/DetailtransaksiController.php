<?php

namespace App\Http\Controllers;
use App\Detailtransaksi;
use App\Jeniscuci;
use Auth;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class DetailtransaksiController extends Controller
{
    public function store(Request $req)
    {
        if(Auth::user()->level=="admin"){
        $validator=Validator::make($req->all(),
        [
            'id_transaksi'=>'required',
            'id_jenis'=>'required',
            'qty'=>'required'
        ]);
        if($validator->fails()){
            return Response()->json($validator->errors());
        }

        $sub =Jeniscuci::where('id',$req->id_jenis)->first();

        $subtotal = $sub->harga_perkilo * $req->qty; 

        $simpan=Detailtransaksi::create([
            'id_transaksi'=>$req->id_transaksi,
            'id_jenis'=>$req->id_jenis,
            'subtotal'=>$subtotal,
            'qty'=>$req->qty
        ]);
        $status=1;
        $message="Detail Berhasil Ditambahkan Berhasil Ditambahkan";
        if($simpan){
          return Response()->json(compact('status','message'));
        }else {
          return Response()->json(['status'=>0]);
        }
      }
      else {
          return response()->json(['status'=>'anda bukan admin']);
      }
  }
  public function update($id,Request $request){
    if(Auth::user()->level=="admin"){
    $validator=Validator::make($request->all(),
        [
            'id_transaksi'=>'required',
            'id_jenis'=>'required',
            'qty'=>'required'
        ]
    );

    if($validator->fails()){
    return Response()->json($validator->errors());
    }
    $sub =jeniscuci::where('id',$req->id_jenis)->first();

    $subtotal = $sub->harga_perkilo*$req->qty; 

    $ubah=DetailTransaksi::where('id',$id)->update([
        'id_transaksi'=>$req->id_transaksi,
            'id_jenis'=>$req->id_jenis,
            'subtotal'=>$subtotal,
            'qty'=>$req->qty
    ]);
    $status=1;
    $message="Detail Transaksi Berhasil Diubah";
    if($ubah){
    return Response()->json(compact('status','message'));
    }else {
    return Response()->json(['status'=>0]);
    }
    }
else {
return response()->json(['status'=>'anda bukan admin']);
}
}

public function destroy($id){
    if(Auth::user()->level=="admin"){
    $hapus=DetailTransaksi::where('id',$id)->delete();
    $status=1;
    $message="Detail Transaksi Berhasil Dihapus";
    if($hapus){
    return Response()->json(compact('status','message'));
    }else {
    return Response()->json(['status'=>0]);
    }
}
else {
    return response()->json(['status'=>'anda bukan admin']);
    }
}
    public function nampil(Request $request)
    {
        $hasil=DB::table('transaksi')
        ->join('pelanggan','pelanggan.id','=','transaksi.id_pelanggan')
        ->where('transaksi.tgl_transaksi','>=',$request->tgl_transaksi)
        ->where('transaksi.tgl_transaksi','<=',$request->tgl_selesai)
        ->get();
        $data=[];
        foreach($hasil as $k)
        {
            $grand=DB::table('detail_transaksi')
            ->where('id_transaksi','=',$k->id)
            ->groupBy('id_transaksi')
            ->select(DB::raw('sum(subtotal) as grandtotal'))
            ->first();

            $detail=DB::table('detail_transaksi')
            ->join('jenis_cuci','jenis_cuci.id','=','detail_transaksi.id_jenis')
            ->where('id_transaksi',$k->id)->get();
            $data=[
                "tgl_transaksi"=>$k->tgl_transaksi,
                "nama"=>$k->nama,
                "alamat"=>$k->alamat,
                "telp"=>$k->telp,
                "tgl_selesai"=>$k->tgl_selesai,
                "grandtotal"=>$grand,
                "detail_cuci"=>$detail

            ];

        }
        return response()->json(['Data'=>$data]);
    }


}
