<?php
// app/Http/Controllers/HistorisController.php
namespace App\Http\Controllers;
use App\Services\HistorisQueryService;
use Illuminate\Http\Request;
use App\Models\Dimension\{DimGolonganTarif, DimMetrik};
use App\Services\ETL\Transformer;

class HistorisController extends Controller {
    public function __construct(protected HistorisQueryService $service) {}

    public function index() {
        return view('partials.historis', [
            'golongan' => DimGolonganTarif::get(),
            'metriks' => DimMetrik::where('aktif',true)->get()
        ]);
    }

    public function compare() {
        return view('partials.historis-compare', [
            'golongan' => DimGolonganTarif::get(),
            'metriks' => DimMetrik::where('aktif',true)->get()
        ]);
    }

    public function chartData(Request $request) {
        $data = $this->service->getChartData($request->only(['tahun_dari','tahun_sampai','metrik','golongan','mode']));
        return response()->json($data);
    }

    public function compareData(Request $request) {
        $t1 = $request->input('tahun1'); $t2 = $request->input('tahun2');
        $metrik = $request->input('metrik','KWH_JUAL');
        $golongan = $request->input('golongan',['S','R','B','I','P','M']);

        $check = $this->service->analyzeComparability($t1, $t2, $metrik, $golongan);
        if (!$check['valid']) return response()->json(['error'=>$check['warning']], 422);

        $chart = $this->service->getChartData([
            'tahun_dari'=>$t1, 'tahun_sampai'=>$t2, 'metrik'=>$metrik, 
            'golongan'=>$check['golongan_compatible'], 'mode'=>'yearly'
        ]);
        return response()->json(['chart'=>$chart, 'metadata'=>$check]);
    }

    public function availableMetrics() {
        return response()->json(DimMetrik::where('aktif',true)->get(['kode','nama','satuan']));
    }
    public function importSheet(string $sheetName, array $rows, int $tahun): array
{
    $transformer = new \App\Services\ETL\Transformer();
    // Di dalam loop import sheet:
$sheetData = $spreadsheet->getSheetByName((string)$tahun)->toArray();
$transformer = new Transformer();
$records = $transformer->transform($sheetData, $tahun);

// Insert ke DB (menggunakan chunk untuk hemat memori)
foreach (array_chunk($records, 500) as $chunk) {
    DB::table('fact_historis')->insert($chunk);
}
    return match(true) {
        $tahun >= 1997 && $tahun <= 2004 => $transformer->transformTriwulan([$rows], $tahun),
        $tahun === 2024                  => $transformer->transform2024([$rows], $tahun),
        $tahun === 2025                  => $transformer->transform2025([$rows], $tahun),
        default                          => $transformer->transformBulanan([$rows], $tahun),
    };
}
public function importViaUrl(Request $request) 
{
    $request->validate(['tahun' => 'required|integer']);
    
    $importer = app(App\Services\ETL\DataHistorisImporter::class);
    $result = $importer->fetchAndImport($request->tahun);

    if ($result['status'] === 'success') {
        return response()->json($result);
    }

    return response()->json($result, 500);
}
}