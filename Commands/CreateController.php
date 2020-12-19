<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class CreateController extends BaseCommand
{
	protected $group       = 'make';
	protected $name        = 'make:controller';
	protected $description = 'Make a controller.';

	/**
	 * the Command's usage
	 *
	 * @var string
	 */
	protected $usage = 'make:controller [controller_name] [Options]';

	/**
	 * the Command's Arguments
	 *
	 * @var array
	 */
	protected $arguments = [
		'controller_name' => 'nama file controllernya',
	];

	/**
	 * the Command's Options
	 *
	 * @var array
	 */
	protected $options = [
		'-n' => 'kostum namespace',
	];

	public function run(array $params = [])
	{
		helper('inflector');
		$name = array_shift($params);

		if (empty($name)) {
			$name = CLI::prompt('nama controllernya mas/mba :)');
		}

		if (empty($name)) {
			CLI::error('situ harus kasih namanya :)');
			return;
		}

		$ns       = $params['-n'] ?? CLI::getOption('n');
		$homepath = APPPATH;

		if (!empty($ns)) {
			// Get all namespaces
			$namespaces = Services::autoloader()->getNamespace();

			foreach ($namespaces as $namespace => $path) {
				if ($namespace === $ns) {
					$homepath = realpath(reset($path));
					break;
				}
			}
		} else {
			$ns = 'App';
		}

		// Always use UTC/GMT so global teams can work together
		// $config   = config('Migrations');

		// $fileName = pascalize($name . 'Controller');
		$fileName = pascalize($name);

		// full path
		$path = $homepath . '/Controllers/' . $fileName . '.php';

		// Class name should be pascal case now (camel case with upper first letter)
		$name = pascalize($name);

		$template = <<<EOD
<?php namespace $ns\Controllers;

//use CodeIgniter\RESTful\ResourceController;

class {name} extends BaseController
{
    public function __construct()
    {
        //
    }
}

EOD;
		$template = str_replace('{name}', $name, $template);

		helper('filesystem');
		if (!write_file($path, $template)) {
			CLI::error('hmmm.... gagal buat controller. coba cek foldernya bisa di baca/tulis gak.');
			return;
		}

		CLI::write('Controller dah dibuat: ' . CLI::color(str_replace($homepath, $ns, $path), 'green'));
	}
}
