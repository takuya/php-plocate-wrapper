<?php

namespace Takuya\PhpPlocateWrapper;

use Takuya\ProcOpen\ProcOpen;

class LocateDbBuilder {
  
  private string $tmpname;
  
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
    
    return $this->find_files($this->tmpname)
           && $this->plocate_build($this->tmpname, $this->db_file, $this->base_path);
  }
}