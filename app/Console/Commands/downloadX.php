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

    protected $description = 'Download Fipe Veiculos';


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
            $this->getAnosModelos($codigoData, $mesData, $codigoMarca, $nomeMarca);
        }
    }

    public function getAnosModelos($codigoData, $mesData, $codigoMarca, $nomeMarca)
    {
        $response = Http::post('https://veiculos.fipe.org.br/api/veiculos//ConsultarModelos', [
            'codigoTabelaReferencia' => $codigoData,
            'codigoTipoVeiculo' => 1,
            'codigoMarca' => $codigoMarca,
        ]);
        $modelos = json_decode($response->body());
        
        $anos = $modelos->Anos;
        $modelos = $modelos->Modelos;
        
        foreach ($anos as $ano) {
                
            $anoVeiculo = $ano->Value;
            $combustivelFormat = explode(' ', $ano->Label);
            $combustivel = $combustivelFormat[0];
                
        }

        foreach ($modelos as $modelo) {
            $codigoModelo = $modelo->Value;
            $nomeModelo = $modelo->Label;
        }
    }
}
