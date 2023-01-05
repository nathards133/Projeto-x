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
            $anoVeiculoRaw = explode('-',$ano->Value);
            
            $anoVeiculo = $anoVeiculoRaw[0];
            $codTipoCombustivel = $anoVeiculoRaw[1];
            
            $combustivelFormat = explode(' ', $ano->Label);
            $combustivel = $combustivelFormat[1];
                
        }

        foreach ($modelos as $modelo) {
            $codigoModelo = $modelo->Value;
            $nomeModelo = $modelo->Label;
        }

        $this->getDataVeiculo($codTipoCombustivel,$codigoData, $mesData, $codigoMarca, $nomeMarca, $codigoModelo, $nomeModelo, $anoVeiculo, $combustivel);
    }

    public function getDataVeiculo($codTipoCombustivel,$codigoData, $mesData, $codigoMarca, $nomeMarca, $codigoModelo, $nomeModelo, $anoVeiculo, $combustivel)
    {

        $response = Http::post('https://veiculos.fipe.org.br/api/veiculos//ConsultarValorComTodosParametros', [
            'codigoTabelaReferencia' => $codigoData,
            'codigoTipoVeiculo' => 1,
            'codigoMarca' => $codigoMarca,
            'codigoModelo' => $codigoModelo,
            'anoModelo' => $anoVeiculo,
            'codigoTipoCombustivel' => $codTipoCombustivel,
            'tipoVeiculo' => 1,
            'tipoConsulta' => 'tradicional',
        ]);
        $body = $response->body();
        $veiculo = json_decode($body);
        $valor = $veiculo->Valor;
        $valor = str_replace('.', '', $valor);
        $marca = $veiculo->Marca;
        $modelo = $veiculo->Modelo;
        $anoModelo = $veiculo->AnoModelo;
        $combustivel = $veiculo->Combustivel;
        $mesReferencia = $veiculo->MesReferencia;
        $tipoVeiculo = $veiculo->TipoVeiculo;
        $siglaCombustivel = $veiculo->SiglaCombustivel;
        $data = $veiculo->Data;
        // $this->saveData($valor, $marca, $modelo, $anoModelo, $combustivel, $mesReferencia, $tipoVeiculo, $siglaCombustivel, $data, $valorFormatado);
    }
}
