<?php 

require_once 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;
use League\Csv\Reader;
use League\Csv\Statement;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'bi',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

$capsule->setAsGlobal();
$capsule->bootEloquent();

function times($times)
{
	for ($i = 1; $i <= $times; $i++) { 
		yield $i;
	}
}

$faker = Faker\Factory::create();

# criar passageiros
foreach (times(100) as $i) {
	$data = [];
	foreach (times(rand(500, 1000)) as $j) {
		$data[] = [
			'nome' => $faker->name,
			'data_nascimento' => $faker->dateTimeBetween('-80 years', '-5 years')->format('Y-m-d')
		];
	}
	Capsule::table('passageiro')->insert($data);
	echo count($data) . ' passageiros criados ' . $i . '/100' . PHP_EOL;
}

# criar aeroportos
$csv = Reader::createFromPath(__DIR__ . '/aeroportos_america_do_sul.csv', 'r');
$csv->setHeaderOffset(0);
$data = [];
foreach ((new Statement())->process($csv) as $record) {
    $data[] = [
    	'sigla' => $record['Sigla'],
    	'pais' => $record['Pais'],
    	'cidade' => $record['Cidade'],
    	'nome' => $record['Nome']
    ];
}
Capsule::table('aeroporto')->insert($data);
echo count($data) . ' aeroportos criados.' . PHP_EOL;

# criar voos
$aeroportos = Capsule::select("select * from aeroporto");
foreach (times(5) as $i) {
	$data = [];
	foreach (times(rand(1000, 5000)) as $j) {
		$year = date('Y') - $i;
		$partida = $faker->dateTimeBetween($year . "-01-01", $year . "-12-30");
		$data[] = [
			'partida' => $partida,
			'chegada' => $faker->dateTimeBetween($partida, '+ 10 hours'),
			'qtd_passageiro' => 5 * rand(5, 12),
			'aeroporto_origem_id' => $aeroportos[array_rand($aeroportos, 1)]->id,
			'aeroporto_destino_id' => $aeroportos[array_rand($aeroportos, 1)]->id,
		];
	}
	$capsule->table('voo')->insert($data);
	echo count($data) . ' voos criados ' . $i . '/5' . PHP_EOL;
}
$passageiros = null;

# criar assentos
$passageiros = Capsule::select("select * from passageiro");
$voos = Capsule::select("select * from voo");
$chunks = array_chunk($voos, 500);
foreach ($chunks as $chunk_index => $voo_chunk) {
	$data = [];
	foreach ($voo_chunk as $voo) {
		foreach (times($voo->qtd_passageiro) as $voo_index => $i) {
			$data[] = [
				'voo_id' => $voo->id,
				'passageiro_id' => $passageiros[array_rand($passageiros, 1)]->id,
				'num_assento' => $voo_index + 1
			];
		}
	}
	$capsule->table('assento')->insert($data);
	echo count($data) . ' assentos criados ' . ($chunk_index + 1) . '/' . count($chunks) . PHP_EOL;
}