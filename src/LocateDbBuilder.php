<?php

namespace Takuya\PhpPlocateWrapper;

use Takuya\ProcOpen\ProcOpen;
use Takuya\SystemUtil\Stream\StringIO\StringIO;
use function Takuya\ProcOpen\cmd_exists;

class LocateDbBuilder {
  
  private string $tmpname;
  private array $ignore_pattern;
  
  public function __destruct() {
    file_exists($this->tmpname) && unlink($this->tmpname);
  }
  
  /**
   * @param string $db_file
   * @param string $base_path
   * @param int    $block_size
   * @param string $require_visibility 'yes' or 'no',Default '' that means plocate-build default ( currently 'yes' )
   */
  public function __construct( protected string $db_file,
                               protected string $base_path,
                               protected int    $block_size = 128,
                               protected string $require_visibility = '' ) {
    ! is_writable(dirname($this->db_file)) && throw new \RuntimeException('not writable');
    ! is_dir($this->base_path) && throw  new \RuntimeException($this->base_path.' does not exists');
    ! cmd_exists('find') && throw new \RuntimeException('findutils not found');
    ! cmd_exists('plocate-build') && throw new \RuntimeException('plocate not found');
    $this->tmpname = $this->tmp_name();
  }
  
  protected function plocate_build( string $find_result_path, string $db_fpath, string $wd ):bool {
    $flag = ! empty($this->require_visibility) ? "-l {$this->require_visibility}" : '';
    $block = $this->block_size;
    $cmd = explode(' ', "plocate-build {$flag} -b {$block} -p") + ['a1' => $find_result_path, 'a2' => $db_fpath];
    $cmd = array_filter($cmd);
    $proc = new ProcOpen($cmd, $wd);
    $proc->run();
    $proc->wait();
    
    return $proc->info->exitcode === 0;
  }
  
  /**
   * @param string $pattern regular expression without delim.( ex '^composer.+$' , not '/^composer.+/ )
   * @return void
   */
  public function addIgnore(string $pattern):void {
    $easy_delim_check = fn ($pattern) => preg_match('/^([\|\/#~])([^\/#~]*)\1([a-zA-Z]*)$/', $pattern);
    if($easy_delim_check($pattern)){
      throw new \InvalidArgumentException('regex with delim. remove delim.');
    }
    $this->ignore_pattern ??=[];
    $this->ignore_pattern[] = $pattern;
  }
  protected function remove_ignore_names(string $input_path):false|int {
    if (empty($this->ignore_pattern)){
      return true;
    }
    $fstype = 'temp/maxmemory:'.(1024*1024*10);
    $out = new StringIO('',$fstype);
    $in = new StringIO(file_get_contents($input_path),$fstype);
    $regex = implode('|',$this->ignore_pattern);
    foreach($in as $line){
      if( preg_match("/{$regex}/",$line)) continue;
      $out->write($line."\n");
    }
    $str = $out->get_contents();
    return file_put_contents($input_path,$str);
  }
  
  protected function find_files( string $output_path ):bool {
    $wd = $this->base_path;
    $proc = new ProcOpen(['find', $this->base_path, '-type', 'f', '-printf', "%P\n"], $wd);
    $proc->setStdout(fopen($output_path, 'w'));
    $proc->run();
    $proc->wait();
    
    return $proc->info->exitcode === 0;
  }
  
  protected function tmp_name():string {
    $length = 10;
    $name = substr(
      str_shuffle(
        str_repeat($x = '0123456789abcdefghijklmnopqrstuvwxyz', ceil($length/strlen($x)))),
      1,
      $length);
    $tmp = sys_get_temp_dir().DIRECTORY_SEPARATOR.$name.'.find.out';
    
    return $tmp;
  }
  
  public function build():bool {
    
    return $this->find_files($this->tmpname) && $this->remove_ignore_names($this->tmpname)
           && $this->plocate_build($this->tmpname, $this->db_file, $this->base_path);
  }
}