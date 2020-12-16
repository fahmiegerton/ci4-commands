<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use Config\Services;

class CreateModel extends BaseCommand
{
    protected $group       = 'make';
    protected $name        = 'make:model';
    protected $description = 'Make a model.';

    /**
     * the Command's usage
     *
     * @var string
     */
    protected $usage = 'make:model [model_name] [Options]';

    /**
     * the Command's Arguments
     *
     * @var array
     */
    protected $arguments = [
        'model_name' => 'nama file modelnya',
    ];

    /**
     * the Command's Options
     *
     * @var array
     */
    protected $options = [
        '-t' => 'nama tabel',
        '-pk' => 'primary key',
        '-n' => 'kostum namespace'
    ];

    public function run(array $params)
    {
        helper('inflector');
        $name = array_shift($params);

        if (empty($name)) {
            $name = CLI::prompt('nama model nya mas/mba :)');
        }

        if (empty($name)) {
            CLI::error('situ harus kasih namanya :)');
            return;
        }

        $ns       = $params['-n'] ?? CLI::getOption('n');
        $table       = $params['-t'] ?? CLI::getOption('t');
        $pk       = $params['-pk'] ?? CLI::getOption('pk');
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

        if (!empty($table)) {
            $table = '' . $table . '';
        } else {
            $table = '';
        }

        if (!empty($pk)) {
            $pk = $pk;
        } else {
            $pk = '';
        }

        // Always use UTC/GMT so global teams can work together
        // $config   = config('Migrations');

        // $fileName = pascalize($name . 'Controller');
        $fileName = pascalize($name . 'Model');

        // full path
        $path = $homepath . '/Models/' . $fileName . '.php';

        // Class name should be pascal case now (camel case with upper first letter)
        $name = pascalize($name);

        $template = <<<EOD
<?php namespace $ns\Models;

use CodeIgniter\Model;

class {name}Model extends Model
{
    protected \$table = '$table';
    protected \$primaryKey = '$pk';

    protected \$returnType    = 'array';
    protected \$useTimestamps = true;
    protected \$createdField  = 'created_at';
    protected \$updatedField  = 'updated_at';
    protected \$deletedField  = 'deleted_at';
    
    //add something below 
}

EOD;
        $template = str_replace('{name}', $name, $template);

        helper('filesystem');
        if (!write_file($path, $template)) {
            CLI::error('hmmm.... gagal buat model. coba cek foldernya bisa di baca/tulis gak.');
            return;
        }

        CLI::write('model dah dibuat: ' . CLI::color(str_replace($homepath, $ns, $path), 'green'));
    }
}