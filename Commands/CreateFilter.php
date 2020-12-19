<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class CreateFilter extends BaseCommand
{
    protected $group       = 'make';
    protected $name        = 'make:filter';
    protected $description = 'Make a filter.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'make:filter [filter_name] [Options]';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'filter_name' => 'nama file filternya',
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
            $name = CLI::prompt('nama filter nya mas/mba :)');
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
        $path = $homepath . '/Filters/' . $fileName . '.php';

        // Class name should be pascal case now (camel case with upper first letter)
        $name = pascalize($name);

        $rn = '$request';
        $rqin = '$request';
        $rsin = '$response';

        $template = <<<EOD
<?php namespace $ns\Filters;

use CodeIgniter\Http\RequestInterface;
use CodeIgniter\Http\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

//use \App\Libraries\mylib;

class {name} implements FilterInterface
{
    public function before(RequestInterface $rn)
    {
        //
    }

    public function after(RequestInterface $rqin, ResponseInterface $rsin)
    {
        //
    }
}

EOD;
        $template = str_replace('{name}', $name, $template);

        helper('filesystem');
        if (!write_file($path, $template)) {
            CLI::error('hmmm.... gagal buat filter. coba cek foldernya bisa di baca/tulis gak.');
            return;
        }

        CLI::write('filter dah dibuat: ' . CLI::color(str_replace($homepath, $ns, $path), 'green'));
        CLI::write('');
        CLI::write('jangan lupa tambahkan alias dulu ke App\Config\Filters.php dan tambahkan ini :');

        $slashing = str_replace('/', '\\', ' => /' . $ns . '\Filters/');
        CLI::write("'" . strtolower($name) . $slashing . $fileName . "::class'", 'black', 'green');
    }
}