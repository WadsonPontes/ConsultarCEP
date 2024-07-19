<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use GuzzleHttp\Client;

class CepController extends Controller
{
    public function search(Request $request, $ceps)
    {
        // Verifica se a string de CEPs está no formato correto
        if (preg_match('/^[0-9,-]+$/', $ceps)) {
            // Remove os traços e quebra a string de CEPs em um array
            $ceps = str_replace('-', '', $ceps);
            $cepArray = explode(',', $ceps);

            $client = new Client(['verify' => false]);
            $result = [];

            foreach ($cepArray as $cep) {
                $response = $client->get("https://viacep.com.br/ws/{$cep}/json/");
                $data = json_decode($response->getBody(), true);
                
                if (isset($data['erro'])) {
                    continue; // Ignora CEPs inválidos
                }

                // Reorganiza os dados conforme solicitado
                $formattedData = [
                    'cep' => str_replace('-', '', $data['cep']),
                    'label' => "{$data['logradouro']}, {$data['localidade']}",
                    'logradouro' => $data['logradouro'],
                    'complemento' => $data['complemento'],
                    'bairro' => $data['bairro'],
                    'localidade' => $data['localidade'],
                    'uf' => $data['uf'],
                    'ibge' => $data['ibge'],
                    'gia' => $data['gia'],
                    'ddd' => $data['ddd'],
                    'siafi' => $data['siafi']
                ];

                $result[] = $formattedData;
            }

            // Reverte a ordem dos resultados para que o primeiro CEP seja o último a ser retornado
            $result = array_reverse($result);

            return response()->json($result);
        } else {
            return response()->json(['error' => 'Formato inválido de CEPs.'], 400);
        }
    }
}
