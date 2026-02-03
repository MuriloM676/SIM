<?php

namespace Database\Seeders;

use App\Enums\GravidadeInfracao;
use App\Models\Infracao;
use Illuminate\Database\Seeder;

class InfracaoSeeder extends Seeder
{
    /**
     * Seed das principais infrações do CTB
     */
    public function run(): void
    {
        $infracoes = [
            // Gravíssimas
            [
                'codigo_ctb' => 'Art. 165',
                'descricao' => 'Dirigir sob a influência de álcool ou substância psicoativa',
                'gravidade' => GravidadeInfracao::GRAVISSIMA,
                'pontos' => 7,
                'valor' => 2934.70,
                'detalhamento' => 'Dirigir sob a influência de álcool (concentração igual ou superior a 0,05 mg/L no ar alveolar)',
                'medidor_velocidade' => false,
            ],
            [
                'codigo_ctb' => 'Art. 218-I',
                'descricao' => 'Avançar sinal vermelho',
                'gravidade' => GravidadeInfracao::GRAVISSIMA,
                'pontos' => 7,
                'valor' => 293.47,
                'detalhamento' => 'Avançar o sinal vermelho do semáforo',
                'medidor_velocidade' => false,
            ],

            // Graves
            [
                'codigo_ctb' => 'Art. 218-III',
                'descricao' => 'Ultrapassar pela contramão',
                'gravidade' => GravidadeInfracao::GRAVE,
                'pontos' => 5,
                'valor' => 195.23,
                'detalhamento' => 'Ultrapassar outro veículo pela contramão',
                'medidor_velocidade' => false,
            ],
            [
                'codigo_ctb' => 'Art. 220-II',
                'descricao' => 'Deixar de dar preferência ao pedestre',
                'gravidade' => GravidadeInfracao::GRAVISSIMA,
                'pontos' => 7,
                'valor' => 293.47,
                'detalhamento' => 'Deixar de dar preferência ao pedestre que atravessa a via',
                'medidor_velocidade' => false,
            ],

            // Velocidade
            [
                'codigo_ctb' => 'Art. 218-II',
                'descricao' => 'Excesso de velocidade entre 20% e 50%',
                'gravidade' => GravidadeInfracao::GRAVE,
                'pontos' => 5,
                'valor' => 195.23,
                'detalhamento' => 'Velocidade superior à máxima em mais de 20% até 50%',
                'medidor_velocidade' => true,
            ],
            [
                'codigo_ctb' => 'Art. 218-III-vel',
                'descricao' => 'Excesso de velocidade acima de 50%',
                'gravidade' => GravidadeInfracao::GRAVISSIMA,
                'pontos' => 7,
                'valor' => 880.41,
                'detalhamento' => 'Velocidade superior à máxima em mais de 50%',
                'medidor_velocidade' => true,
            ],

            // Médias
            [
                'codigo_ctb' => 'Art. 181-G',
                'descricao' => 'Estacionar em local proibido',
                'gravidade' => GravidadeInfracao::MEDIA,
                'pontos' => 4,
                'valor' => 130.16,
                'detalhamento' => 'Estacionar o veículo em local proibido pela sinalização',
                'medidor_velocidade' => false,
            ],
            [
                'codigo_ctb' => 'Art. 244-I',
                'descricao' => 'Conduzir motocicleta sem capacete',
                'gravidade' => GravidadeInfracao::GRAVISSIMA,
                'pontos' => 7,
                'valor' => 293.47,
                'detalhamento' => 'Conduzir motocicleta sem usar capacete de segurança',
                'medidor_velocidade' => false,
            ],

            // Leves
            [
                'codigo_ctb' => 'Art. 181-XXI',
                'descricao' => 'Uso indevido de farol alto',
                'gravidade' => GravidadeInfracao::LEVE,
                'pontos' => 3,
                'valor' => 88.38,
                'detalhamento' => 'Fazer uso indevido de farol alto em vias urbanas',
                'medidor_velocidade' => false,
            ],
            [
                'codigo_ctb' => 'Art. 230-V',
                'descricao' => 'Conduzir veículo sem porte de documentos',
                'gravidade' => GravidadeInfracao::LEVE,
                'pontos' => 3,
                'valor' => 88.38,
                'detalhamento' => 'Conduzir o veículo sem portar documentos obrigatórios',
                'medidor_velocidade' => false,
            ],
        ];

        foreach ($infracoes as $infracao) {
            Infracao::create($infracao);
        }
    }
}
