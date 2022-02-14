<?php 

namespace Tests\Traits;

/**
 * Trait criar para realizar a exclusão de arquivos após a finalização dos tests. 
 */
trait TestStorages
{
    protected function deleteAllFiles()
    {
        $dirs = \Storage::directories(); 

        foreach($dirs as $dir){
            $files = \Storage::files($dir);
            \Storage::delete($files);
            \Storage::deleteDirectory($dir);
        }
    }
}
