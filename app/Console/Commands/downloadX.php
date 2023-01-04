<?php


namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Symfony\Component\DomCrawler\Crawler;
use guzzlehttp\guzzle;

class downloadX extends Command
{
  
    protected $signature = 'download:fipe';

    protected $description = 'Download X';


    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->getDataTabela();

    }

    public function getDataTabela()
    {
     
        $datas = [];
        $response = Http::post('https://veiculos.fipe.org.br/api/veiculos//ConsultarTabelaDeReferencia', []);
        $body = $response->body();
        
        $datas = json_decode($body);
        
        foreach ($datas as $key => $data) {
            $codigoData = $data->Codigo;
            $mesData = $data->Mes;
        }

        $this->getMarcas($codigoData, $mesData);
    }

    public function getMarcas($codigoData, $mesData)
    {
        $marcas = [];
        $response = Http::post('https://veiculos.fipe.org.br/api/veiculos//ConsultarMarcas', [
            'codigoTabelaReferencia' => $codigoData,
            'codigoTipoVeiculo' => 1,
        ]);
        $body = $response->body();
        $marcas = json_decode($body);
        
        foreach ($marcas as $key => $marca) {
            $codigoMarca = $marca->Value;
            $nomeMarca = $marca->Label;
            $this->getModelos($codigoData, $mesData, $codigoMarca, $nomeMarca);
        }
    }

    public function getModelos($codigoData, $mesData, $codigoMarca, $nomeMarca)
    {
        $modelos = [];
        $response = Http::post('https://veiculos.fipe.org.br/api/veiculos//ConsultarModelos', [
            'codigoTabelaReferencia' => $codigoData,
            'codigoTipoVeiculo' => 1,
            'codigoMarca' => $codigoMarca,
        ]);
        $body = $response->body();
        $modelos = json_decode($body);
        // dd($modelos);
        foreach ($modelos as $key => $modelo) {
            $codigoModelo = $modelo->Value;
            $nomeModelo = $modelo->Label;
            $this->getAnos($codigoData, $mesData, $codigoMarca, $nomeMarca, $codigoModelo, $nomeModelo);
        }
    }

    public function getAnos($codigoData, $mesData, $codigoMarca, $nomeMarca, $codigoModelo, $nomeModelo)
    {
        $anos = [];
        $response = Http::post('https://veiculos.fipe.org.br/api/veiculos//ConsultarAnoModelo', [
            'codigoTabelaReferencia' => $codigoData,
            'codigoTipoVeiculo' => 1,
            'codigoMarca' => $codigoMarca,
            'codigoModelo' => $codigoModelo,
        ]);
        $body = $response->body();
        $anos = json_decode($body);
        
        foreach ($anos as $key => $ano) {
            dd($ano);
            $codigoAno = $ano->Value;
            $nomeAno = $ano->Label;
            $this->getValor($codigoData, $mesData, $codigoMarca, $nomeMarca, $codigoModelo, $nomeModelo, $codigoAno, $nomeAno);
        }
    }
}
