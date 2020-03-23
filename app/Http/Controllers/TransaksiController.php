<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Transaksi;
use App\Pelanggan;
use App\Petugas;
use Auth;
use DB;
use Illuminate\Support\Facades\Validator;
class TransaksiController extends Controller
{
    public function index($id)
    {
        if(Auth::user()->level=="petugas" || "admin"){
            $transaksi=DB::table('transaksi')
            ->join('pelanggan','pelanggan.id','transaksi.id_pelanggan')
            ->join('petugas','petugas.id','transaksi.id_petugas')
            ->where('transaksi.id',$id)
            ->select('transaksi.id','pelanggan.nama','petugas.nama_petugas','transaksi.tgl_transaksi','transaksi.tgl_selesai')
            ->get();

            $data=array();
            foreach ($transaksi as $t){
                $data[]=array(
                    'id' => $t->id,
                    'nama' => $t->nama,
                    'nama_petugas' => $t->nama_petugas,
                    'tgl_transaksi' => $t->tgl_transaksi,
                    'tgl_selesai' => $t->tgl_selesai
                );
            }
            return response()->json($transaksi);
        }else{
            return response()->json(['status'=>'anda bukan petugas']);
        }
    }
    public function store(Request $req)
    {
        if(Auth::user()->level=="petugas" || "admin"){
        $validator=Validator::make($req->all(),
        [
            'id_petugas'=>'required',
            'id_pelanggan'=>'required',
            'tgl_transaksi'=>'required',
            'tgl_selesai' =>'required'
        ]
        );
        if($validator->fails()){
            return Response()->json($validator->errors());
        }

        $simpan=Transaksi::create([
            'id_petugas'=>$req->id_petugas,
            'id_pelanggan'=>$req->id_pelanggan,
            'tgl_transaksi'=>$req->tgl_transaksi,
            'tgl_selesai' =>$req->tgl_selesai
        ]);
        $status=1;
        $message="Transaksi Berhasil Ditambahkan";
        if($simpan){
          return Response()->json(compact('status','message'));
        }else {
          return Response()->json(['status'=>0]);
        }
      }
      else {
          return response()->json(['status'=>'anda bukan petugas']);
      }
  }
    public function update($id,Request $request){
        if(Auth::user()->level=="admin"){
        $validator=Validator::make($request->all(),
            [
                'id_petugas'=>'required',
                'id_pelanggan'=>'required',
                'tgl_transaksi'=>'required',
                'tgl_selesai' =>'required'
            ]
        );

        if($validator->fails()){
        return Response()->json($validator->errors());
        }

        $ubah=Transaksi::where('id',$id)->update([
            'id_petugas'=>$request->id_petugas,
            'id_pelanggan'=>$request->id_pelanggan,
            'tgl_transaksi'=>$request->tgl_transaksi,
            'tgl_selesai' =>$request->tgl_selesai
        ]);
        $status=1;
        $message="Transaksi Berhasil Diubah";
        if($ubah){
        return Response()->json(compact('status','message'));
        }else {
        return Response()->json(['status'=>0]);
        }
        }
    else {
    return response()->json(['status'=>'anda bukan petugas']);
    }
}
    public function destroy($id){
        if(Auth::user()->level=="admin"){
        $hapus=Transaksi::where('id',$id)->delete();
        $status=1;
        $message="Transaksi Berhasil Dihapus";
        if($hapus){
        return Response()->json(compact('status','message'));
        }else {
        return Response()->json(['status'=>0]);
        }
    }
    else {
        return response()->json(['status'=>'anda bukan petugas']);
        }
    }
  
    public function tampil(){
        if(Auth::user()->level=="petugas" || "admin"){
            $transaksi=Transaksi::orderBy("tgl_transaksi","ASC")
            ->join('pelanggan','pelanggan.id','transaksi.id_pelanggan')
            ->join('petugas','petugas.id','transaksi.id_petugas')
            ->select('transaksi.id','pelanggan.nama','petugas.nama_petugas','transaksi.tgl_transaksi','transaksi.tgl_selesai')
            ->get();
            $count=$transaksi->count();
            return response()->json(compact("count","transaksi"));
        } else {
            return Response()->json(['status'=> 'Tidak bisa, anda bukan petugas']);
        }
    }
    public function berdasarkantanggal($id)
    {
        if(Auth::user()->level=="petugas" || "admin"){
            $transaksi=DB::table('transaksi')
            ->join('pelanggan','pelanggan.id','transaksi.id_pelanggan')
            ->join('petugas','petugas.id','transaksi.id_petugas')
            ->where('tgl_transaksi',$id)
            ->get();

            $data=[];
            foreach ($transaksi as $t){
                $data=[
                    'id' => $t->id,
                    'nama' => $t->nama,
                    'nama_petugas' => $t->nama_petugas,
                    'tgl_transaksi' => $t->tgl_transaksi,
                    'tgl_selesai' => $t->tgl_selesai
                ];
            }
            return response()->json(['Data'=>$data]);
        }else{
            return response()->json(['status'=>'anda bukan petugas']);
        }
    }

    public function tampilkan(Request $req){
        $transaksi = DB::table('transaksi')->join('pelanggan', 'pelanggan.id','=', 'transaksi.id_pelanggan')
        ->where('transaksi.tgl_transaksi','>=',$req->tgl_transaksi)
        ->where('transaksi.tgl_transaksi','<=',$req->tgl_selesai)
        ->select('nama','telp','alamat','transaksi.id','tgl_transaksi','tgl_selesai')
        ->get();

        if($transaksi->count() > 0){
            $data_transaksi = array();
            foreach ($transaksi as $t){
                $grand = DB::table('detail_transaksi')->where('id_transaksi','=',$t->id)
                ->groupBy('id_transaksi')
                ->select(DB::raw('sum(subtotal) as grandtotal'))
                ->first();

                $detail = DB::table('detail_transaksi')->join('jenis_cuci','detail_transaksi.id_jenis','=','jenis_cuci.id')
                    ->where('id_transaksi','=',$t->id)
                    ->get();

                    $data_transaksi[] = array(
                        'Tanggal' => $t->tgl_transaksi,
                        'Nama Pelanggan' => $t->nama,
                        'Alamat' => $t->alamat,
                        'No Telp' => $t->telp,
                        'Deadline' => $t->tgl_selesai,
                        'Grand Total' => $grand, 
                        'Detail' => $detail,
                    );
                }
                
                return response()->json(compact('data_transaksi'));
                } else{
                $status = 'tidak ada transaksi antara tanggal '.$req->tgl_transaksi.' sampai dengan tanggal '.$req->tgl_selesai;
                return response()->json(compact('status'));
            }
        }
}
